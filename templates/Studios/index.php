<?php
/**
 * @var iterable $studios
 * Each studio has galleries[] (ordered by sort_order ASC).
 * First gallery image = cover photo. Falls back to studio.image, then cream placeholder.
 */
use Cake\Routing\Router;

function _coverUrl($studio): string
{
    $path = '';
    if (!empty($studio->galleries[0]->image_path)) {
        $path = $studio->galleries[0]->image_path;
    } elseif (!empty($studio->image)) {
        $path = $studio->image;
    }
    if ($path) {
        return Router::url('/' . ltrim($path, '/'));
    }
    return '';
}
?>
<div class="wrap">
  <div class="eyebrow">Step 1 of 2</div>
  <h1 class="h">Choose your <em>package</em></h1>
  <p class="lead" style="white-space:nowrap">Explore our collection of seven uniquely themed studio sets, available for hourly rental with a two-hour minimum. Open daily 10:00 AM – 8:00 PM.</p>

  <div class="pkg-grid">
    <?php foreach ($studios as $s): ?>
      <?php $src = _coverUrl($s); ?>
      <article class="pkg">
        <div class="pkg-img">
          <?php if ($src): ?>
            <img src="<?= h($src) ?>" alt="<?= h($s->studio_name) ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none';this.parentElement.style.background='var(--cream)'">
          <?php else: ?>
            <div style="width:100%;height:100%;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--muted-text)">No image</div>
          <?php endif; ?>
        </div>
        <div class="pkg-body">
          <div class="eyebrow">Studio <?= str_pad((string)$s->studio_id, 2, '0', STR_PAD_LEFT) ?></div>
          <div class="pkg-name"><?= h($s->studio_name) ?></div>
          <div class="pkg-theme"><?= h($s->description) ?></div>
          <div class="pkg-rate">RM<?= number_format((float)$s->hourly_rate, 0) ?> /hr</div>
          <div class="pkg-from">from RM<?= number_format($s->min_price, 0) ?> (2 hrs)</div>
          <?= $this->Html->link('Book Now', ['controller' => 'Bookings', 'action' => 'step1'], ['class' => 'btn']) ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>
