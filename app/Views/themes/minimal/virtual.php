<?= $this->include('themes/minimal/partials/header'); ?>
<div class="container">
    <article class="post">
        <h1><?= esc($virtual_title ?? '') ?></h1>
        <div class="content">
            <?= $virtual_body ?? '' ?>
        </div>
    </article>
</div>
<?= $this->include('themes/minimal/partials/footer'); ?>