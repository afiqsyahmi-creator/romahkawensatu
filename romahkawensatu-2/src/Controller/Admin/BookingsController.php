<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class BookingsController extends AppController
{
    public function index(): void
    {
        $bookings = $this->fetchTable('Bookings')->find()
            ->contain(['Customers', 'Studios', 'Payments'])
            ->orderBy(['booking_date' => 'DESC', 'start_time' => 'ASC']);
        $this->set('bookings', $this->paginate($bookings));
    }

    public function view($id = null)
    {
        $booking = $this->fetchTable('Bookings')->get($id, [
            'contain' => ['Customers', 'Studios', 'Payments', 'Addons'],
        ]);
        $this->set(compact('booking'));
    }

    public function changeStatus($id)
    {
        $this->request->allowMethod(['post']);
        $t = $this->fetchTable('Bookings');
        $booking = $t->get($id);
        $booking->booking_status = $this->request->getData('status');
        $t->save($booking)
            ? $this->Flash->success('Status updated.')
            : $this->Flash->error('Could not update.');
        return $this->redirect(['action' => 'index']);
    }
}
