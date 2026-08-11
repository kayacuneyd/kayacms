<?php
namespace Media\Libraries;

/**
 * GD-based image manipulation: crop, rotate, resize.
 */
class ImageProcessor
{
    /**
     * Load an image from a path into a GD resource.
     *
     * @return resource|null
     */
    public static function load(string $path)
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG     => imagecreatefromjpeg($path),
            IMAGETYPE_PNG      => imagecreatefrompng($path),
            IMAGETYPE_GIF      => imagecreatefromgif($path),
            IMAGETYPE_WEBP     => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default            => null,
        };
    }

    /**
     * Save a GD image back to disk preserving original format.
     */
    public static function save($image, string $path, int $quality = 85): bool
    {
        $info = @getimagesize($path);
        $type = $info[2] ?? IMAGETYPE_JPEG;
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        ob_start();
        switch ($type) {
            case IMAGETYPE_PNG:
                $saved = imagepng($image, $path);
                break;
            case IMAGETYPE_GIF:
                $saved = imagegif($image, $path);
                break;
            case IMAGETYPE_WEBP:
                $saved = function_exists('imagewebp')
                    ? imagewebp($image, $path, $quality)
                    : false;
                break;
            case IMAGETYPE_JPEG:
            default:
                $saved = imagejpeg($image, $path, $quality);
                break;
        }
        ob_end_clean();

        return $saved !== false;
    }

    /**
     * Resize image while preserving aspect ratio.
     */
    public static function resize(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality = 85): bool
    {
        [$width, $height] = getimagesize($source);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return copy($source, $destination);
        }

        $ratio     = min($maxWidth / $width, $maxHeight / $height);
        $newWidth  = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $src  = self::load($source);
        $dst  = imagecreatetruecolor($newWidth, $newHeight);

        if (! $src || ! $dst) {
            return false;
        }

        self::preserveAlpha($src, $dst);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $saved = self::save($dst, $destination, $quality);

        imagedestroy($src);
        imagedestroy($dst);

        return $saved;
    }

    /**
     * Crop image to exact dimensions.
     *
     * @param int $x Left offset in source pixels
     * @param int $y Top offset in source pixels
     */
    public static function crop(string $source, string $destination, int $cropWidth, int $cropHeight, int $x = 0, int $y = 0, int $quality = 85): bool
    {
        [$width, $height] = getimagesize($source);

        $cropWidth  = min($cropWidth, $width);
        $cropHeight = min($cropHeight, $height);
        $x          = max(0, min($x, $width - $cropWidth));
        $y          = max(0, min($y, $height - $cropHeight));

        $image = self::load($source);
        $crop  = imagecreatetruecolor($cropWidth, $cropHeight);

        if (! $image || ! $crop) {
            return false;
        }

        self::preserveAlpha($image, $crop);

        imagecopy($crop, $image, 0, 0, $x, $y, $cropWidth, $cropHeight);
        $saved = self::save($crop, $destination, $quality);

        imagedestroy($image);
        imagedestroy($crop);

        return $saved;
    }

    /**
     * Rotate image by degrees (clockwise).
     */
    public static function rotate(string $source, string $destination, float $degrees, int $quality = 85): bool
    {
        $image = self::load($source);

        if (! $image) {
            return false;
        }

        $rotated = imagerotate($image, -$degrees, 0);

        if ($rotated === false) {
            imagedestroy($image);
            return false;
        }

        $saved = self::save($rotated, $destination, $quality);

        imagedestroy($image);
        imagedestroy($rotated);

        return $saved;
    }

    /**
     * Create a square thumbnail preview.
     */
    public static function thumbnail(string $source, string $destination, int $size = 300, int $quality = 85): bool
    {
        [$width, $height] = getimagesize($source);

        $image = self::load($source);

        if (! $image) {
            return false;
        }

        $srcX = 0;
        $srcY = 0;

        if ($width > $height) {
            $srcX = (int) (($width - $height) / 2);
            $width = $height;
        } else {
            $srcY = (int) (($height - $width) / 2);
            $height = $width;
        }

        $thumb = imagecreatetruecolor($size, $size);

        self::preserveAlpha($image, $thumb);

        imagecopyresampled($thumb, $image, 0, 0, $srcX, $srcY, $size, $size, $width, $height);
        $saved = self::save($thumb, $destination, $quality);

        imagedestroy($image);
        imagedestroy($thumb);

        return $saved;
    }

    private static function preserveAlpha($src, $dst): void
    {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefill($dst, 0, 0, $transparent);
    }
}