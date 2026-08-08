<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

class DashboardController extends AppController
{
    /**
     * Admin dashboard — key metrics, revenue chart (daily/weekly/monthly/yearly),
     * today's & upcoming bookings.
     *
     * Query params:
     *   ?period=daily|weekly|monthly|yearly        — revenue chart period
     *   ?status=confirmed|pending|completed|cancelled — filter today's list
     *   ?date=YYYY-MM-DD                              — view a different day
     */
    public function index(): void
    {
        /** @var \App\Model\Table\BookingsTable $bookingsTable */
        $bookingsTable = $this->fetchTable('Bookings');
        /** @var \App\Model\Table\PaymentsTable $paymentsTable */
        $paymentsTable = $this->fetchTable('Payments');

        $request = $this->getRequest();
        $filterStatus = (string) $request->getQuery('status', '');
        $filterDate   = (string) $request->getQuery('date', '');
        $period       = (string) $request->getQuery('period', 'daily');

        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'daily';
        }

        $today = FrozenDate::now();
        $lastWeek = $today->subDays(7);

        // ── Top-level metrics ──────────────────────────────────────────────

        $revenueQuery = $paymentsTable->find();
        $totalRevenue = (float) ($revenueQuery
            ->where(['payment_status' => 'paid'])
            ->select(['total' => $revenueQuery->func()->sum('amount')])
            ->first()
            ->total ?? 0);

        // Revenue this week (last 7 days)
        $weekRevQ = $paymentsTable->find();
        $weekRevenue = (float) ($weekRevQ
            ->where(['payment_status' => 'paid', 'DATE(payment_date) >=' => $lastWeek->format('Y-m-d')])
            ->select(['total' => $weekRevQ->func()->sum('amount')])
            ->first()
            ->total ?? 0);

        // Revenue previous week (7–14 days ago)
        $prevWeekRevQ = $paymentsTable->find();
        $prevWeekRevenue = (float) ($prevWeekRevQ
            ->where([
                'payment_status' => 'paid',
                'DATE(payment_date) >=' => $lastWeek->subDays(7)->format('Y-m-d'),
                'DATE(payment_date) <' => $lastWeek->format('Y-m-d'),
            ])
            ->select(['total' => $prevWeekRevQ->func()->sum('amount')])
            ->first()
            ->total ?? 0);

        $revenueTrend = $prevWeekRevenue > 0
            ? round((($weekRevenue - $prevWeekRevenue) / $prevWeekRevenue) * 100, 1)
            : ($weekRevenue > 0 ? 100 : 0);

        $totalBookings = $bookingsTable->find()->count();

        // Bookings this week vs last week
        $weekBookings = $bookingsTable->find()
            ->where(['booking_date >=' => $lastWeek->format('Y-m-d')])
            ->count();
        $prevWeekBookings = $bookingsTable->find()
            ->where([
                'booking_date >=' => $lastWeek->subDays(7)->format('Y-m-d'),
                'booking_date <' => $lastWeek->format('Y-m-d'),
            ])
            ->count();
        $bookingsTrend = $prevWeekBookings > 0
            ? round((($weekBookings - $prevWeekBookings) / $prevWeekBookings) * 100, 1)
            : ($weekBookings > 0 ? 100 : 0);

        // Bookings grouped by status
        $statusQuery = $bookingsTable->find();
        $byStatus = $statusQuery
            ->select([
                'booking_status',
                'count' => $statusQuery->func()->count('*'),
            ])
            ->groupBy('booking_status')
            ->all()
            ->combine('booking_status', 'count')
            ->toArray();

        // Average booking value (confirmed only)
        $avgQuery = $bookingsTable->find();
        $avgBookingValue = (float) ($avgQuery
            ->where(['booking_status' => 'confirmed'])
            ->select(['avg' => $avgQuery->func()->avg('total_price')])
            ->first()
            ->avg ?? 0);

        // ── 7th metric: Today's check-ins (confirmed bookings today) ──────
        $todayCheckins = $bookingsTable->find()
            ->where([
                'booking_date' => $today->format('Y-m-d'),
                'booking_status' => 'confirmed',
            ])
            ->count();

