<?php

declare(strict_types=1);

$source = $argv[1] ?? '';
$output = $argv[2] ?? '';

if ($source === '' || $output === '') {
    fwrite(STDERR, "Usage: php process-horizontal-logo.php <source> <output>\n");
    exit(1);
}

$image = imagecreatefrompng($source);
if ($image === false) {
    fwrite(STDERR, "Failed to load image.\n");
    exit(1);
}

$width = imagesx($image);
$height = imagesy($image);
$processed = imagecreatetruecolor($width, $height);

imagealphablending($processed, false);
imagesavealpha($processed, true);

$transparent = imagecolorallocatealpha($processed, 0, 0, 0, 127);
imagefill($processed, 0, 0, $transparent);

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgba = imagecolorat($image, $x, $y);
        $red = ($rgba >> 16) & 0xFF;
        $green = ($rgba >> 8) & 0xFF;
        $blue = $rgba & 0xFF;

        if ($red <= 28 && $green <= 28 && $blue <= 28) {
            imagesetpixel($processed, $x, $y, $transparent);
            continue;
        }

        $color = imagecolorallocatealpha($processed, $red, $green, $blue, 0);
        imagesetpixel($processed, $x, $y, $color);
    }
}

imagepng($processed, $output);
imagedestroy($image);
imagedestroy($processed);

echo "Saved transparent logo to {$output}\n";
