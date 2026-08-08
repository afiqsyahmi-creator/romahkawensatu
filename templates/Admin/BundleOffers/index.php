<?php /** @var iterable $bundles */ ?>
<div class="wrap">
  <div class="toolbar">
    <div><div class="eyebrow">Admin</div><h1 class="h">Bundle Offers</h1></div>
    <?= $this->Html->link('+ New bundle', ['action' => 'add'], ['class' => 'btn']) ?>
  </div>
  <?php if (count($bundles) > 0): ?>
  <table>
    <tr>
      <th>#</th>
      <th>Add-on 1</th>
      <th>Add-on 2</th>
      <th>Discount (RM)</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($bundles as $b): ?>
      <tr>
        <td><?= (int)$b->bundle_id ?></td>
        <td><?= h($b->addon_1->addon_name ?? '—') ?></td>
        <td><?= h($b->addon_2->addon_name ?? '—') ?></td>
        <td class="mono">-RM<?= number_format((float)$b->discount_amount, 0) ?></td>
        <td>
          <span class="badge" style="background:<?= $b->is_active ? 'var(--green)' : 'var(--muted)' ?>">
            <?= $b->is_active ? 'Active' : 'Inactive' ?>
          </span>
        </td>
        <td class="actions">
          <?= $this->Html->link('Edit', ['action' => 'edit', $b->bundle_id]) ?>
          <?= $this->Form->postLink('Delete', ['action' => 'delete', $b->bundle_id], ['confirm' => 'Delete this bundle offer?', 'class' => 'del']) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
    <p style="color:var(--muted);margin-top:24px">No bundle offers yet. <?= $this->Html->link('Create one', ['action' => 'add']) ?>.</p>
  <?php endif; ?>
</div>