        // ── Gallery count ──────────────────────────────────────────────────
        $galleryCount = $this->fetchTable('Galleries')->find()->count();

        // ── Revenue chart (period-aware) ───────────────────────────────────
        $revenueChart = $this->_buildRevenueChart($paymentsTable, $period, $today);
        $periodRevenue = (float) array_sum(array_column($revenueChart, 'value'));

        // ── Occupancy rate for today ───────────────────────────────────────
        $totalStudios = $this->fetchTable('Studios')->find()->count();
        $todayTotal  = $bookingsTable->find()
            ->where(['booking_date' => $today->format('Y-m-d')])
            ->count();
        $todayActive = $bookingsTable->find()
            ->where([
                'booking_date' => $today->format('Y-m-d'),
                'booking_status IN' => ['confirmed', 'pending'],
            ])
            ->count();

        $occupancyRate = $totalStudios > 0
            ? round(($todayActive / $totalStudios) * 100, 1)
            : 0;

        // ── Studio breakdown ───────────────────────────────────────────────
        $studiosTable = $this->fetchTable('Studios');
        $studioBreakdown = $studiosTable->find()
            ->select([
                'studio_id',
                'studio_name',
                'bookings_count' => $studiosTable->find()->func()->count('Bookings.booking_id'),
                'revenue' => $studiosTable->find()->func()->sum('Bookings.total_price'),
            ])
            ->leftJoinWith('Bookings', function ($q) {
                return $q->where(['Bookings.booking_status IN' => ['confirmed', 'pending', 'completed']]);
            })
            ->groupBy('Studios.studio_id')
            ->orderBy(['bookings_count' => 'DESC'])
            ->all()
            ->map(function ($row) {
                return [
                    'name' => $row->studio_name,
                    'bookings' => (int)($row->bookings_count ?? 0),
                    'revenue' => (float)($row->revenue ?? 0),
                ];
            })
            ->toArray();

        // ── Today's bookings (filterable) ──────────────────────────────────
        $todayQuery = $bookingsTable->find()
            ->contain(['Studios', 'Customers'])
            ->where(['booking_date' => $today->format('Y-m-d')])
            ->orderBy(['start_time' => 'ASC']);

        if ($filterStatus !== '') {
            $todayQuery->where(['booking_status' => $filterStatus]);
        }

        if ($filterDate !== '') {
            $todayQuery->where(['booking_date' => $filterDate]);
        }

        $todayBookings = $this->paginate($todayQuery, ['limit' => 20]);

        // ── Upcoming bookings (next 7 days) ────────────────────────────────
        $upcomingQuery = $bookingsTable->find()
            ->contain(['Studios', 'Customers'])
            ->where([
                'booking_date >' => $today->format('Y-m-d'),
                'booking_date <=' => $today->addDays(7)->format('Y-m-d'),
                'booking_status IN' => ['confirmed', 'pending'],
            ])
            ->orderBy(['booking_date' => 'ASC', 'start_time' => 'ASC']);

        $upcomingBookings = $this->paginate($upcomingQuery, ['limit' => 10, 'scope' => 'upcoming']);

        // ── Stats for cards ────────────────────────────────────────────────
        $stats = [
            'totalRevenue'    => $totalRevenue,
            'revenueTrend'    => $revenueTrend,
            'totalBookings'   => $totalBookings,
            'bookingsTrend'   => $bookingsTrend,
            'byStatus'        => $byStatus,
            'avgBookingValue' => $avgBookingValue,
            'occupancyRate'   => $occupancyRate,
            'todayCount'      => $todayTotal,
            'todayCheckins'   => $todayCheckins,
            'galleryCount'    => $galleryCount,
        ];

