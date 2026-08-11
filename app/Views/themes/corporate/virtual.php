<?php
$slug   = $virtual_slug ?? '';
$cfg    = $theme_config ?? [];
$title  = $virtual_title ?? ($page_title ?? 'Sayfa');
$body   = $virtual_body ?? '';
?>
<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <article class="article">
        <h1><?= esc($title) ?></h1>
        <div class="content">
            <?= $body ?>
        </div>
    </article>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>