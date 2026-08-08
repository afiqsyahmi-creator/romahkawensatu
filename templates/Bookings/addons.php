<?php
/**
 * @var iterable $addons
 * @var array $saved
 * @var array $bundles
 * @var array $scarcity  [addon_id => remaining_capacity]
 */
$this->assign('title', 'Add-ons');
$savedIds = array_map(fn($s) => $s['addon_id'] ?? null, $saved);
$savedQtyMap = [];
foreach ($saved as $s) {
    $savedQtyMap[$s['addon_id']] = (int)($s['quantity'] ?? 1);
}
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
    <div class="progress-step active">
      <span class="step-num">3</span>
      <span class="step-label">Add-ons</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">4</span>
      <span class="step-label">User Information</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">5</span>
      <span class="step-label">Payment</span>
    </div>
  </div>



  <?= $this->Form->create(null, ['url' => ['action' => 'saveAddons'], 'id' => 'addonsForm']) ?>

  <!-- ========== BUNDLE HINT (shown when applicable) ========== -->
  <?php if (!empty($bundles)): ?>
  <div class="bundles-strip" id="bundlesStrip">
    <?php foreach ($bundles as $b): ?>
    <div class="bundle-hint" data-a1="<?= (int)$b->addon_id_1 ?>" data-a2="<?= (int)$b->addon_id_2 ?>" data-discount="<?= (float)$b->discount_amount ?>" style="display:none">
      <span class="bundle-hint-icon">🎉</span>
      <span class="bundle-hint-text">
        <strong>Bundle offer:</strong>
        <?= h($b->addon_1->addon_name ?? '') ?> + <?= h($b->addon_2->addon_name ?? '') ?>
        — save <strong>RM<?= number_format((float)$b->discount_amount, 0) ?></strong>!
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ========== ADD-ONS GRID ========== -->
  <div class="addons-grid">
    <?php if (count($addons) > 0): ?>
      <?php foreach ($addons as $a):
        $isSelected = in_array($a->addon_id, $savedIds);
        $isQuantity = ($a->selection_type ?? 'toggle') === 'quantity';
        $maxQty = (int)($a->max_per_booking ?? 1);
        $initialQty = $savedQtyMap[$a->addon_id] ?? 1;
        $remaining = $scarcity[$a->addon_id] ?? null;
        $isUnavailable = ($remaining !== null && $remaining < 1);
        $icon = match ($a->addon_type) {
          'photography' => '📸',
          'styling' => '💄',
          'catering' => '🍽️',
          'decoration' => '🎀',
          'videography' => '🎥',
          'music' => '🎵',
          'time' => '⏱️',
          default => '✨',
        };
      ?>
      <div class="addon-card <?= $isSelected ? 'selected' : '' ?> <?= $isUnavailable ? 'unavailable' : '' ?>" data-id="<?= $a->addon_id ?>" data-price="<?= (float)$a->price ?>" data-type="<?= $isQuantity ? 'quantity' : 'toggle' ?>" data-maxqty="<?= $maxQty ?>">
        <!-- Decorative corner accent -->
        <div class="addon-accent"></div>

        <!-- Most Popular badge (data-driven) -->
        <?php if ($a->is_popular): ?>
        <div class="addon-badge-popular">⭐ Most Popular</div>
        <?php endif; ?>

        <div class="addon-card-inner">
          <!-- Header: Icon + Price -->
          <div class="addon-header">
            <div class="addon-icon-wrap">
              <span class="addon-icon"><?= $icon ?></span>
            </div>
            <div class="addon-price-tag">
              <span class="addon-price-currency">RM</span>
              <span class="addon-price-value"><?= number_format((float)$a->price, 0) ?></span>
            </div>
          </div>

          <!-- Info -->
          <div class="addon-info">
            <h3 class="addon-name"><?= h($a->addon_name) ?></h3>
            <span class="addon-type-label"><?= h(ucfirst($a->addon_type)) ?></span>
            <p class="addon-desc"><?= h($a->description) ?></p>

            <!-- Scarcity message (real data only) -->
            <?php if ($remaining !== null && $remaining > 0 && $remaining <= 3): ?>
            <div class="addon-scarcity">
              <span class="scarcity-dot"></span>
              Only <?= $remaining ?> slot<?= $remaining > 1 ? 's' : '' ?> left this week
            </div>
            <?php elseif ($isUnavailable): ?>
            <div class="addon-scarcity unavailable">
              <span class="scarcity-dot"></span>
              Currently unavailable
            </div>
            <?php endif; ?>
          </div>

          <!-- Action: Toggle or Quantity Stepper -->
          <div class="addon-action">
            <?php if ($isQuantity && !$isUnavailable): ?>
              <!-- Quantity stepper -->
              <div class="qty-stepper" data-addon-id="<?= $a->addon_id ?>">
                <button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">−</button>
                <input type="number" name="quantities[<?= $a->addon_id ?>]" value="<?= $isSelected ? $initialQty : 1 ?>" min="1" max="<?= $maxQty ?>" class="qty-input" readonly>
                <button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>
              </div>
              <!-- Hidden checkbox to track selection -->
              <input type="checkbox" name="addons[]" value="<?= $a->addon_id ?>" id="addon_<?= $a->addon_id ?>" <?= $isSelected ? 'checked' : '' ?> class="qty-addon-checkbox">
            <?php elseif (!$isUnavailable): ?>
              <!-- Toggle switch -->
              <input type="checkbox" name="addons[]" value="<?= $a->addon_id ?>" id="addon_<?= $a->addon_id ?>" <?= $isSelected ? 'checked' : '' ?>>
              <label for="addon_<?= $a->addon_id ?>" class="addon-toggle">
                <span class="toggle-track">
                  <span class="toggle-thumb"></span>
                </span>
                <span class="toggle-text"><?= $isSelected ? 'Added' : 'Add to booking' ?></span>
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="addons-empty">
        <div class="addons-empty-icon">✨</div>
        <h3>No Add-ons Available</h3>
        <p>There are no add-ons right now. Use the skip option below to continue.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- ========== SKIP SECTION (shown when nothing selected) ========== -->
  <div class="skip-section" id="skipSection">
    <div class="skip-section-divider">
      <span class="skip-divider-line"></span>
      <span class="skip-divider-icon">✦</span>
      <span class="skip-divider-line"></span>
    </div>
    <div class="skip-section-body">
      <div class="skip-body-icon">↷</div>
      <h3 class="skip-heading">Not interested in add-ons?</h3>
      <p class="skip-text">That's perfectly fine! You can skip this step and proceed straight to your details.</p>
      <a href="<?= $this->Url->build(['action' => 'step3']) ?>" class="btn btn-skip-enhanced">
        <span class="skip-btn-bg"></span>
        <span class="skip-btn-content">
          <span class="skip-btn-arrow">
            <span class="arrow-main">→</span>
            <span class="arrow-trail">→</span>
          </span>
          <span class="skip-btn-label">
            <strong>Skip this step</strong>
            <span class="skip-btn-sub">Continue to your details</span>
          </span>
        </span>
      </a>
    </div>
  </div>

  <!-- ========== STICKY SUMMARY BAR ========== -->
  <div class="addons-summary-bar" id="addonsSummary" style="display:none">
    <div class="summary-bar-inner">
      <!-- Selected state -->
      <div class="summary-bar-left" id="summarySelected">
        <span class="summary-bar-count" id="selectedCount">0</span>
        <span class="summary-bar-label">add-on<span id="pluralSuffix">s</span> selected</span>
      </div>
      <div class="summary-bar-right">
        <div class="summary-bar-total">
          <span class="summary-bar-total-label">Total:</span>
          <span class="summary-bar-total-value" id="selectedTotal">RM0</span>
        </div>
        <!-- Bundle discount shown here when active -->
        <div class="summary-bar-bundle" id="summaryBundle" style="display:none">
          <span class="summary-bundle-label">Bundle discount:</span>
          <span class="summary-bundle-value" id="summaryBundleValue">-RM0</span>
        </div>
        <div class="summary-bar-actions">
          <a href="<?= $this->Url->build(['action' => 'step2']) ?>" class="btn btn-back btn-sm">← Back</a>
          <button type="submit" class="btn btn-continue btn-sm">
            Continue →
          </button>
        </div>
      </div>
    </div>
  </div>

  <?= $this->Form->end() ?>
