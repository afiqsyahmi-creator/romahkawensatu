<?php
/** @var \App\Model\Entity\BundleOffer $bundle */
/** @var iterable $addons */
?>
<div class="wrap" style="max-width:560px">
  <div class="eyebrow">Admin · Bundle Offers</div>
  <h1 class="h">Edit Bundle Offer</h1>
  <?= $this->Form->create($bundle) ?>
  <div class="panel">
    <label>Add-on 1</label>
    <?= $this->Form->select('addon_id_1', $addons->combine('addon_id', 'addon_name')->toArray(), ['empty' => '— Select —', 'required' => true]) ?>
    <label>Add-on 2</label>
    <?= $this->Form->select('addon_id_2', $addons->combine('addon_id', 'addon_name')->toArray(), ['empty' => '— Select —', 'required' => true]) ?>
    <label>Discount Amount (RM)</label>
    <?= $this->Form->control('discount_amount', ['type' => 'number', 'step' => '0.01', 'label' => false, 'required' => true]) ?>
    <label>Active</label>
    <?= $this->Form->checkbox('is_active', ['hiddenField' => false]) ?>
  </div>
  <?= $this->Form->button('Update bundle', ['class' => 'btn']) ?>
  <?= $this->Html->link('Cancel', ['action' => 'index'], ['style' => 'text-decoration:none;margin-left:8px']) ?>
  <?= $this->Form->end() ?>
</div>
