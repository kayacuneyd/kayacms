<?= $this->include('themes/landing/partials/header'); ?>
<div class="wrap">
    <article class="article">
        <?php if (! empty($item->featured_image)): ?>
            <img src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>" style="width:100%;max-height:360px;object-fit:cover;border-radius:12px;margin-bottom:20px;">
        <?php endif; ?>
        <h1><?= esc($item->title) ?></h1>
        <p class="meta"><?= esc(date('F j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>
        <div class="content">
            <?= \App\Libraries\ContentRenderer::render($item->body ?? null, $item) ?>
        </div>
    </article>

    <?php if (! empty($related)): ?>
        <section class="block" id="related">
            <div class="wrap">
                <div class="sec-head">
                    <h2>Related Content</h2>
                </div>
                <div class="blog-grid">
                    <?php foreach ($related as $r): ?>
                        <article class="post-card">
                            <div class="body">
                                <h3><a href="<?= localized_url('/content/' . $r->slug) ?>"><?= esc($r->title) ?></a></h3>
                                <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($r->body ?? null, $r->excerpt ?? null)) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="block" id="comments">
        <div class="wrap">
            <h2>Comments</h2>

            <?php if (session()->getFlashdata('success')): ?>
                <div style="background:#052e16;color:#4ade80;padding:12px 16px;border-radius:8px;margin-bottom:16px;border:1px solid #14532d;">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div style="background:#450a0a;color:#f87171;padding:12px 16px;border-radius:8px;margin-bottom:16px;border:1px solid #7f1d1d;">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($comments)): ?>
                <?= $this->include('themes/landing/partials/comments_list', ['comments' => $comments]); ?>
            <?php else: ?>
                <p class="empty">No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <h3 style="margin-top:32px;">Leave a comment</h3>
            <form action="/comments/store" method="post" style="margin-top:16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:22px;">
                <?= csrf_field() ?>
                <input type="hidden" name="content_id" value="<?= $item->id ?>">

                <div style="margin-bottom:12px;">
                    <label>Name</label><br>
                    <input type="text" name="author_name" value="<?= old('author_name') ?>" required style="width:100%;padding:10px;background:var(--bg);color:var(--ink);border:1px solid var(--border);border-radius:8px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Email</label><br>
                    <input type="email" name="author_email" value="<?= old('author_email') ?>" required style="width:100%;padding:10px;background:var(--bg);color:var(--ink);border:1px solid var(--border);border-radius:8px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Website (optional)</label><br>
                    <input type="url" name="author_url" value="<?= old('author_url') ?>" style="width:100%;padding:10px;background:var(--bg);color:var(--ink);border:1px solid var(--border);border-radius:8px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Comment</label><br>
                    <textarea name="body" rows="5" required style="width:100%;padding:10px;background:var(--bg);color:var(--ink);border:1px solid var(--border);border-radius:8px;"><?= old('body') ?></textarea>
                </div>
                <button type="submit" class="btn btn-brand">Submit Comment</button>
            </form>
        </div>
    </section>
</div>
<?= $this->include('themes/landing/partials/footer'); ?>