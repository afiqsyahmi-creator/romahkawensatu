<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class BookingsController extends AppController
{
    /**
     * ToyyibPay configuration – set these in app_local.php under 'ToyyibPay'.
     * Example:
     *   'ToyyibPay' => [
     *       'sandbox' => true,
     *       'userSecretKey' => 'your-secret-key',
     *       'categoryCode' => 'your-category-code',
     *   ]
     */
    private array $toyyibConfig = [];

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions([
            'step1', 'step2', 'step3', 'step4',
            'saveStep1', 'saveStep2', 'saveStep3',
            'addons', 'saveAddons',
            'processPayment', 'callback', 'success', 'calendarData'
        ]);

        $this->toyyibConfig = [
            'sandbox'       => \Cake\Core\Configure::read('ToyyibPay.sandbox', true),
            'userSecretKey' => \Cake\Core\Configure::read('ToyyibPay.userSecretKey', ''),
            'categoryCode'  => \Cake\Core\Configure::read('ToyyibPay.categoryCode', ''),
        ];
    }

    // ──────────────────────────────────────────────
    //  STEP 1 – Calendar & Time Selection
    // ──────────────────────────────────────────────
    public function step1(): void
    {
        $this->clearSessionBooking();

        // Load all active studios for filter display
        $studios = $this->fetchTable('Studios')->find()
            ->where(['status' => 'active'])
            ->orderBy(['studio_id' => 'ASC'])
            ->all();

        // Distinct studio types / packages for filters
        $studioTypes = $studios->map(fn($s) => $s->studio_name)->toList();

        $this->set(compact('studios', 'studioTypes'));
    }

    // ──────────────────────────────────────────────
    //  STEP 1 – Save date/time to session, go to step 2
    // ──────────────────────────────────────────────
    public function saveStep1(): void
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();

        $bookingDate = $data['booking_date'] ?? '';
        $startTime   = $data['start_time'] ?? '';
        $hours       = (int)($data['hours'] ?? 2);

        if (empty($bookingDate) || empty($startTime)) {
            $this->Flash->error('Please select a date and time before continuing.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        // Normalize date to YYYY-MM-DD (handles M/D/YY, D/M/YYYY, etc.)
        $ts = strtotime($bookingDate);
        if ($ts !== false) {
            $bookingDate = date('Y-m-d', $ts);
        }

        // Normalize time to HH:MM (handles 12-hour format like "6:00 PM")
        $tsTime = strtotime($startTime);
        if ($tsTime !== false) {
            $startTime = date('H:i', $tsTime);
        }

        // Calculate end time
        $endTime = date('H:i', $tsTime + $hours * 3600);

        // Store in session
        $session = $this->request->getSession();
        $session->write('Booking.step1', [
            'booking_date' => $bookingDate,
            'start_time'   => $startTime,
            'end_time'     => $endTime,
            'hours'        => $hours,
        ]);

        $this->redirect(['action' => 'step2']);
    }

    // ──────────────────────────────────────────────
    //  STEP 2 – Select Studio
    // ──────────────────────────────────────────────
    public function step2(): void
    {
        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        if (!$step1) {
            $this->Flash->error('Please start from the beginning.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        $studios = $this->fetchTable('Studios')->find()
            ->where(['status' => 'active'])
            ->contain(['Galleries'])
            ->orderBy(['studio_id' => 'ASC'])
            ->all();

        $this->set(compact('studios'));
    }

    public function saveStep2(): void
    {
        $this->request->allowMethod(['post']);
        $studioId = (int)$this->request->getData('studio_id');

        if (!$studioId) {
            $this->Flash->error('Please select a studio.');
            $this->redirect(['action' => 'step2']);
            return;
        }

        $studio = $this->fetchTable('Studios')->get($studioId);
        if (!$studio || $studio->status !== 'active') {
            $this->Flash->error('Selected studio is not available.');
            $this->redirect(['action' => 'step2']);
            return;
        }

        $session = $this->request->getSession();
        $session->write('Booking.step2', [
            'studio_id'   => $studio->studio_id,
            'studio_name' => $studio->studio_name,
            'hourly_rate' => (float)$studio->hourly_rate,
            'capacity'    => $studio->capacity,
            'image'       => $studio->image,
        ]);

        $this->redirect(['action' => 'addons']);
    }

    // ──────────────────────────────────────────────
    //  STEP 2a – Add-ons Selection
    // ──────────────────────────────────────────────
    public function addons(): void
    {
        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        if (!$step1 || !$step2) {
            $this->Flash->error('Please complete the previous steps first.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        $addonsTable = $this->fetchTable('Addons');

        // 1. Load active add-ons with popularity & capacity data
        $addons = $addonsTable->find()
            ->where(['status' => 'active'])
            ->orderBy(['is_popular' => 'DESC', 'addon_id' => 'ASC'])
            ->all();

        // 2. Calculate real scarcity for each add-on
        $scarcity = [];
        foreach ($addons as $a) {
            $remaining = $addonsTable->getRemainingCapacity((int)$a->addon_id);
            if ($remaining !== null) {
                $scarcity[(int)$a->addon_id] = $remaining;
            }
        }

        // 3. Load active bundle offers
        $bundles = $this->fetchTable('BundleOffers')->findActive();

        // 4. Previously selected add-ons (if any)
        $saved = $session->read('Booking.addons') ?? [];

        $this->set(compact('addons', 'saved', 'bundles', 'scarcity'));
    }

    public function saveAddons(): void
    {
        $this->request->allowMethod(['post']);
        $session = $this->request->getSession();

        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        if (!$step1 || !$step2) {
            $this->Flash->error('Session expired. Please start again.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        $selected = $this->request->getData('addons');
        $quantities = $this->request->getData('quantities', []);

        // Build addon data with price snapshots
        $addonData = [];
        $selectedAddonIds = [];
        if (!empty($selected) && is_array($selected)) {
            $addonsTable = $this->fetchTable('Addons');
            foreach ($selected as $addonId) {
                $addonId = (int)$addonId;
                if ($addonId < 1) continue;
                try {
                    $addon = $addonsTable->get($addonId);

                    // Re-check availability
                    $remaining = $addonsTable->getRemainingCapacity($addonId);
                    if ($remaining !== null && $remaining < 1) {
                        continue; // skip fully booked add-ons
                    }

                    $quantity = 1;
                    if ($addon->selection_type === 'quantity') {
                        $qty = (int)($quantities[$addonId] ?? 1);
                        $quantity = max(1, min($qty, (int)$addon->max_per_booking));
                        // Also cap by remaining capacity
                        if ($remaining !== null) {
                            $quantity = min($quantity, $remaining);
                        }
                    }

                    $selectedAddonIds[] = $addonId;

                    $addonData[] = [
                        'addon_id' => $addon->addon_id,
                        'addon_name' => $addon->addon_name,
                        'addon_type' => $addon->addon_type,
                        'selection_type' => $addon->selection_type,
                        'price' => (float)$addon->price,
                        'quantity' => $quantity,
                    ];
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    // skip invalid addon
                }
            }
        }

        // Calculate bundle discounts
        $bundleDiscounts = [];
        if (!empty($selectedAddonIds)) {
            $matchingBundles = $this->fetchTable('BundleOffers')->findMatchingBundles($selectedAddonIds);
            foreach ($matchingBundles as $bundle) {
                $bundleDiscounts[] = [
                    'bundle_id' => (int)$bundle->bundle_id,
                    'addon_id_1' => (int)$bundle->addon_id_1,
                    'addon_id_2' => (int)$bundle->addon_id_2,
                    'discount_amount' => (float)$bundle->discount_amount,
                    'label' => ($bundle->addon_1->addon_name ?? '') . ' + ' . ($bundle->addon_2->addon_name ?? ''),
                ];
            }
        }

        $session->write('Booking.addons', $addonData);
        $session->write('Booking.bundle_discounts', $bundleDiscounts);

        $this->redirect(['action' => 'step3']);
    }

    // ──────────────────────────────────────────────
    //  STEP 3 – User Information Form
    // ──────────────────────────────────────────────
    public function step3(): void
    {
        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        if (!$step1 || !$step2) {
            $this->Flash->error('Please complete the previous steps first.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        $saved = $session->read('Booking.step3') ?? [];
        $this->set('formData', $saved);
    }

    public function saveStep3(): void
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();

        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        if (!$step1 || !$step2) {
            $this->Flash->error('Session expired. Please start again.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        // Validate required fields
        $errors = [];
        if (empty($data['customer_name'])) $errors[] = 'Full name is required.';
        if (empty($data['phone_number'])) $errors[] = 'Phone number is required.';
        if (empty($data['pax']) || (int)$data['pax'] < 1) $errors[] = 'Number of pax is required.';
        if (empty($data['event_type'])) $errors[] = 'Event type is required.';

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $this->Flash->error($err);
            }
            $session->write('Booking.step3', $data);
            $this->redirect(['action' => 'step3']);
            return;
        }

        $session->write('Booking.step3', [
            'customer_name' => $data['customer_name'],
            'phone_number'  => $data['phone_number'],
            'email'         => $data['email'] ?? '',
            'pax'           => (int)$data['pax'],
            'event_type'    => $data['event_type'],
            'notes'         => $data['notes'] ?? '',
        ]);

        $this->redirect(['action' => 'step4']);
    }

    // ──────────────────────────────────────────────
    //  STEP 4 – Payment Summary & ToyyibPay
    // ──────────────────────────────────────────────
    public function step4(): void
    {
        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        $step3 = $session->read('Booking.step3');

        if (!$step1 || !$step2 || !$step3) {
            $this->Flash->error('Please complete all previous steps first.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        // Calculate totals
        $hours = $step1['hours'];
        $hourlyRate = $step2['hourly_rate'];
        $studioTotal = $hourlyRate * $hours;
        $totalPrice = $studioTotal;

        // Add-ons pricing
        $addons = $session->read('Booking.addons') ?? [];
        $addonsTotal = 0;
        foreach ($addons as $a) {
            $addonsTotal += (float)$a['price'] * (int)($a['quantity'] ?? 1);
        }
        $totalPrice += $addonsTotal;

        // Bundle discounts
        $bundleDiscounts = $session->read('Booking.bundle_discounts') ?? [];
        $bundleDiscountTotal = 0;
        foreach ($bundleDiscounts as $bd) {
            $bundleDiscountTotal += (float)$bd['discount_amount'];
        }
        $totalPrice -= $bundleDiscountTotal;
        if ($totalPrice < 0) $totalPrice = 0;

        $this->set(compact('step1', 'step2', 'step3', 'addons', 'addonsTotal', 'bundleDiscounts', 'bundleDiscountTotal', 'studioTotal', 'totalPrice', 'hours', 'hourlyRate'));
    }

    /**
     * Process payment – create booking in DB, redirect to ToyyibPay
     */
    public function processPayment(): void
    {
        $this->request->allowMethod(['post']);

        $session = $this->request->getSession();
        $step1 = $session->read('Booking.step1');
        $step2 = $session->read('Booking.step2');
        $step3 = $session->read('Booking.step3');

        if (!$step1 || !$step2 || !$step3) {
            $this->Flash->error('Session expired. Please start again.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        // Calculate total
        $hours = $step1['hours'];
        $hourlyRate = $step2['hourly_rate'];
        $totalPrice = $hourlyRate * $hours;

        // Add-ons pricing
        $addons = $session->read('Booking.addons') ?? [];
        $addonsTotal = 0;
        foreach ($addons as $a) {
            $addonsTotal += (float)$a['price'] * (int)($a['quantity'] ?? 1);
        }
        $totalPrice += $addonsTotal;

        // Bundle discounts
        $bundleDiscounts = $session->read('Booking.bundle_discounts') ?? [];
        $bundleDiscountTotal = 0;
        foreach ($bundleDiscounts as $bd) {
            $bundleDiscountTotal += (float)$bd['discount_amount'];
        }
        $totalPrice -= $bundleDiscountTotal;
        if ($totalPrice < 0) $totalPrice = 0;

        // Normalize date (safety net for old sessions with bad format)
        $bookingDate = $step1['booking_date'];
        $ts = strtotime((string)$bookingDate);
        if ($ts !== false) {
            $bookingDate = date('Y-m-d', $ts);
        }

        // Normalize times
        $startTime = $step1['start_time'];
        $tsTime = strtotime((string)$startTime);
        if ($tsTime !== false) {
            $startTime = date('H:i', $tsTime);
        }
        $endTime = $step1['end_time'];
        $tsEnd = strtotime((string)$endTime);
        if ($tsEnd !== false) {
            $endTime = date('H:i', $tsEnd);
        }

        // 1. Find or create customer
        $customersTable = $this->fetchTable('Customers');
        $customer = null;
        if (!empty($step3['email'])) {
            $customer = $customersTable->find()->where(['email' => $step3['email']])->first();
        }
        if (!$customer) {
            $customer = $customersTable->newEntity([
                'customer_name' => $step3['customer_name'],
                'phone_number'  => $step3['phone_number'],
                'email'         => $step3['email'] ?: null,
            ]);
            $customersTable->save($customer);
        }

        // 2. Check overlap
        $bookingsTable = $this->fetchTable('Bookings');
        if ($bookingsTable->slotIsTaken(
            $step2['studio_id'],
            $bookingDate,
            $startTime,
            $endTime
        )) {
            $this->Flash->error('That time slot has just been booked by someone else. Please choose another.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        // 3. Create booking (pending payment)
        $booking = $bookingsTable->newEntity([
            'customer_id'    => $customer->customer_id,
            'studio_id'      => $step2['studio_id'],
            'booking_date'   => $bookingDate,
            'start_time'     => $startTime,
            'end_time'       => $endTime,
            'pax'            => $step3['pax'],
            'event_type'     => $step3['event_type'],
            'notes'          => $step3['notes'] ?? '',
            'total_price'    => $totalPrice,
            'booking_status' => 'pending',
        ]);

        if (!$bookingsTable->save($booking)) {
            $this->Flash->error('Could not create the booking. Please try again.');
            $this->redirect(['action' => 'step4']);
            return;
        }

        // 3b. Save booking_addon junction records
        if (!empty($addons)) {
            $bookingAddonsTable = $this->fetchTable('BookingAddons');
            foreach ($addons as $a) {
                $ba = $bookingAddonsTable->newEntity([
                    'booking_id'       => $booking->booking_id,
                    'addon_id'         => (int)$a['addon_id'],
                    'quantity'         => (int)($a['quantity'] ?? 1),
                    'price_at_booking' => (float)$a['price'],
                ]);
                $bookingAddonsTable->save($ba);
            }
        }

        // 3c. Save bundle discounts
        $bundleDiscounts = $session->read('Booking.bundle_discounts') ?? [];
        if (!empty($bundleDiscounts)) {
            $bbdTable = $this->fetchTable('BookingBundleDiscounts');
            foreach ($bundleDiscounts as $bd) {
                $discount = $bbdTable->newEntity([
                    'booking_id' => $booking->booking_id,
                    'bundle_id' => (int)$bd['bundle_id'],
                    'discount_amount' => (float)$bd['discount_amount'],
                ]);
                $bbdTable->save($discount);
            }
        }

        // 4. Create initial payment record (pending)
        $paymentsTable = $this->fetchTable('Payments');
        $payment = $paymentsTable->newEntity([
            'booking_id'      => $booking->booking_id,
            'amount'          => $totalPrice,
            'payment_method'  => 'ToyyibPay',
            'payment_status'  => 'pending',
        ]);
        $paymentsTable->save($payment);

        // 5. Store booking_id in session for callback
        $session->write('Booking.booking_id', $booking->booking_id);
        $session->write('Booking.payment_id', $payment->payment_id);

        // 6. Redirect to ToyyibPay
        $billUrl = $this->createToyyibBill($booking, $payment, $totalPrice);
        if ($billUrl) {
            $this->redirect($billUrl);
        } else {
            // Fallback: complete booking without payment gateway
            $booking->booking_status = 'confirmed';
            $bookingsTable->save($booking);
            $payment->payment_status = 'paid';
            $paymentsTable->save($payment);
            $this->Flash->success('Booking confirmed! (Payment gateway bypassed for testing.)');
            $this->redirect(['action' => 'success', $booking->booking_id]);
        }
    }

    // ──────────────────────────────────────────────
    //  TOYYIBPAY INTEGRATION
    // ──────────────────────────────────────────────

    /**
     * Create a bill on ToyyibPay and return the redirect URL.
     */
    private function createToyyibBill($booking, $payment, float $totalPrice): ?string
    {
        $config = $this->toyyibConfig;
        $baseUrl = $config['sandbox']
            ? 'https://dev.toyyibpay.com'
            : 'https://toyyibpay.com';

        // Reload booking with customer relation
        $bookingsTable = $this->fetchTable('Bookings');
        $bookingFull = $bookingsTable->find()
            ->where(['booking_id' => $booking->booking_id])
            ->contain(['Customers', 'Studios'])
            ->first();

        if (!$bookingFull) return null;

        $billPrice = (int)round($totalPrice * 100); // ToyyibPay uses cents

        $billName = 'Romahkawensatu Booking #' . $bookingFull->booking_id;
        $billDesc = sprintf(
            'Studio: %s | Date: %s | Time: %s-%s',
            $bookingFull->studio->studio_name,
            (string)$bookingFull->booking_date,
            substr((string)$bookingFull->start_time, 0, 5),
            substr((string)$bookingFull->end_time, 0, 5)
        );

        $returnUrl = $this->getCallbackUrl('bookings', 'success', $bookingFull->booking_id);
        $callbackUrl = $this->getCallbackUrl('bookings', 'callback');
        $cancelUrl = $this->getCallbackUrl('bookings', 'step4');

        $postData = [
            'userSecretKey'           => $config['userSecretKey'],
            'categoryCode'            => $config['categoryCode'],
            'billName'                => $billName,
            'billDescription'         => $billDesc,
            'billPriceSetting'        => '1',
            'billPayorInfo'           => '1',
            'billAmount'              => $billPrice,
            'billReturnUrl'           => $returnUrl,
            'billCallbackUrl'         => $callbackUrl,
            'billExternalReferenceNo' => (string)$bookingFull->booking_id,
            'billTo'                  => $bookingFull->customer->customer_name ?? 'Customer',
            'billEmail'               => $bookingFull->customer->email ?: 'noreply@romahkawensatu.com',
            'billPhone'               => $bookingFull->customer->phone_number ?: '0123456789',
            'billPaymentChannel'      => '0',
            'billChargeToCustomer'    => '1',
            'billSplitPayment'        => '0',
            'billSplitPaymentArgs'    => '',
            'billPaymentSettlement'   => '0',
            'billMerchantCode'        => '',
            'billContentType'         => '1',
        ];

        try {
            $http = new \Cake\Http\Client();
            $response = $http->post($baseUrl . '/index.php/api/createBill', $postData, [
                'type' => 'form',
            ]);

            if ($response->isOk()) {
                $body = $response->getJson();
                if (!empty($body[0]['BillCode'])) {
                    $billCode = $body[0]['BillCode'];
                    // Save bill code to payment record
                    $paymentsTable = $this->fetchTable('Payments');
                    $paymentEntity = $paymentsTable->get($payment->payment_id);
                    $paymentEntity->gateway_reference = $billCode;
                    $paymentsTable->save($paymentEntity);

                    return $baseUrl . '/' . $billCode;
                }
            }
        } catch (\Exception $e) {
            // Log error
        }

        return null;
    }

    /**
     * ToyyibPay callback endpoint (server-to-server POST)
     */
    public function callback(): void
    {
        $this->autoRender = false;
        $this->viewBuilder()->setClassName('Json');

        $data = $this->request->getData();
        $billCode = $data['billcode'] ?? '';
        $orderId  = $data['order_id'] ?? '';
        $statusId = $data['status_id'] ?? '';

        if (empty($billCode) || empty($orderId)) {
            $this->response = $this->response->withStatus(400);
            $this->set('success', false);
            return;
        }

        $bookingId = (int)$orderId;
        $bookingsTable = $this->fetchTable('Bookings');
        $booking = $bookingsTable->find()
            ->where(['booking_id' => $bookingId])
            ->contain(['Payments'])
            ->first();

        if (!$booking) {
            $this->response = $this->response->withStatus(404);
            $this->set('success', false);
            return;
        }

        // Update payment status based on ToyyibPay status_id
        // 1 = successful, 2 = pending, 3 = failed
        $paymentStatus = match ((int)$statusId) {
            1 => 'paid',
            3 => 'failed',
            default => 'pending',
        };

        // Update booking status
        $bookingStatus = match ((int)$statusId) {
            1 => 'confirmed',
            3 => 'cancelled',
            default => 'pending',
        };

        $booking->booking_status = $bookingStatus;
        $bookingsTable->save($booking);

        // Update the associated payment
        if (!empty($booking->payments)) {
            $payment = $booking->payments[0];
            foreach ($booking->payments as $p) {
                if ($p->gateway_reference === $billCode) {
                    $payment = $p;
                    break;
                }
            }
            $paymentsTable = $this->fetchTable('Payments');
            $payment->payment_status = $paymentStatus;
            $payment->gateway_reference = $billCode;
            $paymentsTable->save($payment);
        }

        $this->set('success', true);
    }

    /**
     * Success page after ToyyibPay return
     */
    public function success(int $bookingId): void
    {
        $booking = $this->fetchTable('Bookings')->find()
            ->where(['Bookings.booking_id' => $bookingId])
            ->contain(['Customers', 'Studios', 'Payments'])
            ->first();

        if (!$booking) {
            $this->Flash->error('Booking not found.');
            $this->redirect(['action' => 'step1']);
            return;
        }

        $this->set(compact('booking'));
    }

    // ──────────────────────────────────────────────
    //  CALENDAR DATA
    // ──────────────────────────────────────────────
    public function calendarData(): void
    {
        $this->request->allowMethod(['get']);
        $ym = $this->request->getQuery('ym') ?: date('Y-m');
        $start = $ym . '-01';
        $end = date('Y-m-t', strtotime($start));

        $rows = $this->fetchTable('Bookings')->find()
            ->contain(['Studios'])
            ->where([
                'booking_status IN' => ['confirmed', 'pending'],
                'booking_date >=' => $start,
                'booking_date <=' => $end,
            ])
            ->orderBy(['booking_date' => 'ASC', 'start_time' => 'ASC'])
            ->all()
            ->map(fn($b) => [
                'date'  => $b->booking_date->format('Y-m-d'),
                'studio' => $b->studio->studio_name,
                'start' => substr((string)$b->start_time, 0, 5),
                'end'   => substr((string)$b->end_time, 0, 5),
            ])->toList();

        $this->viewBuilder()->setClassName('Json')->setOption('serialize', ['rows']);
        $this->set('rows', $rows);
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    private function clearSessionBooking(): void
    {
        $session = $this->request->getSession();
        $session->delete('Booking');
    }

    private function getCallbackUrl(string $controller, string $action, mixed $param = null): string
    {
        $base = \Cake\Routing\Router::fullBaseUrl();
        $url = $base . '/' . $controller . '/' . $action;
        if ($param !== null) {
            $url .= '/' . $param;
        }
        return $url;
    }
}
