<?php /** @var \App\Model\Entity\Gallery $gallery @var array $studios @var int $suggestedOrder */ ?>
<div class="wrap" style="max-width:560px">
  <div class="eyebrow">Admin · Gallery</div>
  <h1 class="h">Add image</h1>
  <p style="margin-bottom:20px"><?= $this->Html->link('← Back to Gallery', ['action' => 'index'], ['class' => 'btn btn-ghost btn-sm']) ?></p>

  <?= $this->Form->create($gallery, ['type' => 'file']) ?>
  <div class="card" style="padding:28px">

    <div style="margin-bottom:18px">
      <label for="studio-id">Studio</label>
      <?= $this->Form->control('studio_id', [
        'type' => 'select',
        'options' => $studios,
        'label' => false,
        'required' => true,
        'empty' => '— Select a studio —',
        'id' => 'studio-id',
        'class' => 'filter-select',
        'style' => 'width:100%',
      ]) ?>
    </div>

    <div style="margin-bottom:18px">
      <label for="image-file">Upload image</label>
      <?= $this->Form->control('image_file', [
        'type' => 'file',
        'label' => false,
        'accept' => 'image/jpeg,image/png,image/webp,image/gif',
        'id' => 'image-file',
        'style' => 'font-family:"Jost",sans-serif;font-size:13px;padding:8px 0;width:100%',
      ]) ?>
      <small style="color:var(--muted-text);font-size:11px">JPEG, PNG, WEBP or GIF · Max 5 MB</small>
    </div>

    <div style="margin-bottom:18px;border-top:1px solid var(--line);padding-top:16px">
      <label for="image-path">…or enter an image path / URL</label>
      <?= $this->Form->control('image_path', [
        'label' => false,
        'placeholder' => 'img/gallery/filename.jpg',
        'id' => 'image-path',
        'class' => 'filter-date',
        'style' => 'width:100%',
      ]) ?>
      <small style="color:var(--muted-text);font-size:11px">Relative to webroot, e.g. <code>img/gallery/photo.jpg</code></small>
    </div>

    <div class="row2" style="margin-bottom:0">
      <div>
        <label for="caption">Caption</label>
        <?= $this->Form->control('caption', [
          'label' => false,
          'id' => 'caption',
          'class' => 'filter-date',
          'style' => 'width:100%',
        ]) ?>
      </div>
      <div>
        <label for="sort-order">Sort order</label>
        <?= $this->Form->control('sort_order', [
          'type' => 'number',
          'label' => false,
          'value' => $suggestedOrder ?: 0,
          'id' => 'sort-order',
          'class' => 'filter-date',
          'style' => 'width:100%',
        ]) ?>
      </div>
    </div>

  </div>

  <div style="display:flex;gap:10px;margin-top:20px">
    <?= $this->Form->button('Save image', ['class' => 'btn btn-primary']) ?>
    <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
  </div>
  <?= $this->Form->end() ?>
</div>
