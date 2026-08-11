<?= $this->include('themes/landing/partials/header'); ?>
<section class="block">
    <div class="wrap">
        <div class="sec-head">
            <h1 style="font-size:2rem;"><?= esc($virtual_title ?? ($page_title ?? 'Page')) ?></h1>
        </div>
        <article class="article">
            <div class="content">
                <?= $virtual_body ?? '' ?>
            </div>
        </article>
    </div>
</section>
<?= $this->include('themes/landing/partials/footer'); ?>