        $this->set(compact(
            'stats',
            'revenueChart',
            'period',
            'periodRevenue',
            'todayBookings',
            'upcomingBookings',
            'filterStatus',
            'filterDate',
            'today',
            'studioBreakdown',
        ));
    }

    /**
     * Build revenue chart data for the given period.
     *
     * @param \App\Model\Table\PaymentsTable $paymentsTable
     * @param string $period  daily|weekly|monthly|yearly
     * @param \Cake\I18n\FrozenDate $today
     * @return array  [['label','full','value'], ...]
     */
    private function _buildRevenueChart($paymentsTable, string $period, FrozenDate $today): array
    {
        $data = [];

        switch ($period) {
            case 'daily':
                // Last 14 days
                for ($i = 13; $i >= 0; $i--) {
                    $day = $today->subDays($i);
                    $data[] = [
                        'label' => $day->format('D'),       // Mon
                        'full'  => $day->format('j M'),     // 13 Jul
                        'value' => $this->_dayRevenue($paymentsTable, $day),
                    ];
                }
                break;

            case 'weekly':
                // Last 12 weeks
                $weekStart = new FrozenDate($today->format('Y-m-d'));
                // Find the Monday of the current week
                $dow = (int) $weekStart->format('N'); // 1=Mon .. 7=Sun
                $weekStart = $weekStart->subDays($dow - 1);

                for ($i = 11; $i >= 0; $i--) {
                    $start = clone $weekStart->subWeeks($i);
                    $end = (clone $start)->addDays(6);

                    $q = $paymentsTable->find();
                    $sum = (float) ($q
                        ->where([
                            'payment_status' => 'paid',
                            'DATE(payment_date) >=' => $start->format('Y-m-d'),
                            'DATE(payment_date) <=' => $end->format('Y-m-d'),
                        ])
                        ->select(['total' => $q->func()->sum('amount')])
                        ->first()
                        ->total ?? 0);

                    $data[] = [
                        'label' => 'W' . $start->format('W'),   // W28
                        'full'  => $start->format('j M') . '–' . $end->format('j M'),
                        'value' => $sum,
                    ];
                }
                break;

            case 'monthly':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $dt = (clone $today)->subMonths($i);
                    $monthStart = new FrozenDate($dt->format('Y-m') . '-01');
                    $monthEnd = $monthStart->addDays((int) $monthStart->format('t') - 1);

                    $q = $paymentsTable->find();
                    $sum = (float) ($q
                        ->where([
                            'payment_status' => 'paid',
                            'DATE(payment_date) >=' => $monthStart->format('Y-m-d'),
                            'DATE(payment_date) <=' => $monthEnd->format('Y-m-d'),
                        ])
                        ->select(['total' => $q->func()->sum('amount')])
                        ->first()
                        ->total ?? 0);

                    $data[] = [
                        'label' => $dt->format('M'),        // Jul
                        'full'  => $dt->format('F Y'),      // July 2026
                        'value' => $sum,
                    ];
                }
                break;

            case 'yearly':
                // All years that have payments
                $years = $paymentsTable->find()
                    ->select(['year' => $paymentsTable->find()->func()->year('payment_date')])
                    ->where(['payment_status' => 'paid'])
                    ->groupBy('year')
                    ->orderBy(['year' => 'ASC'])
                    ->all()
                    ->extract('year')
                    ->toArray();

                if (empty($years)) {
                    $years = [(int) $today->format('Y')];
                }

                foreach ($years as $year) {
                    $q = $paymentsTable->find();
                    $sum = (float) ($q
                        ->where([
                            'payment_status' => 'paid',
                            'YEAR(payment_date)' => $year,
                        ])
                        ->select(['total' => $q->func()->sum('amount')])
                        ->first()
                        ->total ?? 0);

                    $data[] = [
                        'label' => (string) $year,
                        'full'  => (string) $year,
                        'value' => $sum,
                    ];
                }
                break;
        }

        return $data;
    }

    /**
     * Helper: sum of paid payments for a single day.
     */
    private function _dayRevenue($paymentsTable, FrozenDate $day): float
    {
        $q = $paymentsTable->find();
        return (float) ($q
            ->where([
                'payment_status' => 'paid',
                'DATE(payment_date)' => $day->format('Y-m-d'),
            ])
            ->select(['total' => $q->func()->sum('amount')])
            ->first()
            ->total ?? 0);
    }
}
