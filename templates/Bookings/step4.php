<?php
/** @var array $step1 */
/** @var array $step2 */
/** @var array $step3 */
/** @var array $addons */
/** @var float $addonsTotal */
/** @var array $bundleDiscounts */
/** @var float $bundleDiscountTotal */
/** @var float $studioTotal */
/** @var float $totalPrice */
/** @var int $hours */
/** @var float $hourlyRate */
$this->assign('title', 'Payment');
$formattedDate = date('l, d F Y', strtotime($step1['booking_date']));
?>
<?= $this->Html->css('booking') ?>

<div class="booking-wrap">
  <!-- ========== PROGRESS BAR ========== -->
  <div class="progress-bar">
    <div class="progress-step completed">
      <span class="step-num">✓</span>
      <span class="step-label">Booking</span>
    </div>
    <div class="progress-line filled"></div>
    <div class="progress-step completed">
      <span class="step-num">✓</span>
      <span class="step-label">Studio Selection</span>
    </div>
    <div class="progress-line filled"></div>
    <div class="progress-step completed">
      <span class="step-num">✓</span>
      <span class="step-label">Add-ons</span>
    </div>
    <div class="progress-line filled"></div>
    <div class="progress-step completed">
      <span class="step-num">✓</span>
      <span class="step-label">User Information</span>
    </div>
    <div class="progress-line filled"></div>
    <div class="progress-step active">
      <span class="step-num">5</span>
      <span class="step-label">Payment</span>
    </div>
  </div>

  <div class="step-header">
    <h1>Complete Your Payment</h1>
    <p>Review your booking details and proceed with payment.</p>
  </div>

  <div class="payment-layout">
    <!-- LEFT: Booking Summary -->
    <div class="payment-summary">
      <div class="card summary-card">
        <h3 class="card-title accent-title">Booking Summary</h3>

        <div class="summary-section">
          <h4 class="summary-section-title">📅 Date & Time</h4>
          <div class="summary-row">
            <span class="summary-label">Date</span>
            <span class="summary-value"><?= h($formattedDate) ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Time</span>
            <span class="summary-value"><?= h(substr($step1['start_time'], 0, 5)) ?> – <?= h(substr($step1['end_time'], 0, 5)) ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Duration</span>
            <span class="summary-value"><?= $hours ?> hour<?= $hours > 1 ? 's' : '' ?></span>
          </div>
        </div>

        <div class="summary-section">
          <h4 class="summary-section-title">👤 Customer</h4>
          <div class="summary-row">
            <span class="summary-label">Name</span>
            <span class="summary-value"><?= h($step3['customer_name']) ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Phone</span>
            <span class="summary-value"><?= h($step3['phone_number']) ?></span>
          </div>
          <?php if (!empty($step3['email'])): ?>
          <div class="summary-row">
            <span class="summary-label">Email</span>
            <span class="summary-value"><?= h($step3['email']) ?></span>
          </div>
          <?php endif; ?>
          <div class="summary-row">
            <span class="summary-label">Pax</span>
            <span class="summary-value"><?= (int)$step3['pax'] ?> person<?= (int)$step3['pax'] > 1 ? 's' : '' ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Event</span>
            <span class="summary-value"><?= h($step3['event_type']) ?></span>
          </div>
        </div>

        <div class="summary-section">
          <h4 class="summary-section-title">🎬 Studio</h4>
          <div class="summary-row">
            <span class="summary-label">Studio</span>
            <span class="summary-value studio-value"><?= h($step2['studio_name']) ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-label">Capacity</span>
            <span class="summary-value">Up to <?= (int)$step2['capacity'] ?> pax</span>
          </div>
        </div>

        <?php if (!empty($addons)): ?>
        <div class="summary-section">
          <h4 class="summary-section-title">✨ Add-ons</h4>
          <?php foreach ($addons as $a):
            $qty = (int)($a['quantity'] ?? 1);
            $lineTotal = (float)$a['price'] * $qty;
          ?>
          <div class="summary-row">
            <span class="summary-label">
              <?= h($a['addon_name']) ?>
              <?php if ($qty > 1): ?><br><small style="color:var(--ink-muted);font-size:12px">× <?= $qty ?></small><?php endif; ?>
            </span>
            <span class="summary-value">RM<?= number_format($lineTotal, 2) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($step3['notes'])): ?>
        <div class="summary-section">
          <h4 class="summary-section-title">📝 Notes</h4>
          <p class="summary-notes"><?= h($step3['notes']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Price Breakdown & Payment -->
    <div class="payment-action">
      <div class="card price-card">
        <h3 class="card-title accent-title">Price Breakdown</h3>

        <div class="price-line">
          <span class="price-line-label">Studio Rental (<?= $hours ?> hr<?= $hours > 1 ? 's' : '' ?>)</span>
          <span class="price-line-value">RM<?= number_format($studioTotal, 2) ?></span>
        </div>
        <div class="price-line price-line-sub">
          <span class="price-line-label">RM<?= number_format($hourlyRate, 2) ?> × <?= $hours ?> hour<?= $hours > 1 ? 's' : '' ?></span>
        </div>

        <?php if (!empty($addons)): ?>
        <?php foreach ($addons as $a):
          $qty = (int)($a['quantity'] ?? 1);
          $lineTotal = (float)$a['price'] * $qty;
        ?>
        <div class="price-line">
          <span class="price-line-label">
            <?= h($a['addon_name']) ?>
            <?php if ($qty > 1): ?><br><small style="font-size:12px;color:var(--ink-muted)">× <?= $qty ?></small><?php endif; ?>
          </span>
          <span class="price-line-value">RM<?= number_format($lineTotal, 2) ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($bundleDiscounts)): ?>
        <?php foreach ($bundleDiscounts as $bd): ?>
        <div class="price-line" style="color:var(--green,#2e7d32)">
          <span class="price-line-label">🎉 Bundle: <?= h($bd['label']) ?></span>
          <span class="price-line-value" style="color:var(--green,#2e7d32)">-RM<?= number_format((float)$bd['discount_amount'], 2) ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="price-divider"></div>

        <div class="price-line price-total">
          <span class="price-total-label">Total</span>
          <span class="price-total-value">RM<?= number_format($totalPrice, 2) ?></span>
        </div>

        <div class="payment-info">
          <div class="payment-info-icon">🔒</div>
          <p>Your payment will be processed securely via <strong>ToyyibPay</strong>. You can pay using FPX, credit/debit card, e-wallet, and more.</p>
        </div>
      </div>

      <?= $this->Form->create(null, ['url' => ['action' => 'processPayment'], 'id' => 'paymentForm']) ?>
      <div class="form-actions payment-actions">
        <a href="<?= $this->Url->build(['action' => 'step3']) ?>" class="btn btn-back">← Back</a>
        <button type="submit" class="btn btn-pay" id="payNowBtn">
          <span class="pay-text">Pay Now</span>
          <span class="pay-amount">RM<?= number_format($totalPrice, 2) ?></span>
        </button>
      </div>
      <?= $this->Form->end() ?>
    </div>
  </div>
</div>

<script>
(function() {
  const payForm = document.getElementById('paymentForm');
  const payBtn = document.getElementById('payNowBtn');

  payForm.addEventListener('submit', function() {
    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="pay-text">Processing...</span><span class="pay-spinner">⟳</span>';
  });
})();
</script>
