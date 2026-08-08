<?php /** @var \App\Model\Entity\Studio $studio @var array $statuses */ ?>
<div class="wrap" style="max-width:560px">
  <div class="eyebrow">Admin · Studios</div>
  <h1 class="h">New studio</h1>
  <?= $this->Form->create($studio, ['type' => 'file']) ?>
  <div class="panel">
    <label>Studio name</label><?= $this->Form->control('studio_name', ['label' => false, 'required' => true]) ?>
    <div class="row2">
      <div><label>Capacity</label><?= $this->Form->control('capacity', ['type' => 'number', 'label' => false, 'min' => 1]) ?></div>
      <div><label>Hourly rate (RM)</label><?= $this->Form->control('hourly_rate', ['type' => 'number', 'step' => '0.01', 'label' => false, 'required' => true]) ?></div>
    </div>
    <label>Description</label><?= $this->Form->control('description', ['type' => 'textarea', 'label' => false]) ?>

    <label>Upload cover image</label>
    <?= $this->Form->control('image_file', ['type' => 'file', 'label' => false, 'accept' => 'image/jpeg,image/png,image/webp']) ?>
    <small style="color:var(--muted-text);font-size:11px">JPEG, PNG or WEBP · Max 5 MB</small>

    <div style="margin-top:14px;border-top:1px solid var(--line);padding-top:14px">
      <label>…or enter image path</label>
      <?= $this->Form->control('image', ['label' => false, 'placeholder' => 'img/gallery/filename.jpg']) ?>
    </div>

    <label>Status</label><?= $this->Form->control('status', ['type' => 'select', 'options' => $statuses, 'label' => false]) ?>
  </div>
  <?= $this->Form->button('Save studio', ['class' => 'btn']) ?>
  <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'adminlink', 'style' => 'text-decoration:none;margin-left:8px']) ?>
  <?= $this->Form->end() ?>
</div>
