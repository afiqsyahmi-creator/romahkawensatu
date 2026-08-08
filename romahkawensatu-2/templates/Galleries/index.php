<?php
/** @var iterable $galleries */
/** @var array $studios */
?>
<style>
/* ── Filter Pills ── */
.gallery-filter {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
  margin-bottom: 28px;
}
.filter-pill {
  font-family: "Jost", sans-serif;
  font-size: 12.5px;
  font-weight: 500;
  padding: 7px 18px;
  border-radius: 20px;
  background: var(--card-bg);
  border: 1px solid var(--line);
  color: var(--muted-text);
  cursor: pointer;
  transition: all .25s;
}
.filter-pill:hover {
  border-color: var(--gold);
  color: var(--gold-dark);
  background: rgba(201,169,110,.08);
}
.filter-pill.active {
  background: var(--gold);
  color: #fff;
  border-color: var(--gold);
}

/* ── Gallery Grid ── */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-top: 8px;
}
@media (max-width: 800px) { .gallery-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .gallery-grid { grid-template-columns: 1fr; } }

.gallery-item {
  border-radius: 12px;
  overflow: hidden;
  background: #f0ede6;
  aspect-ratio: 4/3;
  width: 100%;
  position: relative;
}
.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .4s ease;
  background: #f0ede6;
}
.gallery-item:hover img {
  transform: scale(1.08);
}

/* ── Hover Overlay (exact spec) ── */
.gallery-item .overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top,
    rgba(32,28,23,0.88) 0%,
    rgba(32,28,23,0.15) 55%,
    transparent 100%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 20px;
  opacity: 0;
  transition: opacity .35s ease;
}
.gallery-item:hover .overlay {
  opacity: 1;
}
.gallery-item .overlay .label {
  font-family: "Jost", sans-serif;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #e4d3ac;
  margin-bottom: 4px;
  transform: translateY(8px);
  transition: transform .35s ease;
}
.gallery-item:hover .overlay .label {
  transform: translateY(0);
}
.gallery-item .overlay .caption {
  font-family: "Cormorant Garamond", serif;
  font-size: 17px;
  font-weight: 600;
  color: #fff;
  line-height: 1.3;
  transform: translateY(8px);
  transition: transform .35s ease .05s;
}
.gallery-item:hover .overlay .caption {
  transform: translateY(0);
}
</style>

<div class="wrap">
  <div class="eyebrow">Gallery</div>
  <h1 class="h">Moments from our <em>studios</em></h1>

  <!-- Studio Filter Pills -->
  <div class="gallery-filter" id="galleryFilter">
    <button class="filter-pill active" data-studio="all">All Studios</button>
    <?php foreach ($studios as $id => $name): ?>
      <button class="filter-pill" data-studio="<?= $id ?>"><?= h($name) ?></button>
    <?php endforeach; ?>
  </div>

  <!-- Gallery Grid -->
  <div class="gallery-grid" id="galleryGrid">
    <?php foreach ($galleries as $g): ?>
      <div class="gallery-item" data-studio="<?= $g->studio_id ?>">
        <img src="<?= $this->Url->build('/' . $g->image_path) ?>" alt="<?= h($g->caption ?: $g->studio->studio_name ?? '') ?>" loading="lazy" onerror="this.style.display='none'">
        <div class="overlay">
          <span class="label"><?= h($g->studio->studio_name ?? '') ?></span>
          <?php if ($g->caption): ?>
            <span class="caption"><?= h($g->caption) ?></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var filterBtns = document.querySelectorAll('.filter-pill');
  var params = new URLSearchParams(window.location.search);
  var preset = params.get('studio');

  filterBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      filterBtns.forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
      var studio = this.getAttribute('data-studio');

      // Filter gallery items + show empty state
      document.querySelectorAll('.gallery-item').forEach(function(item) {
        var match = (studio === 'all' || item.getAttribute('data-studio') === studio);
        item.style.display = match ? 'block' : 'none';
      });
    });
  });

  // Auto-select from URL param
  if (preset) {
    var target = document.querySelector('.filter-pill[data-studio="' + preset + '"]');
    if (target) target.click();
  }
});
</script>
