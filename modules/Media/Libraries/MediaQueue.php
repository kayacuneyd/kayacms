<?php

namespace Media\Libraries;

use Media\Helpers\MediaHelper;
use Media\Models\MediaJobModel;
use Media\Models\MediaModel;
use User\Libraries\SecurityLog;

/**
 * Simple, database-backed async job queue for media processing.
 *
 * Jobs are claimed by a worker (CLI command or the admin "run now" action),
 * executed synchronously by that worker, then marked done or failed with an
 * exponential backoff for retried attempts.
 */
class MediaQueue
{
    protected MediaJobModel $jobModel;
    protected MediaModel $mediaModel;

    public function __construct(?MediaJobModel $jobModel = null)
    {
        $this->jobModel   = $jobModel ?? new MediaJobModel();
        $this->mediaModel = new MediaModel();
    }

    /**
     * Add a media processing job to the queue.
     */
    public function enqueue(string $type, ?int $mediaId = null, array $payload = [], int $maxAttempts = 3, int $delaySeconds = 0): int
    {
        return (int) $this->jobModel->insert([
            'type'         => $type,
            'media_id'     => $mediaId,
            'payload'      => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status'       => 'pending',
            'attempts'     => 0,
            'max_attempts' => max(1, $maxAttempts),
            'available_at' => $delaySeconds > 0
                ? date('Y-m-d H:i:s', time() + $delaySeconds)
                : date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Claim the next batch of due jobs for a worker. Claimed rows transition
     * to "processing" so another worker cannot pick them up.
     *
     * @return list<array<string, mixed>>
     */
    public function claim(int $limit = 10, ?string $type = null, string $worker = 'cli'): array
    {
        $jobs = $this->jobModel->pending($type)
                               ->orderBy('available_at', 'ASC')
                               ->orderBy('id', 'ASC')
                               ->findAll($limit);

        if ($jobs === []) {
            return [];
        }

        $claimed = [];

        foreach ($jobs as $job) {
            $id = (int) $job['id'];

            $updatedRows = $this->jobModel->protect(false)->where('id', $id)
                ->where('status', 'pending')
                ->set(['status' => 'processing', 'attempts' => (int) $job['attempts'] + 1, 'updated_at' => date('Y-m-d H:i:s')])
                ->update();

            // Another worker grabbed it between select and update: skip.
            if ($updatedRows === 0) {
                continue;
            }

            $claimed[] = $job;
        }

        return $claimed;
    }

    /**
     * Process a batch of jobs (claim + execute + settle). Returns a summary.
     */
    public function work(int $limit = 10, ?string $type = null, string $worker = 'cli'): array
    {
        $summary = ['claimed' => 0, 'done' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($this->claim($limit, $type, $worker) as $job) {
            $summary['claimed']++;

            try {
                $result = $this->process($job);
                $this->markDone((int) $job['id'], $result);
                $summary['done']++;
            } catch (\Throwable $e) {
                $this->markFailed((int) $job['id'], $e->getMessage());
                $summary['failed']++;
            }
        }

        return $summary;
    }

    /**
     * Execute a single job payload. Throws on failure.
     */
    public function process(array $job): array
    {
        $type     = $job['type'] ?? '';
        $mediaId  = (int) ($job['media_id'] ?? 0);
        $payload  = json_decode((string) ($job['payload'] ?? ''), true) ?: [];

        return match ($type) {
            'thumbnail' => $this->makeThumbnail($mediaId),
            'derivatives' => $this->makeDerivatives($mediaId),
            'resize'    => $this->resize($mediaId, $payload),
            default     => throw new \RuntimeException("Unknown job type: {$type}"),
        };
    }

    public function markDone(int $id, array $result = []): bool
    {
        return $this->jobModel->update($id, [
            'status' => 'done',
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE),
            'error'  => null,
        ]);
    }

    /**
     * Mark a job failed: retry with exponential backoff until max_attempts,
     * then move to failed permanently.
     */
    public function markFailed(int $id, string $error, int $backoffBase = 30): bool
    {
        $job = $this->jobModel->find($id);

        if (! $job) {
            return false;
        }

        $attempts    = (int) $job['attempts'];
        $maxAttempts = (int) ($job['max_attempts'] ?: 3);

        if ($attempts >= $maxAttempts) {
            return $this->jobModel->update($id, [
                'status' => 'failed',
                'error'  => $error,
            ]);
        }

        $delay = $backoffBase * (2 ** ($attempts - 1));

        return $this->jobModel->update($id, [
            'status'       => 'pending',
            'available_at' => date('Y-m-d H:i:s', time() + $delay),
            'error'        => $error,
        ]);
    }

    /**
     * Requeue a permanently failed job from scratch.
     */
    public function retry(int $id): bool
    {
        return $this->jobModel->update($id, [
            'status'       => 'pending',
            'attempts'     => 0,
            'available_at' => date('Y-m-d H:i:s'),
            'error'        => null,
        ]);
    }

    public function stats(): array
    {
        return $this->jobModel->stats();
    }

    public function recent(int $limit = 50): array
    {
        return $this->jobModel->orderBy('id', 'DESC')->findAll($limit);
    }

    public function countPendingFor(int $mediaId): int
    {
        return (int) $this->jobModel->where('media_id', $mediaId)
                                    ->whereIn('status', ['pending', 'processing'])
                                    ->countAllResults();
    }

    protected function makeThumbnail(int $mediaId): array
    {
        $item = $this->mediaModel->find($mediaId);

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            throw new \RuntimeException('Image not found or not an image.');
        }

        $source      = FCPATH . ($item['file_path'] ?? $item['path'] ?? '');
        $thumbName   = MediaHelper::getThumbnailName($item['filename']);
        $thumbFull   = dirname($source) . '/' . $thumbName;
        $relativeDir = dirname($item['file_path'] ?? $item['path'] ?? '');
        $thumbRel    = $relativeDir . '/' . $thumbName;

        $result = MediaHelper::createThumbnail($source, $thumbFull);

        if (! $result['success']) {
            throw new \RuntimeException($result['message'] ?? 'Thumbnail failed.');
        }

        $this->mediaModel->update($mediaId, ['thumbnail_path' => $thumbRel]);

        return ['thumbnail_path' => $thumbRel];
    }

    protected function resize(int $mediaId, array $payload): array
    {
        $item = $this->mediaModel->find($mediaId);

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            throw new \RuntimeException('Image not found or not an image.');
        }

        $maxWidth  = (int) ($payload['width'] ?? 0);
        $maxHeight = (int) ($payload['height'] ?? 0);

        if ($maxWidth < 1 && $maxHeight < 1) {
            throw new \RuntimeException('No target dimensions provided.');
        }

        $source = FCPATH . ($item['file_path'] ?? $item['path'] ?? '');

        if (! ImageProcessor::resize($source, $source, $maxWidth ?: 99999, $maxHeight ?: 99999)) {
            throw new \RuntimeException('Resize failed.');
        }

        [$width, $height] = MediaHelper::getDimensions($source);
        $this->mediaModel->update($mediaId, ['width' => $width, 'height' => $height]);

        $this->enqueue('thumbnail', $mediaId);

        return ['width' => $width, 'height' => $height];
    }

    protected function makeDerivatives(int $mediaId): array
    {
        $item = $this->mediaModel->find($mediaId);

        if (! $item || ! MediaHelper::isImage($item['mime_type'] ?? '')) {
            throw new \RuntimeException('Image not found or not an image.');
        }

        $relative = ltrim($item['file_path'] ?? $item['path'] ?? '', '/');
        $source = FCPATH . $relative;
        if (! is_file($source)) {
            throw new \RuntimeException('Source image file does not exist.');
        }

        if (! \function_exists('imagecreatetruecolor')) {
            $derivatives = [
                'card' => $relative,
                'hero' => $relative,
                'og' => $relative,
            ];
            $this->mediaModel->update($mediaId, [
                'derivatives' => json_encode($derivatives, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return $derivatives + ['note' => 'GD extension is not available; original image paths were reused.'];
        }

        $pathInfo = pathinfo($source);
        $relativeDir = trim(dirname($relative), '.\\/');
        $base = $pathInfo['filename'];
        $ext = $pathInfo['extension'] ?? 'jpg';
        $targets = [
            'card' => [640, 420],
            'hero' => [1400, 900],
            'og' => [1200, 630],
        ];
        $derivatives = json_decode((string) ($item['derivatives'] ?? ''), true) ?: [];

        foreach ($targets as $variant => [$width, $height]) {
            $filename = "{$base}-{$variant}.{$ext}";
            $dest = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $filename;
            if (! is_file($dest) && ! ImageProcessor::resize($source, $dest, $width, $height)) {
                throw new \RuntimeException("Failed to create {$variant} derivative.");
            }
            $derivatives[$variant] = ($relativeDir !== '' ? $relativeDir . '/' : '') . $filename;
        }

        [$width, $height] = MediaHelper::getDimensions($source);
        $this->mediaModel->update($mediaId, [
            'width' => $width,
            'height' => $height,
            'derivatives' => json_encode($derivatives, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $derivatives;
    }
}