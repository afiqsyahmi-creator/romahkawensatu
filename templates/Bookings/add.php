<?php
/** @var \App\Model\Entity\Studio $studio @var array $payMethods */
// Photographer price: use the value passed from the controller if present,
// otherwise fall back to RM800 (matches the seeded add-on).
$photographerPrice = $photographerPrice ?? 800;
?>
<style>
/* live price breakdown */
.calc .calc-line{display:flex;justify-content:space-between;font-family:"Space Mono",monospace;font-size:13px;margin-bottom:8px}
.calc .calc-line span:first-child{color:var(--muted)}
.calc .calc-total{display:flex;justify-content:space-between;align-items:baseline;margin-top:12px;padding-top:14px;border-top:1.5px solid var(--ink)}
.calc .calc-total .tl{font-family:"Space Mono",monospace;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted)}
.calc .calc-total .tv{font-family:"Fraunces",serif;font-size:32px;font-weight:600;color:var(--accent);line-height:1}
</style>

<div class="wrap">
  <?= $this->Html->link('← Back to packages', ['controller' => 'Studios', 'action' => 'index'], ['class' => 'eyebrow', 'style' => 'text-decoration:none']) ?>
  <h1 class="h">Booking <em>details</em></h1>
  <p class="lead"><?= h($studio->studio_name) ?> · RM<?= number_format((float)$studio->hourly_rate, 0) ?>/hr · open 10:00–20:00 (last start 18:00). Pick a date to see what's already booked.</p>

  <div class="book-layout">
    <!-- LEFT: calendar -->
    <div>
      <div class="panel">
        <div class="section-label">Availability calendar</div>
        <div class="cal-head">
          <div class="m" id="calMonth"></div>
          <div class="cal-nav">
            <button type="button" id="calPrev" aria-label="Previous month">‹</button>
            <button type="button" id="calNext" aria-label="Next month">›</button>
          </div>
        </div>
        <div class="cal-grid" id="calGrid"></div>
        <div class="avail" id="availPanel">
          <h4 id="availTitle">Select a date</h4>
          <div id="availList"><p style="font-size:13px;color:var(--muted)">Click a day above to see existing bookings.</p></div>
        </div>
      </div>
    </div>

    <!-- RIGHT: booking form -->
    <aside>
      <?= $this->Form->create(null, ['type' => 'post']) ?>
      <div class="panel">
        <?= $this->Form->hidden('booking_date', ['id' => 'bdate']) ?>
        <div class="selected-date" id="selDateLabel">No date chosen yet</div>

        <div class="row2">
          <div><label>Name</label><?= $this->Form->control('customer_name', ['label' => false, 'required' => true]) ?></div>
          <div><label>Phone</label><?= $this->Form->control('phone_number', ['label' => false, 'required' => true]) ?></div>
        </div>
        <label>Email</label><?= $this->Form->control('email', ['type' => 'email', 'label' => false]) ?>

        <div class="row2">
          <div><label>Start time</label><?= $this->Form->control('start_time', ['type' => 'time', 'label' => false, 'value' => '10:00', 'min' => '10:00', 'max' => '18:00', 'step' => 1800, 'required' => true, 'id' => 'stime']) ?></div>
          <div><label>Hours (min 2)</label><?= $this->Form->control('hours', ['type' => 'number', 'label' => false, 'min' => 2, 'max' => 10, 'value' => 2, 'id' => 'hours']) ?></div>
        </div>
        <div class="dur-hint" id="durHint"></div>

        <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-family:inherit;font-size:14px">
          <?= $this->Form->checkbox('photographer', ['id' => 'photoCheck', 'style' => 'width:auto;margin:0']) ?> Add a photographer (+RM<?= number_format((float)$photographerPrice, 0) ?>, 2 hrs)
        </label>

        <label style="margin-top:14px">Payment method</label>
        <?= $this->Form->control('payment_method', ['type' => 'select', 'label' => false, 'options' => array_combine($payMethods, $payMethods)]) ?>
      </div>

      <!-- LIVE PRICE BREAKDOWN -->
      <div class="panel calc">
        <div class="section-label">Price</div>
        <div class="calc-line"><span id="calcRateLabel">Studio</span><span id="calcStudio">RM0</span></div>
        <div class="calc-line" id="calcPhotoLine" style="display:none"><span>Photographer</span><span id="calcPhoto">RM0</span></div>
        <div class="calc-total"><span class="tl">Total</span><span class="tv" id="calcTotal">RM0</span></div>
      </div>

      <?= $this->Form->button('Confirm &amp; pay', ['class' => 'btn accent', 'escapeTitle' => false, 'style' => 'width:100%']) ?>
      <?= $this->Form->end() ?>
    </aside>
  </div>
</div>

