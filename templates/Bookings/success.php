<?php
/** @var \App\Model\Entity\Booking $booking */
$this->assign('title', 'Booking Confirmed');

$statusOk = $booking->booking_status === 'confirmed' || $booking->booking_status === 'pending';
$paymentStatus = !empty($booking->payments[0]) ? $booking->payments[0]->payment_status : 'unknown';
?>
<link rel="stylesheet" href="/css/booking.css?v=1">

<div class="booking-wrap">
  <div class="success-page">
    <div class="success-card">
      <?php if ($statusOk): ?>
      <div class="success-icon">✓</div>
      <h1>Booking <?= $booking->booking_status === 'confirmed' ? 'Confirmed' : 'Submitted' ?>!</h1>
      <p class="success-message">
        <?php if ($booking->booking_status === 'confirmed'): ?>
          Thank you! Your booking is confirmed. You'll receive a confirmation email shortly.
        <?php else: ?>
          Your booking has been submitted and is pending payment confirmation. We'll notify you once payment is verified.
        <?php endif; ?>
      </p>
      <?php else: ?>
      <div class="success-icon error">✕</div>
      <h1>Payment Not Completed</h1>
      <p class="success-message">Your payment was not completed. Please try again or contact us for assistance.</p>
      <?php endif; ?>

      <div class="success-details">
        <h3>Booking Reference</h3>
        <div class="ref-number">#<?= str_pad((string)$booking->booking_id, 5, '0', STR_PAD_LEFT) ?></div>

        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Studio</span>
            <span class="detail-value"><?= h($booking->studio->studio_name) ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date</span>
            <span class="detail-value"><?= $booking->booking_date->format('l, d F Y') ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Time</span>
            <span class="detail-value"><?= substr($booking->start_time, 0, 5) ?> – <?= substr($booking->end_time, 0, 5) ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Pax</span>
            <span class="detail-value"><?= (int)$booking->pax ?> person<?= (int)$booking->pax > 1 ? 's' : '' ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Total Paid</span>
            <span class="detail-value price">RM<?= number_format((float)$booking->total_price, 2) ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="detail-value status-<?= h($booking->booking_status) ?>"><?= ucfirst(h($booking->booking_status)) ?></span>
          </div>
        </div>
      </div>

      <div class="success-actions">
        <a href="<?= $this->Url->build(['controller' => 'Studios', 'action' => 'index']) ?>" class="btn">Browse More Studios</a>
        <a href="/" class="btn btn-back">Back to Home</a>
      </div>
    </div>
  </div>
</div>
