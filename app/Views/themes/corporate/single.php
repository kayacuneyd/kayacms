<?= $this->include('themes/corporate/partials/header'); ?>
<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <article class="article">
        <?php if (! empty($item->featured_image)): ?>
            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;max-height:420px;object-fit:cover;margin-bottom:3rem;">
        <?php endif; ?>
        <h1><?= esc($item->title) ?></h1>
        <p class="c-grey txt-upper" style="font-size:1.4rem;margin-bottom:3rem;">
            <?= esc(date('j F Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?>
        </p>
        <div class="content">
            <?= \App\Libraries\ContentRenderer::render($item->body ?? null, $item) ?>
        </div>
    </article>

    <?php if (! empty($related)): ?>
        <section class="block" style="padding:4rem 0;">
            <h2>İlgili İçerikler</h2>
            <div class="blog-grid">
                <?php foreach ($related as $r): ?>
                    <div class="post-card">
                        <div class="body">
                            <h3><a href="<?= localized_url('/content/' . $r->slug) ?>"><?= esc($r->title) ?></a></h3>
                            <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($r->body ?? null, $r->excerpt ?? null)) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="block" style="padding:4rem 0;">
        <h2>Yorumlar</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (! empty($comments)): ?>
            <?= $this->include('themes/corporate/partials/comments_list', ['comments' => $comments]); ?>
        <?php else: ?>
            <p class="c-grey">Henüz yorum yok. İlk yorumu siz yapın!</p>
        <?php endif; ?>

        <h3 style="margin-top:3rem;">Yorum Bırakın</h3>
        <form action="/comments/store" method="post" class="form-card">
            <?= csrf_field() ?>
            <input type="hidden" name="content_id" value="<?= $item->id ?>">

            <div>
                <label>Ad</label>
                <input type="text" name="author_name" value="<?= old('author_name') ?>" required>
            </div>
            <div>
                <label>E-posta</label>
                <input type="email" name="author_email" value="<?= old('author_email') ?>" required>
            </div>
            <div>
                <label>Web Sitesi (opsiyonel)</label>
                <input type="url" name="author_url" value="<?= old('author_url') ?>">
            </div>
            <div>
                <label>Yorum</label>
                <textarea name="body" rows="5" required><?= old('body') ?></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="border:none;">Yorum Gönder</button>
        </form>
    </section>
</div>
<?= $this->include('themes/corporate/partials/footer'); ?>
