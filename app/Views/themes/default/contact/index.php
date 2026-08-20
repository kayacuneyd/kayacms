<?= $this->include('themes/default/partials/header') ?>

<main class="ck-flex-1">
    <div class="ck-max-w-4xl ck-mx-auto ck-px-4 ck-py-12 sm:ck-px-6 lg:ck-px-8">
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-8">
            <h1 class="ck-text-3xl ck-font-bold ck-text-gray-900 ck-mb-4"><?= esc($pageTitle) ?></h1>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="ck-mb-6 ck-p-4 ck-bg-green-50 ck-text-green-700 ck-rounded ck-border ck-border-green-200">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="ck-mb-6 ck-p-4 ck-bg-red-50 ck-text-red-700 ck-rounded ck-border ck-border-red-200">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php
                $contactStartedAt = time();
                $contactToken = hash_hmac('sha256', (string) $contactStartedAt, (string) config('Encryption')->key);
            ?>
            <form method="post" action="/contact/<?= esc($form['slug']) ?>/submit" class="ck-space-y-6">
                <?= csrf_field() ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                <input type="hidden" name="form_started_at" value="<?= esc((string) $contactStartedAt) ?>">
                <input type="hidden" name="form_token" value="<?= esc($contactToken) ?>">

                <?php foreach ($form['fields'] as $field):
                    $name = $field['name'];
                    $value = old($name) ?? '';
                ?>
                    <div>
                        <label for="<?= esc($name) ?>" class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">
                            <?= esc($field['label']) ?>
                            <?php if ($field['required']): ?><span class="ck-text-red-500">*</span><?php endif; ?>
                        </label>

                        <?php if ($field['type'] === 'textarea'): ?>
                            <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?> class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" rows="5"><?= esc($value) ?></textarea>

                        <?php elseif ($field['type'] === 'select'): ?>
                            <select id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $field['required'] ? 'required' : '' ?> class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                                <option value="">-- Select --</option>
                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                    <option value="<?= esc($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($field['type'] === 'checkbox'): ?>
                            <div class="ck-space-y-2">
                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                    <label class="ck-flex ck-items-center ck-gap-2">
                                        <input type="checkbox" name="<?= esc($name) ?>[]" value="<?= esc($option) ?>" <?= (is_array($value) && in_array($option, $value)) ? 'checked' : '' ?> class="ck-w-4 ck-h-4">
                                        <span class="ck-text-sm ck-text-gray-700"><?= esc($option) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <input type="<?= esc($field['type']) ?>" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($value) ?>" <?= $field['required'] ? 'required' : '' ?> class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="ck-px-6 ck-py-3 ck-bg-blue-600 ck-text-white ck-font-medium ck-rounded hover:ck-bg-blue-700">Send Message</button>
            </form>
        </div>
    </div>
</main>

<?= $this->include('themes/default/partials/footer') ?>
