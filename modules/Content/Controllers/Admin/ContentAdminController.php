<?php

namespace Content\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Libraries\ContentRenderer;
use Content\Models\ContentModel;
use Content\Models\ContentRevisionModel;

class ContentAdminController extends BaseAdminController
{
    protected ContentModel $contentModel;
    protected ContentRevisionModel $revisionModel;

    public function __construct()
    {
        $this->contentModel  = new ContentModel();
        $this->revisionModel = new ContentRevisionModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('content.view')) {
            return $redirect;
        }

        $data['active'] = 'content';
        $data['title']  = 'Content';

        $perPage = (int) ($this->request->getGet('per_page') ?: 10);
        $page    = (int) ($this->request->getGet('page') ?: 1);
        $search  = (string) $this->request->getGet('search');
        $locale  = (string) ($this->request->getGet('locale') ?: '');

        $this->contentModel->orderBy('created_at', 'DESC');

        if ($locale !== '') {
            $this->contentModel->where('content.locale', $locale);
        }

        if ($search !== '') {
            $this->contentModel->groupStart()
                ->like('title', $search)
                ->orLike('body', $search)
                ->groupEnd();
        }

        $total = (int) $this->contentModel->countAllResults(false);
        $data['items'] = $this->contentModel->findAll($perPage, ($page - 1) * $perPage);
        $data['pagination'] = [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total_items'  => $total,
            'total_pages'  => (int) ceil($total / $perPage),
        ];
        $data['search']    = $search;
        $data['locale']    = $locale;
        $data['locales']   = $this->getActiveLocales();
        $data['canCreate'] = $this->can('content.create');
        $data['canEdit']   = $this->can('content.edit');
        $data['canDelete'] = $this->can('content.delete');

        return $this->render('admin/content/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('content.create')) {
            return $redirect;
        }

        $data['active'] = 'content';
        $data['title']  = 'New Content';
        $data['item']   = null;
        $data['targetLocale'] = '';
        $data['locales'] = $this->getActiveLocales();
        $data['defaultLocale'] = $this->getDefaultLocale();
        $data['relatedIds'] = [];
        $data['allContent'] = $this->contentModel->where('status', 'published')->orderBy('title', 'ASC')->findAll();
        $data['customValues'] = [];
        $data['schemas'] = $this->allSchemasJson();

        return $this->render('admin/content/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('content.create')) {
            return $redirect;
        }

        $data = $this->prepareContentData();

        if ($errors = $this->validateCustomFields($data['custom_data'], $data['content_type'])) {
            return redirect()->back()->withInput()->with('error', implode(', ', $errors));
        }

        if (empty($data['translation_group_id'])) {
            $data['translation_group_id'] = $this->generateGroupId();
        }

        $id = $this->contentModel->insert($data);

