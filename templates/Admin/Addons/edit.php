<?php
/** @var \App\Model\Entity\Addon $addon */
/** @var array $statuses */
/** @var array $selectionTypes */
?>
<div class="wrap" style="max-width:640px">
  <div class="eyebrow">Admin · Add-ons</div>
  <h1 class="h">Edit add-on</h1>
  <?= $this->Form->create($addon) ?>
  <div class="panel">
    <label>Add-on name</label><?= $this->Form->control('addon_name', ['label' => false, 'required' => true]) ?>
    <div class="row2">
      <div><label>Type</label><?= $this->Form->control('addon_type', ['label' => false]) ?></div>
      <div><label>Price (RM)</label><?= $this->Form->control('price', ['type' => 'number', 'step' => '0.01', 'label' => false, 'required' => true]) ?></div>
    </div>
    <label>Description</label><?= $this->Form->control('description', ['type' => 'textarea', 'label' => false]) ?>
    <div class="row2">
      <div><label>Selection Type</label><?= $this->Form->control('selection_type', ['type' => 'select', 'options' => $selectionTypes, 'label' => false]) ?></div>
      <div><label>Max per Booking</label><?= $this->Form->control('max_per_booking', ['type' => 'number', 'min' => 1, 'label' => false]) ?></div>
    </div>
    <div class="row2">
      <div><label>Weekly Capacity (blank = unlimited)</label><?= $this->Form->control('weekly_capacity', ['type' => 'number', 'min' => 0, 'label' => false, 'placeholder' => 'Unlimited']) ?></div>
      <div><label>Status</label><?= $this->Form->control('status', ['type' => 'select', 'options' => $statuses, 'label' => false]) ?></div>
    </div>
  </div>
  <?= $this->Form->button('Update add-on', ['class' => 'btn']) ?>
  <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'adminlink', 'style' => 'text-decoration:none;margin-left:8px']) ?>
  <?= $this->Form->end() ?>
</div>
