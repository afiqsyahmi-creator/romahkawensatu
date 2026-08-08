<?php /** @var iterable $studios */ ?>
<div class="wrap">
  <div class="toolbar">
    <div><div class="eyebrow">Admin</div><h1 class="h">Studios</h1></div>
    <?= $this->Html->link('+ New studio', ['action' => 'add'], ['class' => 'btn']) ?>
  </div>
  <table>
    <tr><th>#</th><th>Name</th><th>Cap.</th><th>Rate</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($studios as $s): ?>
      <tr>
        <td><?= (int)$s->studio_id ?></td>
        <td><?= h($s->studio_name) ?><br><small style="color:var(--muted)"><?= h($s->description) ?></small></td>
        <td><?= h($s->capacity) ?></td>
        <td class="mono">RM<?= number_format((float)$s->hourly_rate, 0) ?></td>
        <td><span class="badge"><?= h($s->status) ?></span></td>
        <td class="actions">
          <?= $this->Html->link('Edit', ['action' => 'edit', $s->studio_id]) ?>
          <?= $this->Form->postLink('Delete', ['action' => 'delete', $s->studio_id], ['confirm' => 'Delete this studio?', 'class' => 'del']) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
