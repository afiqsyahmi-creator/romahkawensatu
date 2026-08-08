<?php
/** @var array $studios */
/** @var array $studioTypes */

use Cake\I18n\Date;

$today = new Date();
$this->assign('title', 'Book a Studio');
?>
<?= $this->Html->css('booking') ?>

<div class="booking-wrap">
  <!-- ========== PROGRESS BAR ========== -->
  <div class="progress-bar">
    <div class="progress-step active">
      <span class="step-num">1</span>
      <span class="step-label">Booking</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">2</span>
      <span class="step-label">Studio Selection</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">3</span>
      <span class="step-label">Add-ons</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">4</span>
      <span class="step-label">User Information</span>
    </div>
    <div class="progress-line"></div>
    <div class="progress-step">
      <span class="step-num">5</span>
      <span class="step-label">Payment</span>
    </div>
  </div>

  <div class="step-header">
    <h1>Select Date &amp; Time</h1>
    <p>Choose your preferred date and time slot for the studio session.</p>
  </div>

  <?= $this->Form->create(null, ['url' => ['action' => 'saveStep1'], 'id' => 'step1Form']) ?>
  <?= $this->Form->hidden('booking_date', ['id' => 'bdate']) ?>
  <?= $this->Form->hidden('start_time', ['id' => 'stime']) ?>
  <?= $this->Form->hidden('hours', ['id' => 'hours', 'value' => 2]) ?>

  <div class="booking-layout">
    <!-- ========== LEFT FILTER SIDEBAR ========== -->
    <aside class="filter-sidebar">
      <h3 class="filter-title">Filters</h3>

      <div class="filter-group">
        <label>Studio Type</label>
        <select id="filterStudio" class="filter-select">
          <option value="">All Studios</option>
          <?php foreach ($studioTypes as $type): ?>
            <option value="<?= h($type) ?>"><?= h($type) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <label>Package</label>
        <select id="filterPackage" class="filter-select">
          <option value="">All Packages</option>
          <option value="2">2 Hours (Min)</option>
          <option value="3">3 Hours</option>
          <option value="4">4 Hours</option>
          <option value="6">6 Hours</option>
          <option value="8">8 Hours</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Number of Pax</label>
        <select id="filterPax" class="filter-select">
          <option value="">Any</option>
          <option value="1">1 Person</option>
          <option value="2">2 People</option>
          <option value="4">Up to 4</option>
          <option value="6">Up to 6</option>
          <option value="8">Up to 8</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Price Range (per hour)</label>
        <div class="price-range">
          <input type="range" id="priceMin" min="80" max="200" value="80" class="range-slider">
          <input type="range" id="priceMax" min="80" max="200" value="200" class="range-slider">
        </div>
        <div class="range-labels">
          <span id="priceMinLabel">RM80</span>
          <span id="priceMaxLabel">RM200</span>
        </div>
      </div>

      <div class="filter-group">
        <label>Available Date</label>
        <div class="date-presets">
          <button type="button" class="date-preset" data-days="0">Today</button>
          <button type="button" class="date-preset" data-days="1">Tomorrow</button>
          <button type="button" class="date-preset" data-days="7">This Week</button>
          <button type="button" class="date-preset" data-days="30">This Month</button>
        </div>
      </div>

      <div class="filter-group">
        <label>Available Time</label>
        <div class="time-presets">
          <button type="button" class="time-preset" data-time="morning">Morning<br><small>10AM–12PM</small></button>
          <button type="button" class="time-preset" data-time="afternoon">Afternoon<br><small>12PM–5PM</small></button>
          <button type="button" class="time-preset" data-time="evening">Evening<br><small>5PM–8PM</small></button>
        </div>
      </div>

      <button type="button" class="btn btn-clear-filters" id="clearFilters">Clear All Filters</button>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="booking-main">
      <!-- Calendar Section -->
      <div class="card cal-card">
        <div class="cal-head">
          <button type="button" id="calPrev" class="cal-nav-btn" aria-label="Previous month">‹</button>
          <h2 class="cal-month" id="calMonth"></h2>
          <button type="button" id="calNext" class="cal-nav-btn" aria-label="Next month">›</button>
        </div>
        <div class="cal-grid" id="calGrid">
          <!-- Days rendered by JS -->
        </div>
      </div>

      <!-- Time Slot Selection -->
      <div class="card time-card" id="timeSlotSection" style="display:none">
        <h3 class="card-title">Select Time Slot</h3>
        <p class="selected-date-label" id="selectedDateLabel">Choose a date above first.</p>
        <div class="time-slots" id="timeSlots">
          <!-- Time slots rendered by JS -->
        </div>
      </div>

      <!-- Duration Selector -->
      <div class="card duration-card" id="durationSection" style="display:none">
        <h3 class="card-title">Session Duration</h3>
        <div class="duration-options" id="durationOptions">
          <button type="button" class="duration-btn selected" data-hours="2">2 Hours<br><small>RM0</small></button>
          <button type="button" class="duration-btn" data-hours="3">3 Hours<br><small>RM0</small></button>
          <button type="button" class="duration-btn" data-hours="4">4 Hours<br><small>RM0</small></button>
          <button type="button" class="duration-btn" data-hours="6">6 Hours<br><small>RM0</small></button>
          <button type="button" class="duration-btn" data-hours="8">8 Hours<br><small>RM0</small></button>
        </div>
      </div>

      <!-- Continue Button -->
      <div class="card continue-card" id="continueSection" style="display:none">
        <div class="selection-summary" id="selectionSummary">
          <span>No selection yet</span>
        </div>
        <button type="submit" class="btn btn-continue" id="continueBtn">
          Continue <span class="arrow">→</span>
        </button>
      </div>
    </div>
  </div>
  <?= $this->Form->end() ?>