</div>

<script>
(function() {
  const cards = document.querySelectorAll('.addon-card');
  const countEl = document.getElementById('selectedCount');
  const totalEl = document.getElementById('selectedTotal');
  const pluralEl = document.getElementById('pluralSuffix');
  const summaryBar = document.getElementById('addonsSummary');
  const summaryBundle = document.getElementById('summaryBundle');
  const summaryBundleValue = document.getElementById('summaryBundleValue');
  const skipSection = document.getElementById('skipSection');

  // Bundle data from server
  const bundles = <?= json_encode(array_map(fn($b) => [
    'a1' => (int)$b->addon_id_1,
    'a2' => (int)$b->addon_id_2,
    'discount' => (float)$b->discount_amount,
    'label' => ($b->addon_1->addon_name ?? '') . ' + ' . ($b->addon_2->addon_name ?? ''),
  ], $bundles)) ?>;

  const bundleHints = document.querySelectorAll('.bundle-hint');

  // ─── Live recalculation ───
  function updateSummary() {
    let count = 0;
    let total = 0;
    let selectedIds = [];

    cards.forEach(card => {
      const checkbox = card.querySelector('input[type="checkbox"]');
      if (!checkbox) return;

      // For quantity cards, also grab the qty input
      let qty = 1;
      const qtyInput = card.querySelector('.qty-input');
      if (qtyInput) qty = parseInt(qtyInput.value) || 1;

      if (checkbox.checked) {
        count += qty;
        const price = parseFloat(card.dataset.price) || 0;
        total += price * qty;
        selectedIds.push(parseInt(card.dataset.id));
        // Ensure qty input is enabled
        if (qtyInput) qtyInput.readOnly = false;
      } else {
        if (qtyInput) qtyInput.readOnly = true;
      }
    });

    // Bundle discounts
    let bundleDiscount = 0;
    let bundleLabel = '';
    bundles.forEach(b => {
      if (selectedIds.includes(b.a1) && selectedIds.includes(b.a2)) {
        bundleDiscount += b.discount;
        bundleLabel = b.label;
      }
    });

    // Apply discount
    const finalTotal = total - bundleDiscount;

    // Update UI
    countEl.textContent = count;
    totalEl.textContent = 'RM' + finalTotal.toLocaleString('en-MY', {minimumFractionDigits: 0});
    pluralEl.textContent = count === 1 ? '' : 's';

    // Toggle skip section vs sticky summary bar
    const rawCount = document.querySelectorAll('.addon-card input[type="checkbox"]:checked').length;
    const hasSelection = rawCount > 0;
    skipSection.style.display = hasSelection ? 'none' : '';
    summaryBar.style.display = hasSelection ? 'block' : 'none';

    // Bundle discount display
    if (bundleDiscount > 0) {
      summaryBundle.style.display = 'flex';
      summaryBundleValue.textContent = '-RM' + bundleDiscount.toLocaleString('en-MY', {minimumFractionDigits: 0});
    } else {
      summaryBundle.style.display = 'none';
    }

    // Bundle hints
    bundleHints.forEach(hint => {
      const a1 = parseInt(hint.dataset.a1);
      const a2 = parseInt(hint.dataset.a2);
      hint.style.display = (selectedIds.includes(a1) && selectedIds.includes(a2)) ? 'flex' : 'none';
    });
  }

  // ─── Quantity stepper logic ───
  document.querySelectorAll('.qty-stepper').forEach(stepper => {
    const minus = stepper.querySelector('.qty-minus');
    const plus = stepper.querySelector('.qty-plus');
    const input = stepper.querySelector('.qty-input');
    const addonId = stepper.dataset.addonId;
    const card = stepper.closest('.addon-card');
    const checkbox = card ? card.querySelector('.qty-addon-checkbox') : null;
    const maxQty = parseInt(card ? card.dataset.maxqty : 1);

    // Click on the card body toggles selection for quantity add-ons
    if (card) {
      card.addEventListener('click', function(e) {
        if (e.target.closest('.qty-stepper') || e.target.closest('input')) return;
        if (!checkbox) return;
        checkbox.checked = !checkbox.checked;
        updateCardState(card, checkbox);
        updateSummary();
      });
    }

    minus.addEventListener('click', function(e) {
      e.stopPropagation();
      let val = parseInt(input.value) || 1;
      if (val > 1) {
        input.value = val - 1;
        // Auto-select if not already selected
        if (checkbox && !checkbox.checked) {
          checkbox.checked = true;
          if (card) updateCardState(card, checkbox);
        }
        updateSummary();
      }
    });

    plus.addEventListener('click', function(e) {
      e.stopPropagation();
      let val = parseInt(input.value) || 1;
      if (val < maxQty) {
        input.value = val + 1;
        // Auto-select if not already selected
        if (checkbox && !checkbox.checked) {
          checkbox.checked = true;
          if (card) updateCardState(card, checkbox);
        }
        updateSummary();
      }
    });

    // Also listen for direct input changes
    input.addEventListener('change', function() {
      let val = parseInt(input.value) || 1;
      if (val < 1) val = 1;
      if (val > maxQty) val = maxQty;
      input.value = val;
      updateSummary();
    });
  });

  // ─── Toggle cards ───
  cards.forEach(card => {
    const checkbox = card.querySelector('input[type="checkbox"]');
    if (!checkbox) return;
    const toggleText = card.querySelector('.toggle-text');
    const qtyInput = card.querySelector('.qty-input');

    // If it's a quantity card, the qty-stepper handles clicks
    if (card.querySelector('.qty-stepper')) return;

    // Click on card body toggles
    card.addEventListener('click', function(e) {
      if (e.target.closest('.addon-toggle') || e.target.closest('input')) return;
      checkbox.checked = !checkbox.checked;
      updateCardState(card, checkbox);
      updateSummary();
    });

    // Checkbox change
    checkbox.addEventListener('change', function() {
      updateCardState(card, checkbox);
      updateSummary();
    });
  });

  function updateCardState(card, checkbox) {
    const toggleText = card.querySelector('.toggle-text');
    if (checkbox.checked) {
      card.classList.add('selected');
      if (toggleText) toggleText.textContent = 'Added';
    } else {
      card.classList.remove('selected');
      if (toggleText) toggleText.textContent = 'Add to booking';
    }
  }

  // ─── Initial summary ───
  updateSummary();
})();
</script>