<script>
(function () {
  const OPEN = 600, CLOSE = 1200;               // 10:00–20:00 in minutes
  const RATE = <?= (float)$studio->hourly_rate ?>;
  const PHOTO = <?= (float)$photographerPrice ?>;
  const myStudio = <?= json_encode($studio->studio_name) ?>;

  const grid = document.getElementById('calGrid');
  const monthLbl = document.getElementById('calMonth');
  const availTitle = document.getElementById('availTitle');
  const availList = document.getElementById('availList');
  const bdate = document.getElementById('bdate');
  const selLabel = document.getElementById('selDateLabel');
  const stime = document.getElementById('stime');
  const hours = document.getElementById('hours');
  const durHint = document.getElementById('durHint');
  const photoCheck = document.getElementById('photoCheck');

  // price elements
  const calcRateLabel = document.getElementById('calcRateLabel');
  const calcStudio = document.getElementById('calcStudio');
  const calcPhotoLine = document.getElementById('calcPhotoLine');
  const calcPhoto = document.getElementById('calcPhoto');
  const calcTotal = document.getElementById('calcTotal');

  const today = new Date(); today.setHours(0,0,0,0);
  let view = new Date(today.getFullYear(), today.getMonth(), 1);
  let selected = null;
  let byDate = {};

  const pad = n => String(n).padStart(2,'0');
  const key = d => d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
  const toMin = t => { const [h,m]=t.split(':').map(Number); return h*60+m; };
  const rm = n => 'RM' + n.toLocaleString('en-MY');
  const fmtLong = d => d.toLocaleDateString('en-MY',{weekday:'short',day:'numeric',month:'short',year:'numeric'});

  // ---- live price calculation ----
  function recalc() {
    const h = Math.max(2, parseInt(hours.value || '2', 10));
    const studioCost = RATE * h;
    const withPhoto = photoCheck && photoCheck.checked;
    calcRateLabel.textContent = 'Studio · ' + rm(RATE) + ' \u00d7 ' + h;
    calcStudio.textContent = rm(studioCost);
    calcPhotoLine.style.display = withPhoto ? 'flex' : 'none';
    calcPhoto.textContent = rm(PHOTO);
    calcTotal.textContent = rm(studioCost + (withPhoto ? PHOTO : 0));
  }

  async function loadMonth() {
    const ym = view.getFullYear()+'-'+pad(view.getMonth()+1);
    byDate = {};
    try {
      const res = await fetch('/bookings/calendar-data?ym=' + ym, {headers:{'Accept':'application/json'}});
      const rows = await res.json();
      (rows.rows || rows).forEach(r => { (byDate[r.date] = byDate[r.date] || []).push(r); });
    } catch (e) {}
    renderGrid();
  }

  function renderGrid() {
    grid.innerHTML = '';
    monthLbl.textContent = view.toLocaleDateString('en-MY',{month:'long',year:'numeric'});
    ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d => {
      const hd = document.createElement('div'); hd.className='dow'; hd.textContent=d; grid.appendChild(hd);
    });
    const firstDay = new Date(view.getFullYear(), view.getMonth(), 1).getDay();
    const days = new Date(view.getFullYear(), view.getMonth()+1, 0).getDate();
    for (let i=0;i<firstDay;i++){ const e=document.createElement('div'); e.className='day empty'; grid.appendChild(e); }
    for (let d=1; d<=days; d++){
      const dt = new Date(view.getFullYear(), view.getMonth(), d);
      const k = key(dt);
      const btn = document.createElement('button');
      btn.type='button'; btn.className='day'; btn.innerHTML='<span>'+d+'</span>';
      if (dt.getTime()===today.getTime()) btn.classList.add('today');
      if (dt < today){ btn.classList.add('past'); btn.disabled=true; }
      if (byDate[k]) { const dot=document.createElement('span'); dot.className='bdot'; btn.appendChild(dot); }
      if (selected===k) btn.classList.add('sel');
      if (!btn.disabled) btn.addEventListener('click', () => selectDate(k, dt));
      grid.appendChild(btn);
    }
  }

  function selectDate(k, dt) {
    selected = k; bdate.value = k;
    selLabel.textContent = 'Selected: ' + fmtLong(dt);
    renderGrid(); renderAvail(k, dt);
  }

  function renderAvail(k, dt) {
    availTitle.textContent = 'Booked on ' + fmtLong(dt);
    const list = byDate[k] || [];
    if (!list.length) { availList.innerHTML = '<p class="avail-empty">All studios free this day — pick any time.</p>'; return; }
    availList.innerHTML = list.map(r => {
      const mine = r.studio === myStudio ? ' mine' : '';
      return '<div class="avail-row'+mine+'"><span>'+r.studio+'</span><span class="t">'+r.start+'–'+r.end+'</span></div>';
    }).join('');
  }

  function updateHint() {
    if (!stime.value) { durHint.classList.remove('show'); return; }
    const end = toMin(stime.value) + (parseInt(hours.value||'2',10))*60;
    if (end >= CLOSE) { durHint.classList.add('show'); durHint.textContent = 'Latest finish is 20:00 — that is the longest session from '+stime.value+'.'; }
    else durHint.classList.remove('show');
  }

  document.getElementById('calPrev').addEventListener('click', () => { view.setMonth(view.getMonth()-1); loadMonth(); });
  document.getElementById('calNext').addEventListener('click', () => { view.setMonth(view.getMonth()+1); loadMonth(); });
  stime.addEventListener('input', updateHint);
  hours.addEventListener('input', () => { updateHint(); recalc(); });
  if (photoCheck) photoCheck.addEventListener('change', recalc);

  loadMonth();
  recalc();
})();
</script>