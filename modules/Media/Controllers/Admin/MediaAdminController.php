<?php

namespace Media\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use CodeIgniter\HTTP\ResponseInterface;
use Media\Helpers\MediaHelper;
use Media\Libraries\ImageProcessor;
use Media\Models\MediaFolderModel;
use Media\Models\MediaModel;

class MediaAdminController extends BaseAdminController
{
    protected MediaModel $mediaModel;
    protected MediaFolderModel $folderModel;

    public function __construct()
    {
        $this->mediaModel  = new MediaModel();
        $this->folderModel = new MediaFolderModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('media.view')) return $redirect;

        $folder  = $this->request->getGet('folder') !== null && $this->request->getGet('folder') !== ''
            ? (int) $this->request->getGet('folder')
            : null;
        $search  = (string) $this->request->getGet('search');
        $perPage = (int) ($this->request->getGet('per_page') ?: 24);
        $page    = (int) ($this->request->getGet('page') ?: 1);

        $builder = $this->mediaModel->orderBy('created_at', 'DESC');

        if ($search !== '') {
            $builder->search($search);
        }

        if ($folder !== null) {
            $builder->inFolder($folder, $this->folderModel);
        }

        $total = (int) $builder->countAllResults(false);
        $items = $builder->findAll($perPage, ($page - 1) * $perPage);

        $data['active']      = 'media';
        $data['title']       = 'Media';
        $data['items']       = $this->mediaModel->decorate($items);
        $data['folders']     = $this->folderModel->tree();
        $data['currentFolder'] = $folder;
        $data['pagination']  = [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total_items'  => $total,
            'total_pages'  => (int) ceil($total / $perPage),
        ];
        $data['search']   = $search;
        $data['canUpload'] = $this->can('media.upload');
        $data['canDelete'] = $this->can('media.delete');
        $data['canEdit']   = $this->can('media.edit');

