<?php
/**
 * @var array           $stats           [totalRevenue, revenueTrend, totalBookings, bookingsTrend, byStatus, avgBookingValue, occupancyRate, todayCount, todayCheckins]
 * @var array           $revenueChart    [['label','full','value'], …]
 * @var string          $period          daily|weekly|monthly|yearly
 * @var float           $periodRevenue   sum of chart values
 * @var iterable        $todayBookings   today's bookings (paginated)
 * @var iterable        $upcomingBookings upcoming (paginated)
 * @var array           $studioBreakdown [['name','bookings','revenue'], …]
 * @var string          $filterStatus
 * @var string          $filterDate
 * @var \Cake\I18n\FrozenDate $today
 */

function _chartSvg(array $data, string $periodLabel): string
{
    $count = count($data);
    if ($count < 2) return '';

    $values = array_column($data, 'value');
    $maxVal = max($values) ?: 1;
    $w = 720; $h = 200;
    $padL = 0; $padR = 0; $padT = 20; $padB = 30;
    $chartW = $w - $padL - $padR;
    $chartH = $h - $padT - $padB;

    $points = [];
    $areaPoints = [];
    $stepX = $chartW / max($count - 1, 1);
    foreach ($data as $i => $d) {
        $x = $padL + $i * $stepX;
        $y = $padT + $chartH - (($d['value'] / $maxVal) * $chartH);
        $points[] = "$x,$y";
        $areaPoints[] = "$x,$y";
    }

    $pointsStr = implode(' ', $points);
    $areaStart = "$padL," . ($padT + $chartH);
    $areaEnd = ($padL + ($count - 1) * $stepX) . ',' . ($padT + $chartH);
    $areaStr = $areaStart . ' ' . implode(' ', $areaPoints) . ' ' . $areaEnd;

    $labels = '';
    $labelInterval = $periodLabel === 'daily' ? 2 : 1;
    foreach ($data as $i => $d) {
        if ($i % $labelInterval !== 0 && $i !== $count - 1) continue;
        $x = $padL + $i * $stepX;
        $labels .= "<text x=\"$x\" y=\"" . ($h - 6) . "\" text-anchor=\"middle\" font-family=\"Space Mono,monospace\" font-size=\"9\" fill=\"#8b8579\">" . h($d['label']) . "</text>";
    }

    $dots = '';
    foreach ($data as $i => $d) {
        if ((float)$d['value'] <= 0) continue;
        $x = $padL + $i * $stepX;
        $y = $padT + $chartH - (($d['value'] / $maxVal) * $chartH);
        $dots .= "<circle cx=\"$x\" cy=\"$y\" r=\"3.5\" fill=\"#c9a96e\" stroke=\"#fcfbf8\" stroke-width=\"2\"><title>" . h($d['full']) . ": RM" . number_format((float)$d['value'], 0) . "</title></circle>";
    }

    $html = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg">';
    $html .= '<defs><linearGradient id="gld" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#c9a96e" stop-opacity=".3"/><stop offset="100%" stop-color="#c9a96e" stop-opacity=".01"/></linearGradient></defs>';
    $html .= '<polygon points="' . $areaStr . '" fill="url(#gld)"/>';
    $html .= '<polyline points="' . $pointsStr . '" fill="none" stroke="#c9a96e" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>';
    $html .= $dots;
    $html .= $labels;
    $html .= '</svg>';
    return $html;
}

function _sparklineSvg(array $data): string
{
    $vals = array_column($data, 'value');
    $mx = max($vals) ?: 1;
    $c = count($vals);
    if ($c < 2) return '';
    $w = 44; $h = 18;
    $pts = [];
    $step = $w / max($c - 1, 1);
    foreach ($vals as $i => $v) {
        $x = $i * $step;
        $y = $h - (($v / $mx) * $h);
        $pts[] = "$x,$y";
    }
    return '<span class="sparkline"><svg viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg"><polyline points="' . implode(' ', $pts) . '" fill="none" stroke="#c9a96e" stroke-width="1.5" stroke-linejoin="round"/></svg></span>';
}

