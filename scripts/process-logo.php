<?php

declare(strict_types=1);

$source = $argv[1] ?? '';
$outputDir = $argv[2] ?? '';

if ($source === '' || $outputDir === '') {
    fwrite(STDERR, "Usage: php process-logo.php <source-image> <output-dir>\n");
    exit(1);
}

if (! is_file($source)) {
    fwrite(STDERR, "Source image not found: {$source}\n");
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "Unable to create output directory: {$outputDir}\n");
    exit(1);
}

$info = getimagesize($source);
if ($info === false) {
    fwrite(STDERR, "Unable to read image metadata.\n");
    exit(1);
}

$image = match ($info[2]) {
    IMAGETYPE_PNG => imagecreatefrompng($source),
    IMAGETYPE_JPEG => imagecreatefromjpeg($source),
    IMAGETYPE_WEBP => imagecreatefromwebp($source),
    default => false,
};

if ($image === false) {
    fwrite(STDERR, "Unsupported image type.\n");
    exit(1);
}

$width = imagesx($image);
$height = imagesy($image);

$minX = $width;
$minY = $height;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgba = imagecolorat($image, $x, $y);
        $red = ($rgba >> 16) & 0xFF;
        $green = ($rgba >> 8) & 0xFF;
        $blue = $rgba & 0xFF;
        $alpha = ($rgba & 0x7F000000) >> 24;

        if ($alpha >= 120) {
            continue;
        }

        if ($red > 245 && $green > 245 && $blue > 245) {
            continue;
        }

        $minX = min($minX, $x);
        $minY = min($minY, $y);
        $maxX = max($maxX, $x);
        $maxY = max($maxY, $y);
    }
}

if ($maxX <= $minX || $maxY <= $minY) {
    fwrite(STDERR, "Unable to detect logo bounds.\n");
    exit(1);
}

$padding = 4;
$minX = max(0, $minX - $padding);
$minY = max(0, $minY - $padding);
$maxX = min($width - 1, $maxX + $padding);
$maxY = min($height - 1, $maxY + $padding);

$trimWidth = $maxX - $minX + 1;
$trimHeight = $maxY - $minY + 1;

$trimmed = imagecreatetruecolor($trimWidth, $trimHeight);
imagealphablending($trimmed, false);
imagesavealpha($trimmed, true);

$transparent = imagecolorallocatealpha($trimmed, 0, 0, 0, 127);
imagefill($trimmed, 0, 0, $transparent);

imagecopy($trimmed, $image, 0, 0, $minX, $minY, $trimWidth, $trimHeight);

$iconSize = min($trimWidth, $trimHeight);
$iconX = (int) floor(($trimWidth - $iconSize) / 2);
$iconY = (int) floor(($trimHeight - $iconSize) / 2);

$icon = imagecreatetruecolor($iconSize, $iconSize);
imagealphablending($icon, false);
imagesavealpha($icon, true);
imagefill($icon, 0, 0, $transparent);
imagecopy($icon, $trimmed, 0, 0, $iconX, $iconY, $iconSize, $iconSize);

$fullPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ksrelief-logo.png';
$iconPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ksrelief-logo-icon.png';
$faviconPath = dirname($outputDir) . DIRECTORY_SEPARATOR . 'favicon.png';

imagepng($trimmed, $fullPath);
imagepng($icon, $iconPath);
imagepng($icon, $faviconPath);

imagedestroy($image);
imagedestroy($trimmed);
imagedestroy($icon);

echo "Saved:\n- {$fullPath} ({$trimWidth}x{$trimHeight})\n- {$iconPath} ({$iconSize}x{$iconSize})\n- {$faviconPath}\n";
