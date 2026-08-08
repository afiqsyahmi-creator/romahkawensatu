<?php /** @var \App\Model\Entity\Gallery $gallery @var array $studios */ ?>
<div class="wrap" style="max-width:560px">
  <div class="eyebrow">Admin · Gallery</div>
  <h1 class="h">Edit image</h1>
  <p style="margin-bottom:20px"><?= $this->Html->link('← Back to Gallery', ['action' => 'index'], ['class' => 'btn btn-ghost btn-sm']) ?></p>

  <?php if ($gallery->image_path): ?>
    <div style="margin-bottom:16px">
      <img src="/<?= h($gallery->image_path) ?>" width="120" height="80" style="object-fit:cover;border-radius:3px;border:1px solid var(--line)" onerror="this.style.display='none'">
    </div>
  <?php endif; ?>

  <?= $this->Form->create($gallery, ['type' => 'file']) ?>
  <div class="card" style="padding:28px">

    <div style="margin-bottom:18px">
      <label for="studio-id">Studio</label>
      <?= $this->Form->control('studio_id', [
        'type' => 'select', 'options' => $studios, 'label' => false, 'required' => true,
        'id' => 'studio-id', 'class' => 'filter-select', 'style' => 'width:100%',
      ]) ?>
    </div>

    <div style="margin-bottom:18px">
      <label for="image-file">Replace image <span style="font-weight:400;color:var(--muted-text)">(optional)</span></label>
      <?= $this->Form->control('image_file', [
        'type' => 'file', 'label' => false,
        'accept' => 'image/jpeg,image/png,image/webp,image/gif',
        'id' => 'image-file',
        'style' => 'font-family:"Jost",sans-serif;font-size:13px;padding:8px 0;width:100%',
      ]) ?>
      <small style="color:var(--muted-text);font-size:11px">JPEG, PNG, WEBP or GIF · Max 5 MB</small>
    </div>

    <div style="margin-bottom:18px;border-top:1px solid var(--line);padding-top:16px">
      <label for="image-path">Image path</label>
      <?= $this->Form->control('image_path', [
        'label' => false, 'id' => 'image-path',
        'class' => 'filter-date', 'style' => 'width:100%',
      ]) ?>
    </div>

    <div class="row2" style="margin-bottom:0">
      <div>
        <label for="caption">Caption</label>
        <?= $this->Form->control('caption', [
          'label' => false, 'id' => 'caption',
          'class' => 'filter-date', 'style' => 'width:100%',
        ]) ?>
      </div>
      <div>
        <label for="sort-order">Sort order</label>
        <?= $this->Form->control('sort_order', [
          'type' => 'number', 'label' => false, 'id' => 'sort-order',
          'class' => 'filter-date', 'style' => 'width:100%',
        ]) ?>
      </div>
    </div>

  </div>

  <div style="display:flex;gap:10px;margin-top:20px">
    <?= $this->Form->button('Update image', ['class' => 'btn btn-primary']) ?>
    <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
  </div>
  <?= $this->Form->end() ?>
</div>
