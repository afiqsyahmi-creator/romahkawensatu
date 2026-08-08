<?php
/** @var array $formData */
$this->assign('title', 'Your Information');
$name = $formData['customer_name'] ?? '';
$phone = $formData['phone_number'] ?? '';
$email = $formData['email'] ?? '';
$pax = $formData['pax'] ?? '';
$eventType = $formData['event_type'] ?? '';
$notes = $formData['notes'] ?? '';
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
    <div class="progress-step active">
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
    <h1>Your Information</h1>
    <p>Please fill in your details so we can process your booking.</p>
  </div>

  <div class="form-layout">
    <div class="card form-card">
      <?= $this->Form->create(null, ['url' => ['action' => 'saveStep3'], 'id' => 'step3Form']) ?>

      <div class="form-group">
        <label for="customer_name">Full Name <span class="required">*</span></label>
        <?= $this->Form->text('customer_name', [
          'value' => $name,
          'id' => 'customer_name',
          'required' => true,
          'placeholder' => 'Enter your full name',
          'class' => 'form-input'
        ]) ?>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="phone_number">Phone Number <span class="required">*</span></label>
          <?= $this->Form->tel('phone_number', [
            'value' => $phone, 'id' => 'phone_number', 'required' => true,
            'placeholder' => 'e.g. 012-3456789', 'class' => 'form-input'
          ]) ?>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <?= $this->Form->email('email', [
            'value' => $email, 'id' => 'email',
            'placeholder' => 'your@email.com', 'class' => 'form-input'
          ]) ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="pax">Number of Pax <span class="required">*</span></label>
          <?= $this->Form->select('pax', [
            1 => '1 Person', 2 => '2 People', 3 => '3 People', 4 => '4 People',
            5 => '5 People', 6 => '6 People', 7 => '7 People', 8 => '8 People',
            10 => '10 People', 12 => '12 People',
          ], [
            'value' => $pax ? (int)$pax : '', 'id' => 'pax',
            'empty' => '— Select —', 'required' => true, 'class' => 'form-input'
          ]) ?>
        </div>
        <div class="form-group">
          <label for="event_type">Event Type <span class="required">*</span></label>
          <?= $this->Form->select('event_type', [
            'Wedding' => 'Wedding', 'Engagement' => 'Engagement',
            'Pre-wedding' => 'Pre-wedding', 'Birthday' => 'Birthday',
            'Family' => 'Family', 'Portrait' => 'Portrait',
            'Corporate' => 'Corporate', 'Fashion' => 'Fashion', 'Other' => 'Other',
          ], [
            'value' => $eventType, 'id' => 'event_type',
            'empty' => '— Select —', 'required' => true, 'class' => 'form-input'
          ]) ?>
        </div>
      </div>

      <div class="form-group">
        <label for="notes">Notes / Special Requests</label>
        <?= $this->Form->textarea('notes', [
          'value' => $notes, 'id' => 'notes',
          'placeholder' => 'Any special requests or additional information...',
          'class' => 'form-input form-textarea', 'rows' => 4
        ]) ?>
      </div>

      <div class="form-actions">
        <a href="<?= $this->Url->build(['action' => 'addons']) ?>" class="btn btn-back">← Back</a>
        <button type="submit" class="btn btn-continue">Continue →</button>
      </div>

      <?= $this->Form->end() ?>
    </div>
  </div>
</div>
