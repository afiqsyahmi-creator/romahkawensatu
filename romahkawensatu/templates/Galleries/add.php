<?php /** @var \App\Model\Entity\Gallery $gallery @var iterable $studios */ ?>
<div class="wrap" style="max-width:600px">
  <div class="eyebrow">Share your experience</div>
  <h1 class="h">Upload a <em>photo</em></h1>
  <p class="lead">Had a great time at our studio? Share your photos with the community.</p>

  <?= $this->Form->create($gallery, ['type' => 'file']) ?>
  <div class="panel">

    <label for="studio-id">Which studio did you visit?</label>
    <?= $this->Form->control('studio_id', [
      'type' => 'select',
      'options' => $studios,
      'label' => false,
      'required' => true,
      'empty' => '— Select a studio —',
      'id' => 'studio-id',
    ]) ?>

    <label for="image-file">Your photo</label>
    <?= $this->Form->control('image_file', [
      'type' => 'file',
      'label' => false,
      'accept' => 'image/jpeg,image/png,image/webp,image/gif',
      'id' => 'image-file',
      'required' => true,
    ]) ?>
    <small style="color:var(--muted-text);font-size:12px">JPEG, PNG, WEBP or GIF · Max 5 MB</small>

    <label for="caption">Caption <small style="font-weight:400;color:var(--muted-text)">(optional)</small></label>
    <?= $this->Form->control('caption', [
      'label' => false,
      'id' => 'caption',
      'placeholder' => 'A nice memory from our session…',
    ]) ?>

  </div>

  <div style="display:flex;gap:10px;align-items:center">
    <?= $this->Form->button('Upload photo', ['class' => 'btn accent']) ?>
    <?= $this->Html->link('Cancel', '/', ['class' => 'adminlink', 'style' => 'text-decoration:none']) ?>
  </div>
  <?= $this->Form->end() ?>
</div>
