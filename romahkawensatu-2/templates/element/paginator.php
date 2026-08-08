<?php
/**
 * Simple paginator element for admin pages.
 * Usage: <?= $this->element('paginator') ?>
 *        <?= $this->element('paginator', ['scope' => 'upcoming']) ?>
 *
 * Relies on CakePHP's built-in PaginatorHelper.
 */
$model = isset($scope) ? $scope : null;
if (!isset($this->Paginator) || !$this->Paginator->hasPage($model, 2)) {
    return; // only one page — nothing to show
}
?>
<div class="paging">
    <span><?= $this->Paginator->prev('← Prev', ['model' => $model]) ?></span>
    <span><?= $this->Paginator->numbers(['model' => $model]) ?></span>
    <span><?= $this->Paginator->next('Next →', ['model' => $model]) ?></span>
    <span style="margin-left:auto;color:var(--muted-text)">
        <?= $this->Paginator->counter($model, ['format' => 'Page {{page}} of {{pages}}']) ?>
    </span>
</div>
