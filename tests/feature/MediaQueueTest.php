<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Media\Libraries\MediaQueue;
use Media\Models\MediaJobModel;
use Media\Models\MediaModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class MediaQueueTest extends CIUnitTestCase
{
    use FeatureTestTrait {
        setupRequest as protected traitSetupRequest;
    }

    protected ?FakeQTUploadedFile $uploadFile = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('media_jobs')->where('1=1')->delete();
        $db->table('media')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'queueuser',
            'email' => 'queue@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    /**
     * Generates a small real JPEG at $path so tests don't depend on an
     * external fixture file.
     */
    private function generateTestImage(string $path, int $width = 800, int $height = 600): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, 100, 150, 200);
        imagefill($image, 0, 0, $bg);
        imagejpeg($image, $path, 90);
        imagedestroy($image);
    }

    private function seedImageMedia(): array
    {
        $dir = FCPATH . 'assets/uploads/test/queue/';

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . 'queue-test-image.jpg';
        $this->generateTestImage($file);

        $relative = str_replace(FCPATH, '', $file);

        $mediaId = (new MediaModel())->insert([
            'filename'      => 'queue-test-image.jpg',
            'original_name' => 'queue-test-image.jpg',
            'mime_type'     => 'image/jpeg',
            'size'          => filesize($file),
            'file_path'     => $relative,
            'path'          => $relative,
            'width'         => 800,
            'height'        => 600,
            'uploaded_by'   => 1,
        ]);

        return ['id' => (int) $mediaId, 'path' => $file];
    }

    public function testEnqueueCreatesPendingJob(): void
    {
        $id = (new MediaQueue())->enqueue('thumbnail', 5, [], 3);

        $job = (new MediaJobModel())->find($id);

        $this->assertNotNull($job);
        $this->assertSame('thumbnail', $job['type']);
        $this->assertSame('pending', $job['status']);
        $this->assertSame(0, (int) $job['attempts']);
    }

    public function testClaimMovesJobsToProcessingAndHonorsWorkerLock(): void
    {
        $queue = new MediaQueue();
        $queue->enqueue('thumbnail', 1);
        $queue->enqueue('thumbnail', 2);

        $claimed = $queue->claim(10, null, 'worker-a');

        $this->assertCount(2, $claimed);

        $jobs = (new MediaJobModel())->findAll();
        foreach ($jobs as $job) {
            $this->assertSame('processing', $job['status']);
        }

        $none = $queue->claim(10, null, 'worker-b');
        $this->assertSame([], $none);
    }

    public function testWorkCreatesThumbnailAndMarksDone(): void
    {
        $media = $this->seedImageMedia();
        $queue = new MediaQueue();
        $queue->enqueue('thumbnail', $media['id']);

        $summary = $queue->work(10, null, 'cli');

        $this->assertSame(1, $summary['done']);
        $this->assertSame(0, $summary['failed']);

        $job = (new MediaJobModel())->orderBy('id', 'DESC')->first();
        $this->assertSame('done', $job['status']);

        $item = (new MediaModel())->find($media['id']);
        $this->assertNotEmpty($item['thumbnail_path']);
        $this->assertFileExists(FCPATH . $item['thumbnail_path']);
    }

    public function testFailedJobRetriesThenFailsPermanently(): void
    {
        $queue = new MediaQueue();
        $id = $queue->enqueue('thumbnail', 999999, [], 2);

        $queue->work(10, null, 'cli'); // attempt 1
        $job1 = (new MediaJobModel())->find($id);
        $this->assertSame('pending', $job1['status']);
        $this->assertSame(1, (int) $job1['attempts']);
        $this->assertNotEmpty($job1['error']);

        // Simulate the backoff window elapsing so attempt 2 can be claimed.
        $db = \Config\Database::connect();
        $db->table('media_jobs')->where('id', $id)->update([
            'available_at' => date('Y-m-d H:i:s', time() - 60),
        ]);

        $queue->work(10, null, 'cli'); // attempt 2
        $job2 = (new MediaJobModel())->find($id);
        $this->assertSame('failed', $job2['status']);
        $this->assertSame(2, (int) $job2['attempts']);
        $this->assertNotEmpty($job2['error']);
    }

    public function testRetryRequeuesFailedJob(): void
    {
        $queue = new MediaQueue();
        $id = $queue->enqueue('thumbnail', 999999, [], 1);
        $queue->work(10, null, 'cli');

        $job = (new MediaJobModel())->find($id);
        $this->assertSame('failed', $job['status']);

        $queue->retry($id);
        $job = (new MediaJobModel())->find($id);
        $this->assertSame('pending', $job['status']);
        $this->assertSame(0, (int) $job['attempts']);
        $this->assertNull($job['error']);
    }

    public function testUploadEnqueuesThumbnailJob(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'queue@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $dir = WRITEPATH . 'uploads/queue-upload/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $uploadFile = $dir . 'upload.jpg';
        $this->generateTestImage($uploadFile);

        $this->uploadFile = new FakeQTUploadedFile($uploadFile);

        $this->post('/admin/media/store', [
            'alt_text' => 'Queue upload test',
        ]);

        $job = (new MediaJobModel())->where('type', 'thumbnail')->orderBy('id', 'DESC')->first();
        $this->assertNotNull($job);
        $this->assertSame('pending', $job['status']);
    }

    protected function setupRequest(string $method, ?string $path = null): \CodeIgniter\HTTP\IncomingRequest
    {
        $request = $this->traitSetupRequest($method, $path);

        if ($this->uploadFile !== null) {
            $collection = new FakeQTFileCollection();
            $collection->addFile('files', [$this->uploadFile]);
            $this->setPrivateProperty($request, 'files', $collection);
            $this->uploadFile = null;
        }

        return $request;
    }

    public function testAdminQueuePageRequiresLoginAndRenders(): void
    {
        $result = $this->get('/admin/media/queue');
        $result->assertRedirectTo('/admin/login');

        $this->post('/admin/auth/attempt', [
            'email' => 'queue@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->get('/admin/media/queue');
        $result->assertOK();
        $result->assertSee('Media Queue');
        $result->assertSee('Run Queue Now');
    }
}

class FakeQTUploadedFile extends \CodeIgniter\HTTP\Files\UploadedFile
{
    public function __construct(
        string $sourcePath,
        string $originalName = 'upload.jpg',
        ?string $mimeType = 'image/jpeg',
        ?int $size = null,
        ?int $error = UPLOAD_ERR_OK,
        ?string $clientPath = null,
    ) {
        parent::__construct(
            $sourcePath,
            $originalName,
            $mimeType,
            $size ?? (int) filesize($sourcePath),
            $error,
            $clientPath,
        );
    }

    public function isValid(): bool
    {
        return true;
    }

    public function move(string $targetPath, ?string $name = null, bool $overwrite = false): bool
    {
        $destination = rtrim($targetPath, '/') . '/' . ($name ?? $this->getName());

        if (! $overwrite && is_file($destination)) {
            return false;
        }

        return copy($this->getTempName(), $destination);
    }
}

class FakeQTFileCollection extends \CodeIgniter\HTTP\Files\FileCollection
{
    public function addFile(string $name, array $files): void
    {
        $this->files[$name] = $files;
    }
}