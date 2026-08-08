<?php /** @var iterable $bookings */ ?>
<div class="wrap">
  <div class="eyebrow">Admin</div>
  <h1 class="h">All bookings</h1>
  <table>
    <tr><th>#</th><th>Date</th><th>Time</th><th>Studio</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Set status</th></tr>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= (int)$b->booking_id ?></td>
        <td class="mono"><?= h($b->booking_date->format('d M Y')) ?></td>
        <td class="mono"><?= substr((string)$b->start_time,0,5) ?>–<?= substr((string)$b->end_time,0,5) ?></td>
        <td><?= h($b->studio->studio_name) ?></td>
        <td><?= h($b->customer->customer_name) ?><br><small class="mono" style="color:var(--muted)"><?= h($b->customer->phone_number) ?></small></td>
        <td><span class="badge <?= h($b->booking_status) ?>"><?= h(ucfirst($b->booking_status)) ?></span></td>
        <td><?= h($b->payments[0]->payment_status ?? '—') ?></td>
        <td class="mono">RM<?= number_format((float)$b->total_price,0) ?></td>
        <td>
          <?= $this->Form->create(null, ['url' => ['action' => 'changeStatus', $b->booking_id], 'style' => 'display:flex;gap:4px;margin:0']) ?>
            <?= $this->Form->select('status', ['pending'=>'pending','confirmed'=>'confirmed','completed'=>'completed','cancelled'=>'cancelled'], ['value' => $b->booking_status, 'style' => 'margin:0;padding:6px']) ?>
            <?= $this->Form->button('Save', ['class' => 'adminlink', 'style' => 'cursor:pointer']) ?>
          <?= $this->Form->end() ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