        return $this->render('admin/media/index', $data);
    }

    public function upload()
    {
        if ($redirect = $this->requirePermission('media.upload')) return $redirect;

        $data['active']   = 'media';
        $data['title']    = 'Upload Media';
        $data['item']     = null;
        $data['folders']  = $this->folderModel->tree();
        $data['folderId'] = $this->request->getGet('folder') ? (int) $this->request->getGet('folder') : 0;

        return $this->render('admin/media/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('media.upload')) return $redirect;

        $files = $this->request->getFileMultiple('files');

        if (empty($files)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one file.');
        }

        $uploaded = 0;
        $errors   = [];

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                $errors[] = 'Invalid file.';
                continue;
            }

            $validation = MediaHelper::validateFile($file);

            if (! empty($validation)) {
                $errors[] = implode(', ', $validation);
                continue;
            }

            $uploadPath  = MediaHelper::getUploadPath();
            $filename    = MediaHelper::generateFilename($file->getClientName());
            $relativeDir = 'assets/uploads/' . date('Y/m') . '/';
            $fullDir     = FCPATH . $relativeDir;

            if (! is_dir($fullDir) && ! mkdir($fullDir, 0775, true)) {
                $errors[] = 'Failed to create upload directory.';
                continue;
            }

            $mainPath = $fullDir . $filename;

            if (! $file->move($fullDir, $filename)) {
                $errors[] = "Failed to move '{$file->getClientName()}'.";
                continue;
            }

            $thumbnailPath = null;
            $width         = null;
            $height        = null;
            $isImage       = MediaHelper::isImage($file->getMimeType());

            if ($isImage) {
                [$width, $height] = MediaHelper::getDimensions($mainPath);
            }

            $data = [
                'filename'       => $filename,
                'original_name'  => $file->getClientName(),
                'file_path'      => $relativeDir . $filename,
                'path'           => $relativeDir . $filename,
                'thumbnail_path' => $thumbnailPath,
                'mime_type'      => $file->getMimeType(),
                'size'           => (int) $file->getSize(),
                'alt_text'       => $this->request->getPost('alt_text'),
                'folder_id'      => $this->request->getPost('folder_id') ? (int) $this->request->getPost('folder_id') : null,
                'width'          => $width,
                'height'         => $height,
                'uploaded_by'    => session()->get('user_id'),
            ];

            if (! $this->mediaModel->insert($data)) {
                @unlink($mainPath);
                if ($thumbnailPath) {
                    @unlink(FCPATH . $thumbnailPath);
                }
                $errors[] = implode(', ', $this->mediaModel->errors());
                continue;
            }

            $id = $this->mediaModel->getInsertID();

            if ($isImage) {
                (new \Media\Libraries\MediaQueue())->enqueue('thumbnail', (int) $id);
            }

            $this->logActivity('created', 'media', (int) $id, "Uploaded media: {$data['filename']}");
            $uploaded++;
        }

        if ($uploaded > 0) {
            return redirect()->to('/admin/media')->with('success', "{$uploaded} file(s) uploaded successfully.");
        }

        return redirect()->back()->withInput()->with('error', implode(' ', $errors));
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('media.view')) return $redirect;

        $item = $this->mediaModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/media')->with('error', 'Media not found.');
        }

        $item = is_array($item) ? $item : (array) $item;
        $item = $this->mediaModel->decorate([$item])[0];

        $data['active']  = 'media';
        $data['title']   = 'Edit Media';
        $data['item']    = $item;
        $data['folders'] = $this->folderModel->tree();

        return $this->render('admin/media/edit', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $item = $this->mediaModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/media')->with('error', 'Media not found.');
        }

        $item = is_array($item) ? $item : (array) $item;
        $folderId = $this->request->getPost('folder_id');
        $folderId = ($folderId !== null && $folderId !== '') ? (int) $folderId : null;

        $data = [
            'alt_text'  => $this->request->getPost('alt_text'),
            'folder_id' => $folderId,
        ];

        if (! $this->mediaModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->mediaModel->errors()));
        }

        $this->logActivity('updated', 'media', $id, "Updated media metadata: {$item['filename']}");

        return redirect()->to('/admin/media')->with('success', 'Media updated successfully.');
    }

    public function resize(int $id)
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $item = $this->mediaModel->find($id);
        $item = is_array($item) ? $item : (array) $item;

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            return redirect()->back()->with('error', 'Image not found.');
        }

        $source = FCPATH . ($item['file_path'] ?? $item['path']);
        $dest   = $source;

        $maxWidth  = (int) $this->request->getPost('width');
        $maxHeight = (int) $this->request->getPost('height');

        if ($maxWidth < 1 && $maxHeight < 1) {
            return redirect()->back()->with('error', 'Provide at least one target dimension.');
        }

        if (ImageProcessor::resize($source, $dest, $maxWidth ?: 99999, $maxHeight ?: 99999)) {
            [$width, $height] = MediaHelper::getDimensions($dest);
            $this->mediaModel->update($id, ['width' => $width, 'height' => $height]);
            $fullDir = dirname($source);
            MediaHelper::createThumbnail($source, $fullDir . '/' . MediaHelper::getThumbnailName($item['filename']));
            $this->logActivity('updated', 'media', $id, "Resized image: {$item['filename']}");

            return redirect()->to("/admin/media/edit/{$id}")->with('success', 'Image resized successfully.');
        }

        return redirect()->back()->with('error', 'Failed to resize image.');
    }

    public function rotate(int $id)
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $item = $this->mediaModel->find($id);
        $item = is_array($item) ? $item : (array) $item;

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            return redirect()->back()->with('error', 'Image not found.');
        }

        $degrees = (int) $this->request->getPost('degrees') ?: 90;
        $source  = FCPATH . ($item['file_path'] ?? $item['path']);

        if (ImageProcessor::rotate($source, $source, $degrees)) {
            [$width, $height] = MediaHelper::getDimensions($source);
            $this->mediaModel->update($id, ['width' => $width, 'height' => $height]);
            $fullDir = dirname($source);
            MediaHelper::createThumbnail($source, $fullDir . '/' . MediaHelper::getThumbnailName($item['filename']));
            $this->logActivity('updated', 'media', $id, "Rotated image {$degrees}°: {$item['filename']}");

            return redirect()->to("/admin/media/edit/{$id}")->with('success', 'Image rotated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to rotate image.');
    }

    public function crop(int $id): ResponseInterface
    {
        if ($redirect = $this->requirePermission('media.edit')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $item = $this->mediaModel->find($id);
        $item = is_array($item) ? $item : (array) $item;

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Image not found']);
        }

        $x     = (int) $this->request->getPost('x');
        $y     = (int) $this->request->getPost('y');
        $cropW = (int) $this->request->getPost('width');
        $cropH = (int) $this->request->getPost('height');

        if ($cropW < 1 || $cropH < 1) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid crop dimensions']);
        }

        $source = FCPATH . ($item['file_path'] ?? $item['path']);

        if (ImageProcessor::crop($source, $source, $cropW, $cropH, $x, $y)) {
            [$width, $height] = MediaHelper::getDimensions($source);
            $this->mediaModel->update($id, ['width' => $width, 'height' => $height]);
            $fullDir = dirname($source);
            MediaHelper::createThumbnail($source, $fullDir . '/' . MediaHelper::getThumbnailName($item['filename']));
            $this->logActivity('edited', 'media', $id, "Cropped image: {$item['filename']}");

            return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => 'Image cropped successfully']);
        }

        return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to crop image']);
    }

    public function picker()
    {
        if ($redirect = $this->requirePermission('media.view')) return $redirect;

        $folder = $this->request->getGet('folder') !== null && $this->request->getGet('folder') !== ''
            ? (int) $this->request->getGet('folder')
            : null;
        $search  = (string) $this->request->getGet('search');
        $perPage = 24;
        $page    = (int) ($this->request->getGet('page') ?: 1);

        $builder = $this->mediaModel->orderBy('created_at', 'DESC');

        if ($search !== '') {
            $builder->search($search);
        }

        if ($folder !== null) {
            $builder->inFolder($folder, $this->folderModel);
        }

        $total = (int) $builder->countAllResults(false);
        $items = $builder->findAll($perPage, ($page - 1) * $perPage);

        $data['active']    = 'media';
        $data['title']     = 'Media Picker';
        $data['items']     = $this->mediaModel->decorate($items);
        $data['folders']   = $this->folderModel->tree();
        $data['search']    = $search;
        $data['page']      = $page;
        $data['totalPages']  = (int) ceil($total / $perPage);

        return $this->render('admin/media/picker', $data);
    }

    public function createFolder()
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $data['active']  = 'media';
        $data['title']   = 'New Folder';
        $data['folders'] = $this->folderModel->tree();

        return $this->render('admin/media/folder_form', $data);
    }

    public function storeFolder()
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $name     = (string) $this->request->getPost('name');
        $parentId = $this->request->getPost('parent_id');

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Folder name is required.');
        }

        $slug    = url_title($name, '-', true);
        $unique  = $slug;
        $counter = 1;

        while ($this->folderModel->findBySlug($unique)) {
            $unique = $slug . '-' . $counter++;
        }

        $data = [
            'name'      => $name,
            'slug'      => $unique,
            'parent_id' => ($parentId !== null && $parentId !== '') ? (int) $parentId : null,
        ];

        if (! $this->folderModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->folderModel->errors()));
        }

        $id = $this->folderModel->getInsertID();
        $this->logActivity('created', 'media-folder', (int) $id, "Created media folder: {$name}");

        return redirect()->to('/admin/media')->with('success', 'Folder created successfully.');
    }

    public function deleteFolder(int $id)
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $folder = $this->folderModel->find($id);

        if (! $folder) {
            return redirect()->to('/admin/media')->with('error', 'Folder not found.');
        }

        $this->mediaModel->where('folder_id', $id)->set(['folder_id' => null])->update();

        foreach ($this->folderModel->childIds($id) as $childId) {
            $this->folderModel->delete($childId);
        }

        $this->logActivity('deleted', 'media-folder', $id, "Deleted media folder: {$folder['name']}");

        return redirect()->to('/admin/media')->with('success', 'Folder deleted successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('media.delete')) return $redirect;

        $item = $this->mediaModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/media')->with('error', 'Media not found.');
        }

        $item = is_array($item) ? $item : (array) $item;

        if (! empty($item['file_path'])) {
            @unlink(FCPATH . $item['file_path']);
        }
        if (! empty($item['thumbnail_path'])) {
            @unlink(FCPATH . $item['thumbnail_path']);
        }

        $this->mediaModel->delete($id);

        $this->logActivity('deleted', 'media', $id, "Deleted media: {$item['filename']}");

        return redirect()->to('/admin/media')->with('success', 'Media deleted successfully.');
    }

    public function queue()
    {
        if ($redirect = $this->requirePermission('media.view')) return $redirect;

        $queue = new \Media\Libraries\MediaQueue();

        $data['active'] = 'media_queue';
        $data['title']  = 'Media Queue';
        $data['stats']  = $queue->stats();
        $data['jobs']   = $queue->recent(100);
        $data['canRun'] = $this->can('media.edit');

        return $this->render('admin/media/queue', $data);
    }

    public function runQueue()
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $limit   = max(1, (int) ($this->request->getPost('limit') ?: 10));
        $summary = (new \Media\Libraries\MediaQueue())->work($limit, null, 'admin');

        $message = "Queue run completed: claimed {$summary['claimed']}, done {$summary['done']}, failed {$summary['failed']}.";

        return redirect()->to('/admin/media/queue')
            ->with($summary['failed'] > 0 ? 'warning' : 'success', $message);
    }

    public function retryJob(int $id)
    {
        if ($redirect = $this->requirePermission('media.edit')) return $redirect;

        $job = (new \Media\Models\MediaJobModel())->find($id);

        if (! $job) {
            return redirect()->to('/admin/media/queue')->with('error', 'Job not found.');
        }

        (new \Media\Libraries\MediaQueue())->retry($id);

        return redirect()->to('/admin/media/queue')->with('success', 'Job requeued.');
    }
}