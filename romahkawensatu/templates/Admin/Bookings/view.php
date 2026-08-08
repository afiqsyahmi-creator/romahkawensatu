<?php
/**
 * @var \App\Model\Entity\Booking $booking
 */
?>
<div class="wrap">
  <div class="eyebrow">Admin / Bookings</div>
  <h1 class="h">Booking #<?= h($booking->booking_id) ?></h1>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:700px">

    <div><strong>Status</strong><br><span class="badge <?= h($booking->booking_status) ?>"><?= h(ucfirst($booking->booking_status)) ?></span></div>
    <div><strong>Date</strong><br><?= h($booking->booking_date) ?></div>
    <div><strong>Time</strong><br><?= substr((string)$booking->start_time, 0, 5) ?> – <?= substr((string)$booking->end_time, 0, 5) ?></div>
    <div><strong>Studio</strong><br><?= h($booking->studio->studio_name ?? '—') ?></div>
    <div><strong>Customer</strong><br><?= h($booking->customer->customer_name ?? '—') ?></div>
    <div><strong>Phone</strong><br><?= h($booking->customer->phone_number ?? '—') ?></div>
    <div><strong>Email</strong><br><?= h($booking->customer->email ?? '—') ?></div>
    <div><strong>Pax</strong><br><?= h((string)($booking->pax ?? '—')) ?></div>
    <div><strong>Event type</strong><br><?= h($booking->event_type ?? '—') ?></div>
    <div><strong>Total price</strong><br>RM<?= number_format((float)$booking->total_price, 2) ?></div>

    <?php if (!empty($booking->notes)): ?>
      <div style="grid-column:1/-1"><strong>Notes</strong><br><?= nl2br(h($booking->notes)) ?></div>
    <?php endif; ?>
  </div>

  <div style="margin-top:20px;display:flex;gap:8px">
    <?= $this->Html->link('← Back to all bookings', ['action' => 'index'], ['class' => 'btn']) ?>
  </div>
</div>
