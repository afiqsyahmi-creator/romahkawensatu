<?php
/** @var \App\Model\Entity\Studio $studio */
/** @var int|null $prevId */
/** @var int|null $nextId */
$this->assign('title', $studio->studio_name);
?>
<style>
.studio-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 32px;
  padding-top: 20px;
  border-top: 1px solid #d4c5a8;
}
.studio-nav a {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: "Jost", sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: #a8894e;
  text-decoration: none;
  padding: 8px 16px;
  border: 1px solid #d4c5a8;
  border-radius: 8px;
  background: linear-gradient(145deg, #fcf9f2, #f7f0e4);
  transition: all .25s;
}
.studio-nav a:hover {
  background: #c9a96e;
  border-color: #c9a96e;
  color: #fff;
}
.studio-nav .prev { margin-right: auto; }
.studio-nav .next { margin-left: auto; }
.studio-nav .spacer { flex: 1; }
</style>

<div class="wrap">
  <p class="eyebrow">Studio <?= h($studio->studio_id ?? '') ?></p>
  <h1 class="h"><?= h($studio->studio_name) ?></h1>

  <?php if (!empty($studio->galleries)): ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:32px">
      <?php foreach ($studio->galleries as $g): ?>
        <div style="border-radius:var(--radius);overflow:hidden;background:#f0ede6;aspect-ratio:4/3;width:100%">
          <img src="<?= $this->Url->build('/' . $g->image_path) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;background:#f0ede6" onerror="this.style.display='none'">
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:var(--muted-text);margin-top:32px">No images yet for this studio.</p>
  <?php endif; ?>

  <div class="studio-nav">
    <?php if ($prevId): ?>
      <a href="<?= $this->Url->build('/studios/' . $prevId) ?>" class="prev">← Previous</a>
    <?php else: ?>
      <span></span>
    <?php endif; ?>
    <?php if ($nextId): ?>
      <a href="<?= $this->Url->build('/studios/' . $nextId) ?>" class="next">Next →</a>
    <?php else: ?>
      <span></span>
    <?php endif; ?>
  </div>

  <a href="<?= $this->Url->build('/book') ?>" class="btn btn-primary" style="margin-top:24px;display:inline-flex">Book This Studio</a>
</div>