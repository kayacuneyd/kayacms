<?= $this->include('themes/minimal/partials/header'); ?>
<script>document.body.dataset.contentSlug = <?= json_encode($item->slug ?? '') ?>;</script>
<script src="<?= base_url('assets/js/analytics.js') ?>" defer></script>
<div class="container">
    <article class="post">
        <h1><?= esc($item->title) ?></h1>
        <p class="meta"><?= esc(date('F j, Y', strtotime($item->published_at ?? $item->created_at ?? 'now'))) ?></p>

        <?php if (! empty($item->featured_image)): ?>
            <img class="featured" src="<?= esc($item->featured_image) ?>" alt="<?= esc($item->title) ?>">
        <?php endif; ?>

        <div class="content">
            <?= \App\Libraries\ContentRenderer::render($item->body ?? null, $item) ?>
        </div>
    </article>

    <?php if (! empty($related)): ?>
        <section class="related" style="padding-bottom:40px;">
            <h2>Related</h2>
            <ul class="post-list" style="padding-bottom:0;">
                <?php foreach ($related as $r): ?>
                    <li>
                        <h2 style="font-size:1.1rem;"><a href="<?= localized_url('/content/' . $r->slug) ?>"><?= esc($r->title) ?></a></h2>
                        <p class="excerpt"><?= esc(\App\Libraries\ContentRenderer::excerpt($r->body ?? null, $r->excerpt ?? null)) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="comments">
        <h2>Comments</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <p style="background:#eef7ee;color:#2d6a2d;padding:10px 14px;"><?= session()->getFlashdata('success') ?></p>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <p style="background:#fdeeee;color:#8a2d2d;padding:10px 14px;"><?= session()->getFlashdata('error') ?></p>
        <?php endif; ?>

        <?php if (! empty($comments)): ?>
            <?= $this->include('themes/minimal/partials/comments_list', ['comments' => $comments]); ?>
        <?php else: ?>
            <p class="meta">No comments yet. Be the first to comment!</p>
        <?php endif; ?>

        <h3 style="margin-top:32px;">Leave a comment</h3>
        <form class="comment-form" action="/comments/store" method="post" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="content_id" value="<?= $item->id ?>">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Name</label>
            <input type="text" name="author_name" value="<?= old('author_name') ?>" required>
            <label>Email</label>
            <input type="email" name="author_email" value="<?= old('author_email') ?>" required>
            <label>Website (optional)</label>
            <input type="url" name="author_url" value="<?= old('author_url') ?>">
            <label>Comment</label>
            <textarea name="body" rows="5" required><?= old('body') ?></textarea>
            <button type="submit">Submit Comment</button>
        </form>
    </section>
</div>
<?= $this->include('themes/minimal/partials/footer'); ?>
