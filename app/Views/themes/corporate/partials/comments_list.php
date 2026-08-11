<?php
function renderComments(array $comments, int $level = 0): void
{
    foreach ($comments as $comment): ?>
        <div class="comment" style="margin-left:<?= $level * 24 ?>px; margin-bottom: 16px; padding: 16px; background: #f8fafc; border-radius: 8px;">
            <div style="font-weight:600; margin-bottom:4px;">
                <?= esc($comment['author_name']) ?>
                <span style="font-weight:normal; color:#64748b; font-size:0.9em;">
                    <?= esc(date('F j, Y', strtotime($comment['created_at'] ?? 'now'))) ?>
                </span>
            </div>
            <div style="margin-bottom:8px;"><?= nl2br(esc($comment['body'])) ?></div>
            <button type="button" onclick="document.getElementById('reply-<?= $comment['id'] ?>').style.display='block'" style="font-size:0.9em; color:#2563eb; background:none; border:none; cursor:pointer;">Reply</button>

            <div id="reply-<?= $comment['id'] ?>" style="display:none; margin-top:12px;">
                <form action="/comments/store" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="content_id" value="<?= $comment['content_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <div style="margin-bottom:8px;">
                        <input type="text" name="author_name" placeholder="Name" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <input type="email" name="author_email" placeholder="Email" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <textarea name="body" rows="3" placeholder="Your reply..." required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"></textarea>
                    </div>
                    <button type="submit" style="padding:6px 14px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;">Post Reply</button>
                </form>
            </div>

            <?php if (! empty($comment['children'])): ?>
                <div style="margin-top:12px;">
                    <?= renderComments($comment['children'], $level + 1) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach;
}

renderComments($comments ?? []);
