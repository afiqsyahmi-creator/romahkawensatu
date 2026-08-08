<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Gallery> $galleries
 */

$hasGalleries = false;
?>

<div class="wrap">

    <div class="toolbar">
        <div>
            <div class="eyebrow">Admin</div>
            <h1 class="h">Gallery</h1>
        </div>

        <div style="display:flex;gap:8px">
            <?= $this->Html->link(
                '+ Add image',
                ['action' => 'add'],
                ['class' => 'btn']
            ) ?>
            <?= $this->Html->link(
                '← Dashboard',
                [
                    'controller' => 'Dashboard',
                    'action' => 'index',
                ],
                [
                    'class' => 'btn btn-secondary',
                ]
            ) ?>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Preview</th>
                    <th>Studio</th>
                    <th>Caption</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($galleries as $gallery): ?>
                    <?php
                    $hasGalleries = true;

                    $imagePath = trim(
                        (string)($gallery->image_path ?? '')
                    );

                    $imageUrl = '';

                    if ($imagePath !== '') {
                        if (preg_match('#^https?://#i', $imagePath)) {
                            $imageUrl = $imagePath;
                        } else {
                            $normalizedPath = str_replace(
                                '\\',
                                '/',
                                $imagePath
                            );

                            $imageUrl = $this->Url->build(
                                '/' . ltrim($normalizedPath, '/')
                            );
                        }
                    }

                    $studioName = $gallery->studio->studio_name
                        ?? ('Studio #' . (int)$gallery->studio_id);

                    $caption = trim(
                        (string)($gallery->caption ?? '')
                    );
                    ?>

                    <tr>
                        <td>
                            <?= (int)$gallery->gallery_id ?>
                        </td>

                        <td>
                            <div
                                style="
                                    width:60px;
                                    height:60px;
                                    overflow:hidden;
                                    position:relative;
                                    border:1px solid var(--line);
                                    border-radius:3px;
                                    background:var(--cream);
                                "
                            >
                                <?php if ($imageUrl !== ''): ?>
                                    <img
                                        src="<?= h($imageUrl) ?>"
                                        alt="<?= h(
                                            $caption !== ''
                                                ? $caption
                                                : $studioName
                                        ) ?>"
                                        width="60"
                                        height="60"
                                        loading="lazy"
                                        style="
                                            width:100%;
                                            height:100%;
                                            display:block;
                                            object-fit:cover;
                                            object-position:center;
                                        "
                                        onerror="
                                            this.style.display='none';
                                            this.nextElementSibling.style.display='flex';
                                        "
                                    >

                                    <span
                                        style="
                                            display:none;
                                            width:100%;
                                            height:100%;
                                            align-items:center;
                                            justify-content:center;
                                            padding:5px;
                                            text-align:center;
                                            font-size:9px;
                                            color:var(--muted-text);
                                        "
                                    >
                                        Image unavailable
                                    </span>
                                <?php else: ?>
                                    <span
                                        style="
                                            display:flex;
                                            width:100%;
                                            height:100%;
                                            align-items:center;
                                            justify-content:center;
                                            padding:5px;
                                            text-align:center;
                                            font-size:9px;
                                            color:var(--muted-text);
                                        "
                                    >
                                        No image
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <?= h($studioName) ?>
                        </td>

                        <td>
                            <?php if ($caption !== ''): ?>
                                <div>
                                    <?= h($caption) ?>
                                </div>
                            <?php else: ?>
                                <div style="color:var(--muted-text)">
                                    No caption
                                </div>
                            <?php endif; ?>

                            <?php if ($imagePath !== ''): ?>
                                <small
                                    class="mono"
                                    style="
                                        display:block;
                                        margin-top:4px;
                                        color:var(--muted-text);
                                        overflow-wrap:anywhere;
                                    "
                                >
                                    <?= h($imagePath) ?>
                                </small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= (int)$gallery->sort_order ?>
                        </td>

                        <td class="actions">
                            <?= $this->Html->link(
                                'Edit',
                                [
                                    'action' => 'edit',
                                    $gallery->gallery_id,
                                ]
                            ) ?>

                            <?= $this->Form->postLink(
                                'Delete',
                                [
                                    'action' => 'delete',
                                    $gallery->gallery_id,
                                ],
                                [
                                    'confirm' => sprintf(
                                        'Delete the image for %s?',
                                        $studioName
                                    ),
                                    'class' => 'del',
                                ]
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$hasGalleries): ?>
                    <tr>
                        <td
                            colspan="6"
                            style="
                                padding:40px;
                                text-align:center;
                                color:var(--muted-text);
                            "
                        >
                            No gallery images have been added.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->Paginator->hasPage()): ?>
        <div
            style="
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:15px;
                margin-top:24px;
            "
        >
            <div class="paging">
                <?= $this->Paginator->prev('← Previous') ?>
                <?= $this->Paginator->next('Next →') ?>
            </div>

            <small style="color:var(--muted-text)">
                <?= $this->Paginator->counter(
                    'Page {{page}} of {{pages}}, showing {{current}} of {{count}} images'
                ) ?>
            </small>
        </div>
    <?php endif; ?>

</div>