function _trendBadge(float $pct): string
{
    if ($pct > 0) return '<span class="trend up">↑' . number_format($pct, 1) . '%</span>';
    if ($pct < 0) return '<span class="trend down">↓' . number_format(abs($pct), 1) . '%</span>';
    return '<span class="trend flat">–</span>';
}

$cardClass = function(string $key, string $type = 'display'): string {
    return 'stat-card' . ($type === 'alert' ? ' is-alert' : '') . ($type === 'nav' ? ' is-navigable' : ' is-display');
};
?><div class="wrap">

  <!-- ═══ Header ═══════════════════════════════════════════ -->
  <div class="eyebrow">Admin / Dashboard</div>
  <h1 class="h">Overview</h1>

  <!-- ═══ Command / Search Bar ═════════════════════════════ -->
  <div class="command-bar">
    <span class="icon">⌕</span>
    <input type="search" placeholder="Search bookings, customers&hellip;" aria-label="Search">
  </div>

  <!-- ═══ Quick-Action Pills ═══════════════════════════════ -->
  <div class="action-pills" style="margin-bottom:28px">
    <?= $this->Html->link('All bookings',   ['controller' => 'Bookings', 'action' => 'index'],   ['class' => 'action-pill']) ?>
    <?= $this->Html->link('Studios',        ['controller' => 'Studios', 'action' => 'index'],   ['class' => 'action-pill']) ?>
    <?= $this->Html->link('Galleries',      ['controller' => 'Galleries', 'action' => 'index'], ['class' => 'action-pill']) ?>
    <?= $this->Html->link('Add-ons',        ['controller' => 'Addons', 'action' => 'index'],    ['class' => 'action-pill']) ?>
    <?= $this->Html->link('Admins',         ['controller' => 'Admins', 'action' => 'index'],    ['class' => 'action-pill']) ?>
  </div>

  <!-- ═══ Stat Cards ═══════════════════════════════════════ -->
  <div class="stat-grid">

    <!-- 1. Revenue (paid) — navigable -->
    <div class="stat-card is-navigable">
      <div class="k">Revenue (paid)</div>
      <div class="v">RM<?= number_format((float)$stats['totalRevenue'], 0) ?></div>
      <div class="sub">
        <?= _trendBadge((float)($stats['revenueTrend'] ?? 0)) ?>
        vs last week
        <?= _sparklineSvg($revenueChart) ?>
      </div>
    </div>

    <!-- 2. Total bookings — navigable -->
    <div class="stat-card is-navigable">
      <div class="k">Total bookings</div>
      <div class="v"><?= (int)$stats['totalBookings'] ?></div>
      <div class="sub">
        <?= _trendBadge((float)($stats['bookingsTrend'] ?? 0)) ?>
        vs last week
      </div>
    </div>

    <!-- 3. Confirmed — display only (no drill-down) -->
    <div class="stat-card is-display">
      <div class="k">Confirmed</div>
      <div class="v"><?= (int)($stats['byStatus']['confirmed'] ?? 0) ?></div>
      <div class="sub">All time</div>
    </div>

    <!-- 4. Pending (needs review) — alert / navigable -->
    <div class="stat-card is-alert is-navigable">
      <div class="k">Pending &middot; needs review</div>
      <div class="v"><?= (int)($stats['byStatus']['pending'] ?? 0) ?></div>
      <div class="sub">
        <a href="<?= $this->Url->build(['controller' => 'Bookings', 'action' => 'index', '?' => ['status' => 'pending']]) ?>">Review <span class="arrow">→</span></a>
      </div>
    </div>

    <!-- 5. Avg. booking value — display only -->
    <?php $avgVal = (float)($stats['avgBookingValue'] ?? 0); ?>
    <div class="stat-card <?= $avgVal > 0 ? 'is-display' : 'is-display is-zero' ?>">
      <div class="k">Avg. booking</div>
      <div class="v"><?= $avgVal > 0 ? 'RM' . number_format($avgVal, 0) : '—' ?></div>
      <div class="sub">Confirmed only</div>
    </div>

    <!-- 6. Occupancy — display only -->
    <?php $occ = (float)($stats['occupancyRate'] ?? 0); ?>
    <div class="stat-card <?= $occ > 0 ? 'is-display' : 'is-display is-zero' ?>">
      <div class="k">Occupancy</div>
      <div class="v"><?= $occ > 0 ? number_format($occ, 1) . '%' : '—' ?></div>
      <div class="sub">Today</div>
    </div>

    <!-- 7. Today's check-ins — actionable metric (navigable) -->
    <div class="stat-card is-navigable">
      <div class="k">Today's check-ins</div>
      <div class="v"><?= (int)($stats['todayCheckins'] ?? 0) ?></div>
      <div class="sub">
        <a href="<?= $this->Url->build(['controller' => 'Bookings', 'action' => 'index', '?' => ['status' => 'confirmed', 'date' => $today->format('Y-m-d')]]) ?>">View <span class="arrow">→</span></a>
      </div>
    </div>

    <!-- 8. Gallery images — navigable -->
    <div class="stat-card is-navigable">
      <div class="k">Gallery images</div>
      <div class="v"><?= (int)($stats['galleryCount'] ?? 0) ?></div>
      <div class="sub">
        <a href="<?= $this->Url->build(['controller' => 'Galleries', 'action' => 'index']) ?>">Manage <span class="arrow">→</span></a>
      </div>
    </div>

  </div>

  <!-- ═══ Revenue Chart ════════════════════════════════════ -->
  <div class="card">
    <div class="card-header">
      <span>
        <span class="eyebrow">Revenue trend</span>
        <span class="mono" style="margin-left:12px;font-size:15px;color:var(--gold-dark)">RM<?= number_format($periodRevenue, 0) ?></span>
      </span>
      <div class="period-tabs">
        <?php $periods = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly']; ?>
        <?php foreach ($periods as $key => $label): ?>
          <?php if ($period === $key): ?>
            <span class="period-tab active"><?= $label ?></span>
          <?php else: ?>
            <?= $this->Html->link($label, ['?' => ['period' => $key] + $this->request->getQueryParams()], ['class' => 'period-tab']) ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card-body chart-container">
      <?php if (count($revenueChart) >= 2): ?>
        <?= _chartSvg($revenueChart, $period) ?>
      <?php else: ?>
        <p style="text-align:center;color:var(--muted-text);padding:30px 0">Not enough data for this period.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══ Studio Breakdown ═════════════════════════════════ -->
  <?php if (!empty($studioBreakdown)): ?>
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <span class="eyebrow">Studio breakdown</span>
    </div>
    <div class="studio-mini-grid">
      <?php foreach ($studioBreakdown as $s): ?>
      <div class="studio-mini">
        <div class="name"><?= h($s['name']) ?></div>
        <div class="stat-row">
          <span><?= (int)$s['bookings'] ?> booking<?= (int)$s['bookings'] !== 1 ? 's' : '' ?></span>
          <span class="num">RM<?= number_format((float)$s['revenue'], 0) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ═══ Today's Bookings ═════════════════════════════════ -->
  <div class="card" style="margin-bottom:0">
    <div class="card-header">
      <span>
        <span class="eyebrow">Today's bookings</span>
        <span class="mono" style="margin-left:8px;font-size:12px;color:var(--muted-text)"><?= (int)$stats['todayCount'] ?> total</span>
      </span>
      <?= $this->Html->link('View all →', ['controller' => 'Bookings', 'action' => 'index'], ['class' => 'btn btn-ghost btn-sm']) ?>
    </div>

    <!-- Pill filter chips (replaces dropdown) -->
    <form method="get" class="filter-chips" style="margin-bottom:16px">
      <span class="label">Status</span>
      <?php
      $chips = [
        '' => 'All',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
      ];
      foreach ($chips as $val => $label):
        $active = $filterStatus === $val;
        $url = $active ? ['action' => 'index'] : ['?' => ['status' => $val, 'date' => $filterDate ?: $today->format('Y-m-d')]];
      ?>
        <?php if ($active): ?>
          <span class="chip active <?= h($val) ?>"><?= h($label) ?></span>
        <?php else: ?>
          <?= $this->Html->link($label, $url, ['class' => 'chip ' . h($val)]) ?>
        <?php endif; ?>
      <?php endforeach; ?>
      <input type="hidden" name="date" value="<?= h($filterDate ?: $today->format('Y-m-d')) ?>">
      <?php if ($filterStatus !== '' || $filterDate !== ''): ?>
        <?= $this->Html->link('Clear', ['action' => 'index'], ['class' => 'btn btn-ghost btn-sm', 'style' => 'margin-left:4px']) ?>
      <?php endif; ?>
    </form>

    <div class="table-container">
      <table>
        <tr><th>Time</th><th>Studio</th><th>Customer</th><th>Status</th><th>Total</th><th></th></tr>
        <?php $h = false; foreach ($todayBookings as $b): $h = true; ?>
          <tr>
            <td class="mono"><?= substr((string)$b->start_time,0,5) ?>–<?= substr((string)$b->end_time,0,5) ?></td>
            <td><?= h($b->studio->studio_name) ?></td>
            <td><?= h($b->customer->customer_name) ?></td>
            <td><span class="badge <?= h($b->booking_status) ?>"><?= h(ucfirst($b->booking_status)) ?></span></td>
            <td class="mono">RM<?= number_format((float)$b->total_price,0) ?></td>
            <td><?= $this->Html->link('View', ['controller'=>'Bookings','action'=>'view',$b->booking_id], ['class'=>'btn btn-ghost btn-sm']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$h): ?><tr><td colspan="6" style="color:var(--muted-text);text-align:center;padding:24px">No bookings for this day.</td></tr><?php endif; ?>
      </table>
    </div>
    <div class="paging"><?= $this->element('paginator') ?></div>
  </div>

  <!-- ═══ Upcoming Bookings ════════════════════════════════ -->
  <div class="card">
    <div class="card-header">
      <span>
        <span class="eyebrow">Upcoming bookings</span>
        <span class="mono" style="margin-left:8px;font-size:12px;color:var(--muted-text)">Next 7 days</span>
      </span>
    </div>
    <div class="table-container">
      <table>
        <tr><th>Date</th><th>Time</th><th>Studio</th><th>Customer</th><th>Status</th><th>Total</th><th></th></tr>
        <?php $hu = false; foreach ($upcomingBookings as $b): $hu = true; ?>
          <tr>
            <td class="mono"><?= h((new \Cake\I18n\FrozenDate($b->booking_date))->format('j M')) ?></td>
            <td class="mono"><?= substr((string)$b->start_time,0,5) ?>–<?= substr((string)$b->end_time,0,5) ?></td>
            <td><?= h($b->studio->studio_name) ?></td>
            <td><?= h($b->customer->customer_name) ?></td>
            <td><span class="badge <?= h($b->booking_status) ?>"><?= h(ucfirst($b->booking_status)) ?></span></td>
            <td class="mono">RM<?= number_format((float)$b->total_price,0) ?></td>
            <td><?= $this->Html->link('View', ['controller'=>'Bookings','action'=>'view',$b->booking_id], ['class'=>'btn btn-ghost btn-sm']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$hu): ?><tr><td colspan="7" style="color:var(--muted-text);text-align:center;padding:24px">No upcoming bookings.</td></tr><?php endif; ?>
      </table>
    </div>
    <div class="paging"><?= $this->element('paginator', ['scope' => 'upcoming']) ?></div>
  </div>

  <!-- ═══ Summary Bar ═══════════════════════════════════════ -->
  <div class="summary-bar">
    <span class="label">By status</span>
    <?php foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $l): ?>
      <span class="badge <?= $k ?>"><?= $l ?> &middot; <?= (int)($stats['byStatus'][$k] ?? 0) ?></span>
    <?php endforeach; ?>
    <span style="margin-left:auto"><?= $this->Html->link('Manage bookings →', ['controller'=>'Bookings','action'=>'index'], ['class'=>'btn btn-ghost btn-sm']) ?></span>
  </div>

</div>
