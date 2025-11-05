<?php

declare(strict_types=1);

namespace ImageResizer\Services;

use GdImage;
use ImageResizer\Exceptions\ImageProcessingException;

class ImageProcessor
{
    /**
     * @throws ImageProcessingException
     */
    public function process(
        string $imageData,
        string $imageType,
        ?int $width,
        ?int $height,
        int $quality,
        ?string $crop = null
    ): array {
        $image = @imagecreatefromstring($imageData);

        if ($image === false) {
            throw new ImageProcessingException('Failed to create image from data');
        }

        try {
            // Enable transparency for PNG
            if ($imageType === 'png') {
                $image = $this->enableTransparency($image);
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Handle missing dimensions
            [$width, $height] = $this->calculateDimensions(
                $width,
                $height,
                $originalWidth,
                $originalHeight
            );

            // Calculate aspect ratios
            $originalAspectRatio = $originalWidth / $originalHeight;
            $targetAspectRatio = $width / $height;

            // Crop if aspect ratios don't match
            if (abs($originalAspectRatio - $targetAspectRatio) > 0.01) {
                $image = $this->cropImage($image, $imageType, $originalWidth, $originalHeight, $targetAspectRatio, $crop);
            }

            // Resize the image
            $resizedImage = $this->resizeImage($image, $imageType, $width, $height);

            imagedestroy($image);

            return [$resizedImage, $width, $height];
        } catch (\Throwable $e) {
            if (isset($image) && $image instanceof GdImage) {
                imagedestroy($image);
            }
            throw new ImageProcessingException('Image processing failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function calculateDimensions(
        ?int $width,
        ?int $height,
        int $originalWidth,
        int $originalHeight
    ): array {
        // If both are null, use original dimensions
        if ($width === null && $height === null) {
            return [$originalWidth, $originalHeight];
        }

        $aspectRatio = $originalWidth / $originalHeight;

        // Calculate missing dimension
        if ($width === null) {
            $width = (int)round($aspectRatio * $height);
        }

        if ($height === null) {
            $height = (int)round($width / $aspectRatio);
        }

        return [$width, $height];
    }

    /**
     * @throws ImageProcessingException
     */
    private function cropImage(
        GdImage $image,
        string $imageType,
        int $originalWidth,
        int $originalHeight,
        float $targetAspectRatio,
        ?string $crop
    ): GdImage {
        // Calculate new dimensions after crop
        if ($originalWidth / $originalHeight > $targetAspectRatio) {
            $newWidth = (int)round($originalHeight * $targetAspectRatio);
            $newHeight = $originalHeight;
        } else {
            $newWidth = $originalWidth;
            $newHeight = (int)round($originalWidth / $targetAspectRatio);
        }

        [$cropX, $cropY] = $this->calculateCropPosition(
            $crop,
            $originalWidth,
            $originalHeight,
            $newWidth,
            $newHeight
        );

        if ($imageType === 'jpeg') {
            $cropped = @imagecrop($image, [
                'x' => $cropX,
                'y' => $cropY,
                'width' => $newWidth,
                'height' => $newHeight
            ]);

            if ($cropped === false) {
                throw new ImageProcessingException('Failed to crop JPEG image');
            }

            return $cropped;
        }

        // PNG with transparency
        $cropped = imagecreatetruecolor($newWidth, $newHeight);
        if ($cropped === false) {
            throw new ImageProcessingException('Failed to create cropped image canvas');
        }

        $cropped = $this->enableTransparency($cropped);
        imagecopy($cropped, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight);

        return $cropped;
    }

    private function calculateCropPosition(
        ?string $crop,
        int $originalWidth,
        int $originalHeight,
        int $newWidth,
        int $newHeight
    ): array {
        return match ($crop) {
            'topleft' => [0, 0],
            'topright' => [$originalWidth - $newWidth, 0],
            'bottomleft' => [0, $originalHeight - $newHeight],
            'bottomright' => [$originalWidth - $newWidth, $originalHeight - $newHeight],
            'bottomcentre' => [(int)(($originalWidth - $newWidth) / 2), $originalHeight - $newHeight],
            'topcentre' => [(int)(($originalWidth - $newWidth) / 2), 0],
            'centreleft' => [0, (int)(($originalHeight - $newHeight) / 2)],
            'centreright' => [$originalWidth - $newWidth, (int)(($originalHeight - $newHeight) / 2)],
            default => [(int)(($originalWidth - $newWidth) / 2), (int)(($originalHeight - $newHeight) / 2)], // centre
        };
    }

    /**
     * @throws ImageProcessingException
     */
    private function resizeImage(
        GdImage $image,
        string $imageType,
        int $width,
        int $height
    ): GdImage {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $resized = imagecreatetruecolor($width, $height);
        if ($resized === false) {
            throw new ImageProcessingException('Failed to create resized image canvas');
        }

        if ($imageType === 'png') {
            $resized = $this->enableTransparency($resized);
        }

        $success = imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $originalWidth,
            $originalHeight
        );

        if (!$success) {
            imagedestroy($resized);
            throw new ImageProcessingException('Failed to resize image');
        }

        return $resized;
    }

    private function enableTransparency(GdImage $image): GdImage
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefill($image, 0, 0, $transparent);
        }
        return $image;
    }

    /**
     * @throws ImageProcessingException
     */
    public function outputImage(GdImage $image, string $imageType, int $quality, ?string $filename = null): string
    {
        ob_start();

        try {
            $success = match ($imageType) {
                'jpeg' => imagejpeg($image, $filename, $quality),
                'png' => imagepng($image, $filename),
                default => throw new ImageProcessingException("Unsupported output type: {$imageType}"),
            };

            if (!$success) {
                ob_end_clean();
                throw new ImageProcessingException("Failed to output {$imageType} image");
            }

            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new ImageProcessingException('Failed to output image: ' . $e->getMessage(), 0, $e);
        }
    }
}