</div>

<script>
(function() {
  const OPEN = 600;   // 10:00 in minutes
  const CLOSE = 1200; // 20:00 in minutes
  const SLOT_INTERVAL = 60; // 60 min slots

  const today = new Date(); today.setHours(0,0,0,0);
  let view = new Date(today.getFullYear(), today.getMonth(), 1);
  let selectedDate = null;
  let selectedTime = null;
  let selectedHours = 2;
  let calendarData = {};

  const grid = document.getElementById('calGrid');
  const monthLbl = document.getElementById('calMonth');
  const bdate = document.getElementById('bdate');
  const stime = document.getElementById('stime');
  const hours = document.getElementById('hours');
  const timeSlotSection = document.getElementById('timeSlotSection');
  const durationSection = document.getElementById('durationSection');
  const continueSection = document.getElementById('continueSection');
  const selectedDateLabel = document.getElementById('selectedDateLabel');
  const selectionSummary = document.getElementById('selectionSummary');
  const timeSlotsEl = document.getElementById('timeSlots');
  const durationBtns = document.querySelectorAll('.duration-btn');

  const pad = n => String(n).padStart(2,'0');
  const key = d => d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
  const fmtDate = d => d.toLocaleDateString('en-MY',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  const fmtShort = d => d.toLocaleDateString('en-MY',{weekday:'short',day:'numeric',month:'short'});

  function toMin(t) {
    const parts = t.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1] || 0);
  }

  function minToTime(m) {
    return pad(Math.floor(m/60)) + ':' + pad(m%60);
  }

  // Build time slots
  function buildTimeSlots() {
    timeSlotsEl.innerHTML = '';
    for (let m = OPEN; m + 60 <= CLOSE; m += SLOT_INTERVAL) {
      const start = m;
      const end = m + 60;
      const startStr = minToTime(start);
      const endStr = minToTime(end);
      const isLate = end > CLOSE;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'time-slot';
      btn.dataset.time = startStr;
      btn.innerHTML = `<span class="time-range">${startStr} – ${endStr}</span><span class="time-duration">1 hour</span>`;

      if (isLate || (selectedDate && selectedDate.getTime() === today.getTime() && start <= (today.getHours()*60 + today.getMinutes()))) {
        btn.classList.add('disabled');
        btn.disabled = true;
      }

      // Check if taken (from calendar data)
      if (selectedDate) {
        const k = key(selectedDate);
        const bookings = calendarData[k] || [];
        const isBooked = bookings.some(b => {
          const bs = toMin(b.start);
          const be = toMin(b.end);
          return (start < be && end > bs);
        });
        if (isBooked) {
          btn.classList.add('booked');
          btn.disabled = true;
          btn.title = 'Already booked';
        }
      }

      btn.addEventListener('click', () => selectTime(startStr, btn));
      timeSlotsEl.appendChild(btn);
    }
  }

  function selectTime(timeStr, btn) {
    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedTime = timeStr;
    stime.value = timeStr;
    updateSummary();
    durationSection.style.display = 'block';
    updateDurations();
    continueSection.style.display = 'block';
  }

  function updateDurations() {
    if (!selectedTime || !selectedDate) return;
    const startMin = toMin(selectedTime);
    const maxHours = Math.floor((CLOSE - startMin) / 60);
    const minHours = 2;

    durationBtns.forEach(btn => {
      const h = parseInt(btn.dataset.hours);
      const isDisabled = h < minHours || h > maxHours;
      btn.disabled = isDisabled;
      btn.classList.toggle('disabled', isDisabled);
      btn.classList.toggle('selected', h === selectedHours && !isDisabled);

      // Update price display
      const rateEl = btn.querySelector('small');
      if (rateEl) {
        rateEl.textContent = h + (h === 1 ? ' hr' : ' hrs');
      }

      if (!isDisabled) {
        btn.onclick = () => {
          selectedHours = h;
          hours.value = h;
          durationBtns.forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
          updateSummary();
        };
      }
    });
  }

  function updateSummary() {
    if (selectedDate && selectedTime) {
      const parts = [
        fmtShort(selectedDate),
        selectedTime + ' onwards',
        selectedHours + ' hour' + (selectedHours > 1 ? 's' : '')
      ];
      selectionSummary.innerHTML = parts.map(p => '<span class="summary-chip">' + p + '</span>').join('');
    }
  }

  // Calendar
  async function loadMonth() {
    const ym = view.getFullYear()+'-'+pad(view.getMonth()+1);
    calendarData = {};
    try {
      const res = await fetch('<?= $this->Url->build(['controller' => 'Bookings', 'action' => 'calendarData']) ?>?ym=' + ym, {headers:{'Accept':'application/json'}});
      const data = await res.json();
      (data.rows || []).forEach(r => {
        (calendarData[r.date] = calendarData[r.date] || []).push(r);
      });
    } catch(e) {}
    renderGrid();
  }

  function renderGrid() {
    grid.innerHTML = '';
    monthLbl.textContent = view.toLocaleDateString('en-MY',{month:'long',year:'numeric'});

    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
      const hd = document.createElement('div');
      hd.className = 'cal-dow';
      hd.textContent = d;
      grid.appendChild(hd);
    });

    const firstDay = new Date(view.getFullYear(), view.getMonth(), 1).getDay();
    const days = new Date(view.getFullYear(), view.getMonth()+1, 0).getDate();

    for (let i=0; i<firstDay; i++) {
      const e = document.createElement('div');
      e.className = 'cal-day empty';
      grid.appendChild(e);
    }

    for (let d=1; d<=days; d++) {
      const dt = new Date(view.getFullYear(), view.getMonth(), d);
      const k = key(dt);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'cal-day';
      btn.innerHTML = '<span class="day-num">'+d+'</span>';

      // Today indicator
      if (dt.getTime() === today.getTime()) btn.classList.add('today');

      // Past dates
      if (dt < today) {
        btn.classList.add('past');
        btn.disabled = true;
      }

      // Has bookings
      if (calendarData[k] && calendarData[k].length > 0) {
        const dot = document.createElement('span');
        dot.className = 'day-dot';
        btn.appendChild(dot);
      }

      // Selected
      if (selectedDate && k === key(selectedDate)) btn.classList.add('sel');

      if (!btn.disabled) {
        btn.addEventListener('click', () => selectDate(k, dt));
      }
      grid.appendChild(btn);
    }
  }

  function selectDate(k, dt) {
    selectedDate = dt;
    bdate.value = k;
    selectedTime = null;
    stime.value = '';

    selectedDateLabel.textContent = 'Selected: ' + fmtDate(dt);
    timeSlotSection.style.display = 'block';
    durationSection.style.display = 'none';
    continueSection.style.display = 'none';

    buildTimeSlots();
    renderGrid();
  }

  // Price range sliders
  const priceMin = document.getElementById('priceMin');
  const priceMax = document.getElementById('priceMax');
  const priceMinLabel = document.getElementById('priceMinLabel');
  const priceMaxLabel = document.getElementById('priceMaxLabel');

  function updatePriceLabels() {
    let min = parseInt(priceMin.value);
    let max = parseInt(priceMax.value);
    if (min > max) {
      [min, max] = [max, min];
      priceMin.value = min;
      priceMax.value = max;
    }
    priceMinLabel.textContent = 'RM' + min;
    priceMaxLabel.textContent = 'RM' + max;
  }
  priceMin.addEventListener('input', updatePriceLabels);
  priceMax.addEventListener('input', updatePriceLabels);

  // Navigation
  document.getElementById('calPrev').addEventListener('click', () => {
    view.setMonth(view.getMonth()-1);
    loadMonth();
  });
  document.getElementById('calNext').addEventListener('click', () => {
    view.setMonth(view.getMonth()+1);
    loadMonth();
  });

  // Date presets
  document.querySelectorAll('.date-preset').forEach(btn => {
    btn.addEventListener('click', () => {
      const days = parseInt(btn.dataset.days);
      const d = new Date(today);
      d.setDate(d.getDate() + days);

      // Navigate view to that month
      view = new Date(d.getFullYear(), d.getMonth(), 1);
      loadMonth().then(() => {
        selectDate(key(d), d);
      });
    });
  });

  // Time presets
  document.querySelectorAll('.time-preset').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.time-preset').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      // Filter time slots
      const filter = btn.dataset.time;
      document.querySelectorAll('.time-slot').forEach(slot => {
        if (slot.disabled) return;
        const t = toMin(slot.dataset.time);
        let show = true;
        if (filter === 'morning') show = t >= 600 && t < 720;
        else if (filter === 'afternoon') show = t >= 720 && t < 1020;
        else if (filter === 'evening') show = t >= 1020;
        slot.style.display = show ? '' : 'none';
      });
    });
  });

  // Clear filters
  document.getElementById('clearFilters').addEventListener('click', () => {
    document.querySelectorAll('.filter-select').forEach(s => s.value = '');
    priceMin.value = 80; priceMax.value = 200;
    updatePriceLabels();
    document.querySelectorAll('.time-preset').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.time-slot').forEach(s => s.style.display = '');
  });

  // Form validation before submit
  document.getElementById('step1Form').addEventListener('submit', function(e) {
    if (!bdate.value || !stime.value) {
      e.preventDefault();
      alert('Please select a date and time slot first.');
    }
  });

  // Init
  loadMonth();
  updatePriceLabels();
})();
</script>
