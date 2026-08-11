<?= $this->include('themes/landing/partials/header') ?>

<main>
    <section class="block" style="border-top:none;">
        <div class="wrap" style="max-width:720px;">
            <div class="sec-head" style="text-align:left;margin-bottom:28px;">
                <h2><?= esc($pageTitle) ?></h2>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div style="margin-bottom:20px;padding:14px 18px;border:1px solid rgba(34,197,94,.35);background:rgba(34,197,94,.12);color:#86efac;border-radius:12px;">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div style="margin-bottom:20px;padding:14px 18px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.12);color:#fca5a5;border-radius:12px;">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/contact/<?= esc($form['slug']) ?>/submit" style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;">
                <?= csrf_field() ?>

                <?php foreach ($form['fields'] as $field):
                    $name  = $field['name'];
                    $value = old($name) ?? '';
                ?>
                    <div style="margin-bottom:18px;">
                        <label for="<?= esc($name) ?>" style="display:block;margin-bottom:8px;font-size:.9rem;font-weight:600;">
                            <?= esc($field['label']) ?>
                            <?php if ($field['required']): ?><span style="color:var(--brand);">*</span><?php endif; ?>
                        </label>

                        <?php if ($field['type'] === 'textarea'): ?>
                            <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?> rows="5" style="width:100%;padding:10px 14px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;color:var(--ink);"><?= esc($value) ?></textarea>

                        <?php elseif ($field['type'] === 'select'): ?>
                            <select id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?> style="width:100%;padding:10px 14px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;color:var(--ink);">
                                <option value="">-- Select --</option>
                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                    <option value="<?= esc($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($field['type'] === 'checkbox'): ?>
                            <?php foreach ($field['options'] ?? [] as $option): ?>
                                <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:.92rem;">
                                    <input type="checkbox" name="<?= esc($name) ?>[]" value="<?= esc($option) ?>" <?= (is_array($value) && in_array($option, $value)) ? 'checked' : '' ?>>
                                    <span><?= esc($option) ?></span>
                                </label>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <input type="<?= esc($field['type']) ?>" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($value) ?>" <?= $field['required'] ? 'required' : '' ?> style="width:100%;padding:10px 14px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;color:var(--ink);">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-brand">Send Message</button>
            </form>
        </div>
    </section>
</main>

<?= $this->include('themes/landing/partials/footer') ?>
