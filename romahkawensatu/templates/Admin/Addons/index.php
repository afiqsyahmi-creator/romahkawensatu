<?php /** @var iterable $addons */ ?>
<div class="wrap">
  <div class="toolbar">
    <div><div class="eyebrow">Admin</div><h1 class="h">Add-ons</h1></div>
    <?= $this->Html->link('+ New add-on', ['action' => 'add'], ['class' => 'btn']) ?>
  </div>
  <table>
    <tr>
      <th>#</th>
      <th>Name</th>
      <th>Type</th>
      <th>Selection</th>
      <th>Price</th>
      <th>Popular</th>
      <th>Capacity</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($addons as $a): ?>
      <tr>
        <td><?= (int)$a->addon_id ?></td>
        <td><?= h($a->addon_name) ?><br><small style="color:var(--muted)"><?= h($a->description) ?></small></td>
        <td><?= h($a->addon_type) ?></td>
        <td style="font-size:12px"><?= h($a->selection_type ?? 'toggle') ?></td>
        <td class="mono">RM<?= number_format((float)$a->price, 0) ?></td>
        <td><?= $a->is_popular ? '⭐' : '—' ?></td>
        <td style="font-size:12px">
          <?php if ($a->weekly_capacity !== null): ?>
            <?= (int)$a->weekly_booked ?>/<?= (int)$a->weekly_capacity ?>
          <?php else: ?>
            ∞
          <?php endif; ?>
        </td>
        <td><span class="badge"><?= h($a->status) ?></span></td>
        <td class="actions">
          <?= $this->Html->link('Edit', ['action' => 'edit', $a->addon_id]) ?>
          <?= $this->Form->postLink('Delete', ['action' => 'delete', $a->addon_id], ['confirm' => 'Delete this add-on?', 'class' => 'del']) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
