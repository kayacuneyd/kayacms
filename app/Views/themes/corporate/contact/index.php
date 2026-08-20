<?= $this->include('themes/corporate/partials/header') ?>

<div class="wrap" style="padding-top:12rem;padding-bottom:6rem;">
    <div class="section-head center" style="margin:0 auto 3rem;">
        <h1><?= esc($pageTitle) ?></h1>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php
        $contactStartedAt = time();
        $contactToken = hash_hmac('sha256', (string) $contactStartedAt, (string) config('Encryption')->key);
    ?>
    <form method="post" action="/contact/<?= esc($form['slug']) ?>/submit" class="form-card" style="max-width:760px;margin:0 auto;">
        <?= csrf_field() ?>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="hidden" name="form_started_at" value="<?= esc((string) $contactStartedAt) ?>">
        <input type="hidden" name="form_token" value="<?= esc($contactToken) ?>">

        <?php foreach ($form['fields'] as $field):
            $name  = $field['name'];
            $value = old($name) ?? '';
        ?>
            <div>
                <label for="<?= esc($name) ?>">
                    <?= esc($field['label']) ?>
                    <?php if ($field['required']): ?><span class="c-accent">*</span><?php endif; ?>
                </label>

                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?> rows="5"><?= esc($value) ?></textarea>

                <?php elseif ($field['type'] === 'select'): ?>
                    <select id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?>>
                        <option value="">-- Seçin --</option>
                        <?php foreach ($field['options'] ?? [] as $option): ?>
                            <option value="<?= esc($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($field['type'] === 'checkbox'): ?>
                    <?php foreach ($field['options'] ?? [] as $option): ?>
                        <label style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;font-weight:400;">
                            <input type="checkbox" name="<?= esc($name) ?>[]" value="<?= esc($option) ?>" <?= (is_array($value) && in_array($option, $value)) ? 'checked' : '' ?>>
                            <span><?= esc($option) ?></span>
                        </label>
                    <?php endforeach; ?>

                <?php else: ?>
                    <input type="<?= esc($field['type']) ?>" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($value) ?>" <?= $field['required'] ? 'required' : '' ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-accent" style="border:none;">Gönder</button>
    </form>
</div>

<?= $this->include('themes/corporate/partials/footer') ?>
