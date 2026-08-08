<?php
/** @var iterable $studios */
$this->assign('title', 'Select Studio');
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
    <div class="progress-step active">
      <span class="step-num">2</span>
      <span class="step-label">Studio Selection</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
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

  <div class="step-header">
    <h1>Choose Your Studio</h1>
    <p>Select from our beautifully themed studio sets for your perfect session.</p>
  </div>

  <?= $this->Form->create(null, ['url' => ['action' => 'saveStep2'], 'id' => 'step2Form']) ?>
  <?= $this->Form->hidden('studio_id', ['id' => 'studioId']) ?>

  <div class="studio-grid">
    <?php foreach ($studios as $s):
      // Cover: first gallery image → studio.image → placeholder
      $cover = '';
      if (!empty($s->galleries[0]->image_path)) {
          $cover = \Cake\Routing\Router::url('/' . ltrim($s->galleries[0]->image_path, '/'));
      } elseif (!empty($s->image)) {
          $cover = \Cake\Routing\Router::url('/' . ltrim($s->image, '/'));
      }
      $minPrice = (float)$s->hourly_rate * 2;
    ?>
    <div class="studio-card" data-id="<?= $s->studio_id ?>" data-name="<?= h($s->studio_name) ?>" data-rate="<?= (float)$s->hourly_rate ?>" data-capacity="<?= (int)$s->capacity ?>">
      <div class="studio-img">
        <?php if ($cover): ?>
          <img src="<?= h($cover) ?>" alt="<?= h($s->studio_name) ?>" onerror="this.style.display='none';this.parentElement.style.background='var(--cream)'">
        <?php else: ?>
          <div class="studio-img-placeholder">
            <span><?= h(substr($s->studio_name, 0, 2)) ?></span>
          </div>
        <?php endif; ?>
      </div>
      <div class="studio-body">
        <h3 class="studio-name"><?= h($s->studio_name) ?></h3>
        <p class="studio-desc"><?= h($s->description) ?></p>
        <div class="studio-meta">
          <div class="meta-item">
            <span class="meta-icon">👥</span>
            <span class="meta-text">Up to <?= (int)$s->capacity ?> pax</span>
          </div>
          <div class="meta-item">
            <span class="meta-icon">⏰</span>
            <span class="meta-text">10:00 – 20:00</span>
          </div>
        </div>
        <div class="studio-price">
          <span class="price-rate">RM<?= number_format((float)$s->hourly_rate, 0) ?></span>
          <span class="price-label">/hour</span>
        </div>
        <div class="studio-from">from RM<?= number_format($minPrice, 0) ?> (2 hrs)</div>
        <button type="button" class="btn btn-select-studio" data-id="<?= $s->studio_id ?>">Select Studio</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

<div class="form-actions">
    <a href="<?= $this->Url->build(['action' => 'step1']) ?>" class="btn btn-back">← Back</a>
    <button type="submit" class="btn btn-continue" id="step2Continue" disabled>Continue →</button>
  </div>

  <?= $this->Form->end() ?>
</div>

<script>
(function() {
  const studioId = document.getElementById('studioId');
  const continueBtn = document.getElementById('step2Continue');
  let selected = null;

  document.querySelectorAll('.btn-select-studio').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.dataset.id;
      const card = this.closest('.studio-card');

      document.querySelectorAll('.studio-card').forEach(c => c.classList.remove('selected'));
      document.querySelectorAll('.btn-select-studio').forEach(b => b.textContent = 'Select Studio');

      card.classList.add('selected');
      this.textContent = 'Selected ✓';
      studioId.value = id;
      selected = id;
      continueBtn.disabled = false;
    });
  });
})();
</script>
