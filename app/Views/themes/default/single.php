<?= $this->include('themes/default/partials/header'); ?>
<script>document.body.dataset.contentSlug = <?= json_encode($item->slug ?? '') ?>;</script>
<script src="<?= base_url('assets/js/analytics.js') ?>" defer></script>
<div class="wrap">
    <article class="single">
        <?php if (! empty($item->featured_image)): ?>
            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;max-height:360px;object-fit:cover;border-radius:8px;margin-bottom:20px;">
        <?php endif; ?>
        <h1><?= esc($item->title) ?></h1>
        <p class="meta"><?= esc(date('F j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>
        <div class="content">
            <?= \App\Libraries\ContentRenderer::render($item->body ?? null, $item) ?>
        </div>
    </article>

    <?php if (! empty($related)): ?>
        <section class="related" style="margin-top:40px;">
            <h2>Related Content</h2>
            <div class="grid">
                <?php foreach ($related as $r): ?>
                    <div class="card">
                        <div class="body">
                            <h3><a href="<?= localized_url('/content/' . $r->slug) ?>"><?= esc($r->title) ?></a></h3>
                            <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($r->body ?? null, $r->excerpt ?? null)) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="comments" style="margin-top:40px;">
        <h2>Comments</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div style="background:#d1fae5;color:#065f46;padding:12px 16px;border-radius:6px;margin-bottom:16px;">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:6px;margin-bottom:16px;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($comments)): ?>
            <?= $this->include('themes/default/partials/comments_list', ['comments' => $comments]); ?>
        <?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>

        <h3 style="margin-top:32px;">Leave a comment</h3>
        <form action="/comments/store" method="post" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="content_id" value="<?= $item->id ?>">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

            <div style="margin-bottom:12px;">
                <label>Name</label><br>
                <input type="text" name="author_name" value="<?= old('author_name') ?>" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
            </div>
            <div style="margin-bottom:12px;">
                <label>Email</label><br>
                <input type="email" name="author_email" value="<?= old('author_email') ?>" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
            </div>
            <div style="margin-bottom:12px;">
                <label>Website (optional)</label><br>
                <input type="url" name="author_url" value="<?= old('author_url') ?>" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
            </div>
            <div style="margin-bottom:12px;">
                <label>Comment</label><br>
                <textarea name="body" rows="5" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"><?= old('body') ?></textarea>
            </div>
            <button type="submit" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;">Submit Comment</button>
        </form>
    </section>
</div>
<?= $this->include('themes/default/partials/footer'); ?>
