<?= $this->include('themes/default/partials/header'); ?>
<div class="wrap">
    <article class="single">
        <h1><?= esc($virtual_title ?? '') ?></h1>
        <div class="content">
            <?= $virtual_body ?? '' ?>
        </div>
    </article>
</div>
<?= $this->include('themes/default/partials/footer'); ?>