        if (! $id) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->contentModel->errors()));
        }

        $this->saveRelatedContent((int) $id);

        $this->logActivity('created', 'content', (int) $id, "Created content: {$data['title']}");

        \App\Libraries\Hooks::doAction('content.created', (int) $id, $data['title'], $data['slug'] ?? null, $data['status'] ?? null);

        (new \User\Libraries\Webhook())->dispatch('content.created', [
            'id'    => (int) $id,
            'title' => $data['title'],
            'slug'  => $data['slug'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        return redirect()->to('/admin/content')->with('success', 'Content created successfully.');
    }

    /**
     * AJAX endpoint returning the sanitised, rendered HTML for the current
     * editor body. Used by the admin form's live preview button.
     */
    public function preview()
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $body = (string) ($this->request->getPost('body') ?? '');

        if (! $this->request->isAJAX()) {
            return redirect()->to('/admin/content');
        }

        return $this->response->setStatusCode(200)->setBody(
            ContentRenderer::render($body)
        );
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $data['active'] = 'content';
        $data['title']  = 'Edit Content';
        $data['item']   = $item;
        $data['locales'] = $this->getActiveLocales();
        $data['defaultLocale'] = $this->getDefaultLocale();
        $data['translations'] = $this->contentModel->translations($item->translation_group_id);
        $data['relatedIds'] = (new \Content\Models\ContentRelationModel())->relatedIds((int) $id);
        $data['allContent'] = $this->contentModel->where('status', 'published')->orderBy('title', 'ASC')->findAll();
        $data['customValues'] = $item->custom_data ?: [];
        $data['schemas'] = $this->allSchemasJson();

        return $this->render('admin/content/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $this->revisionModel->createRevision($item->toArray(), (int) session()->get('user_id'));

        $data = $this->prepareContentData();
        $data['translation_group_id'] = $item->translation_group_id ?: $this->generateGroupId();

        if ($errors = $this->validateCustomFields($data['custom_data'], $data['content_type'])) {
            return redirect()->back()->withInput()->with('error', implode(', ', $errors));
        }

        if (! $this->contentModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->contentModel->errors()));
        }

        $this->saveRelatedContent($id);

        $this->logActivity('updated', 'content', $id, "Updated content: {$data['title']}");

        \App\Libraries\Hooks::doAction('content.updated', (int) $id, $data['title'], $data['slug'] ?? null, $data['status'] ?? null);

        return redirect()->to('/admin/content')->with('success', 'Content updated successfully.');
    }

    public function revisions(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $data['active'] = 'content';
        $data['title']  = 'Revision History';
        $data['item']   = $item;
        $data['revisions'] = $this->revisionModel->where('content_id', $id)
                                                  ->orderBy('version', 'DESC')
                                                  ->findAll();
        $data['canRestore'] = $this->can('content.edit');

        return $this->render('admin/content/revisions', $data);
    }

    public function restore(int $id, int $revisionId)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $revision = $this->revisionModel->findRevision($id, $revisionId);

        if (! $revision) {
            return redirect()->to("/admin/content/revisions/{$id}")->with('error', 'Revision not found.');
        }

        $this->revisionModel->createRevision($item->toArray(), (int) session()->get('user_id'));

        $restoreData = [
            'title'                => $revision['title'],
            'slug'                 => $revision['slug'],
            'body'                 => $revision['body'],
            'excerpt'              => $revision['excerpt'],
            'content_type'         => $revision['content_type'],
            'status'               => $revision['status'],
            'featured_image'       => $revision['featured_image'],
            'meta_title'           => $revision['meta_title'],
            'meta_description'     => $revision['meta_description'],
            'meta_keywords'        => $revision['meta_keywords'],
            'canonical_url'        => $revision['canonical_url'],
            'published_at'         => $revision['published_at'],
            'author_id'            => session()->get('user_id'),
            'locale'               => $item->locale,
            'translation_group_id' => $item->translation_group_id,
        ];

        if (! $this->contentModel->update($id, $restoreData)) {
            return redirect()->back()->with('error', implode(', ', $this->contentModel->errors()));
        }

        $this->logActivity('restored', 'content', $id, "Restored content revision #{$revision['version']}");

        return redirect()->to("/admin/content/revisions/{$id}")->with('success', 'Revision restored successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('content.delete')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $this->contentModel->delete($id);

        \App\Libraries\Hooks::doAction('content.deleted', (int) $id, $item->title ?? null);

        $this->logActivity('deleted', 'content', $id, "Deleted content: {$item->title}");

        (new \User\Libraries\Webhook())->dispatch('content.deleted', [
            'id'    => $id,
            'title' => $item->title,
            'slug'  => $item->slug,
        ]);

        return redirect()->to('/admin/content')->with('success', 'Content deleted successfully.');
    }

    public function featured(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $state = (int) $this->request->getPost('featured');
        $this->contentModel->update($id, ['is_featured' => $state ? 1 : 0]);

        $this->logActivity($state ? 'featured' : 'unfeatured', 'content', $id, "Toggled featured on: {$item->title}");

        return redirect()->to('/admin/content')->with('success', $state ? 'Content featured.' : 'Content unfeatured.');
    }

    public function translations(int $id)
    {
        if ($redirect = $this->requirePermission('content.view')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $data['active'] = 'content';
        $data['title']  = 'Translations';
        $data['item']   = $item;
        $data['translations'] = $this->contentModel->translations($item->translation_group_id);
        $data['locales'] = $this->getActiveLocales();

        return $this->render('admin/content/translations', $data);
    }

    public function createTranslation(int $id)
    {
        if ($redirect = $this->requirePermission('content.create')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $targetLocale = $this->request->getGet('locale') ?: '';

        $data['active'] = 'content';
        $data['title']  = 'New Translation';
        $data['source'] = $item;
        $data['targetLocale'] = $targetLocale;
        $data['locales'] = $this->getActiveLocales();
        $data['defaultLocale'] = $this->getDefaultLocale();
        $data['item'] = null;
        $data['customValues'] = $item->custom_data ?: [];
        $data['schemas'] = $this->allSchemasJson();

        return $this->render('admin/content/form', $data);
    }

    public function exportTranslation(int $id, string $targetLocale)
    {
        if ($redirect = $this->requirePermission('content.view')) {
            return $redirect;
        }

        $item = $this->contentModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/content')->with('error', 'Content not found.');
        }

        $export = [
            'translation_group_id' => $item->translation_group_id ?: $this->generateGroupId(),
            'source_locale'        => $item->locale,
            'target_locale'        => $targetLocale,
            'content_type'         => $item->content_type,
            'reference_slug'       => $item->slug,
            'fields' => [
                'title'            => $item->title,
                'slug'             => $item->slug,
                'body'             => $item->body,
                'excerpt'          => $item->excerpt,
                'featured_image'   => $item->featured_image,
                'meta_title'       => $item->meta_title,
                'meta_description' => $item->meta_description,
                'meta_keywords'    => $item->meta_keywords ?? '',
                'canonical_url'    => $item->canonical_url ?? '',
            ],
        ];

        $filename = 'translation-' . $item->slug . '-' . $targetLocale . '.json';

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function importTranslation()
    {
        if ($redirect = $this->requirePermission('content.create')) {
            return $redirect;
        }

        $file = $this->request->getFile('translation_file');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid JSON file.');
        }

        $content = file_get_contents($file->getTempName());
        $json    = json_decode($content, true);

        if (! $json || ! isset($json['fields'], $json['target_locale'])) {
            return redirect()->back()->with('error', 'Invalid translation file format.');
        }

        $fields = $json['fields'];
        $locale = $json['target_locale'];
        $groupId = $json['translation_group_id'] ?? $this->generateGroupId();
        $activeLocales = $this->getActiveLocales();

        if (! in_array($locale, $activeLocales, true)) {
            return redirect()->back()->with('error', "Locale '{$locale}' is not active.");
        }

        $slug = $fields['slug'] ?? '';
        $slug = preg_replace('/\.html?$/', '', $slug);
        $slug = trim($slug, '/');
        $slug = $this->makeUniqueSlug($slug, $locale);

        $insertData = [
            'locale'               => $locale,
            'translation_group_id' => $groupId,
            'content_type'         => $json['content_type'] ?? 'article',
            'title'                => $fields['title'] ?? 'Untitled',
            'slug'                 => $slug,
            'body'                 => $fields['body'] ?? '',
            'excerpt'              => $fields['excerpt'] ?? null,
            'status'               => 'draft',
            'author_id'            => session()->get('user_id'),
            'featured_image'       => $fields['featured_image'] ?? null,
            'meta_title'           => $fields['meta_title'] ?? null,
            'meta_description'     => $fields['meta_description'] ?? null,
            'meta_keywords'        => $fields['meta_keywords'] ?? null,
            'canonical_url'        => $fields['canonical_url'] ?? null,
            'published_at'         => null,
        ];

        $existing = $this->contentModel
            ->where('translation_group_id', $groupId)
            ->where('locale', $locale)
            ->first();

        if ($existing) {
            $this->contentModel->update($existing->id, $insertData);
            $id = $existing->id;
            $message = 'Translation updated successfully.';
        } else {
            $id = $this->contentModel->insert($insertData);
            $message = 'Translation imported successfully.';
        }

        if (! $id) {
            return redirect()->back()->with('error', implode(', ', $this->contentModel->errors()));
        }

        $this->logActivity('imported', 'content', (int) $id, "Imported {$locale} translation: {$insertData['title']}");

        return redirect()->to("/admin/content/edit/{$id}")->with('success', $message);
    }

    protected function prepareContentData(): array
    {
        $status = $this->request->getPost('status') ?: 'draft';
        $publishedAt = $this->request->getPost('published_at');

        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $contentType = $this->request->getPost('content_type') ?: 'article';

        return [
            'locale'               => $this->request->getPost('locale') ?: $this->getDefaultLocale(),
            'translation_group_id' => $this->request->getPost('translation_group_id') ?: null,
            'title'                => $this->request->getPost('title'),
            'slug'                 => $this->request->getPost('slug'),
            'body'                 => $this->request->getPost('body'),
            'excerpt'              => $this->request->getPost('excerpt'),
            'content_type'         => $contentType,
            'status'               => $status,
            'author_id'            => session()->get('user_id'),
            'featured_image'       => $this->request->getPost('featured_image'),
            'meta_title'           => $this->request->getPost('meta_title'),
            'meta_description'     => $this->request->getPost('meta_description'),
            'meta_keywords'        => $this->request->getPost('meta_keywords'),
            'canonical_url'        => $this->request->getPost('canonical_url'),
            'published_at'         => $publishedAt,
            'is_featured'          => $this->request->getPost('is_featured') ? 1 : 0,
            'custom_data'          => json_encode((new \Content\Libraries\CustomFields())->collect($this->request->getPost(), $contentType), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    /**
     * Validate user-defined custom fields for the given content type and
     * return the collected custom data map when valid.
     */
    protected function collectCustomData(string $contentType): array
    {
        $custom = new \Content\Libraries\CustomFields();
        $data   = $custom->collect($this->request->getPost(), $contentType);

        if ($errors = $custom->validate($data, $contentType)) {
            $this->data['custom_field_errors'] = $errors;
        }

        return $data;
    }

    /**
     * Validate collected custom_data against the content type schema and
     * return the list of field errors (empty when valid).
     */
    protected function validateCustomFields(array|string $customData, string $contentType): array
    {
        if (is_string($customData)) {
            $decoded = json_decode($customData, true);
            $customData = is_array($decoded) ? $decoded : [];
        }

        return (new \Content\Libraries\CustomFields())->validate($customData, $contentType);
    }

    /**
     * All registered content type schemas keyed by content type, as JSON for
     * the admin form's dynamic field renderer.
     */
    protected function allSchemasJson(): string
    {
        $rows  = (new \Content\Models\ContentTypeSchemaModel())->allWithTypes();
        $byKey = [];

        foreach ($rows as $row) {
            $byKey[$row['content_type']] = $row['fields'] ?? [];
        }

        return htmlspecialchars((string) json_encode($byKey), ENT_QUOTES, 'UTF-8');
    }

    protected function getActiveLocales(): array
    {
        $settingModel = new \Setting\Models\SettingModel();
        $active = $settingModel->getSetting('site_active_locales', 'tr');
        return array_filter(array_map('trim', explode(',', $active)));
    }

    protected function getDefaultLocale(): string
    {
        $settingModel = new \Setting\Models\SettingModel();
        return $settingModel->getSetting('site_default_locale', 'tr');
    }

    protected function generateGroupId(): string
    {
        return bin2hex(random_bytes(8));
    }

    protected function saveRelatedContent(int $id): void
    {
        $relatedModel  = new \Content\Models\ContentRelationModel();
        $contentModel  = $this->contentModel;

        $relatedIds = $this->request->getPost('related_ids') ?? [];
        if (! is_array($relatedIds)) {
            $relatedIds = [];
        }

        $relatedIds = array_values(array_filter(array_map('intval', $relatedIds)));
        $relatedModel->clearRelations($id);

        foreach ($relatedIds as $relatedId) {
            if ($relatedId > 0 && $relatedId !== $id && $contentModel->find($relatedId)) {
                $relatedModel->addRelation($id, $relatedId);
            }
        }
    }

    protected function makeUniqueSlug(string $slug, string $locale): string
    {
        $base = $slug ?: 'translation';
        $candidate = $base;
        $counter = 1;

        while ($this->contentModel->where('slug', $candidate)->where('locale', $locale)->first()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
