<?php
/**
 * PWA Icon Generator for PASYA
 * 
 * Run this script from the command line to generate PWA icons from the PASYA logo.
 * Usage: php artisan pwa:generate-icons
 * 
 * Or run directly: php public/generate-icons.php
 */

$basePath = __DIR__;
$sourceLogo = $basePath . '/images/PASYA.png';
$iconsPath = $basePath . '/images/icons/';
$splashPath = $basePath . '/images/splash/';

$iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
$splashSizes = [
    ['width' => 640, 'height' => 1136],
    ['width' => 750, 'height' => 1334],
    ['width' => 1242, 'height' => 2208],
];

// Check if GD is available
if (!extension_loaded('gd')) {
    echo "GD extension not loaded. Creating SVG placeholders instead.\n";
    createSvgIcons($iconsPath, $iconSizes);
    exit;
}

// Check if source logo exists
if (!file_exists($sourceLogo)) {
    echo "Source logo not found at: $sourceLogo\n";
    echo "Creating SVG placeholder icons instead.\n";
    createSvgIcons($iconsPath, $iconSizes);
    exit;
}

// Load source image
$sourceImage = imagecreatefrompng($sourceLogo);
if (!$sourceImage) {
    echo "Could not load source image.\n";
    createSvgIcons($iconsPath, $iconSizes);
    exit;
}

$sourceWidth = imagesx($sourceImage);
$sourceHeight = imagesy($sourceImage);

echo "Generating PWA icons from PASYA logo...\n";

// Generate icons
foreach ($iconSizes as $size) {
    $icon = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($icon, false);
    imagesavealpha($icon, true);
    
    // Fill with green background
    $green = imagecolorallocate($icon, 22, 163, 74);
    imagefilledrectangle($icon, 0, 0, $size, $size, $green);
    
    // Calculate logo size (80% of icon)
    $logoSize = (int)($size * 0.7);
    $offset = (int)(($size - $logoSize) / 2);
    
    // Copy and resize logo
    imagecopyresampled(
        $icon, $sourceImage,
        $offset, $offset, 0, 0,
        $logoSize, $logoSize,
        $sourceWidth, $sourceHeight
    );
    
    $filename = $iconsPath . "icon-{$size}x{$size}.png";
    imagepng($icon, $filename);
    imagedestroy($icon);
    echo "Created: icon-{$size}x{$size}.png\n";
}

// Generate splash screens
echo "\nGenerating splash screens...\n";
foreach ($splashSizes as $splash) {
    $width = $splash['width'];
    $height = $splash['height'];
    
    $splashImage = imagecreatetruecolor($width, $height);
    
    // Fill with green gradient
    $green = imagecolorallocate($splashImage, 22, 163, 74);
    imagefilledrectangle($splashImage, 0, 0, $width, $height, $green);
    
    // Center logo
    $logoSize = min($width, $height) * 0.4;
    $offsetX = (int)(($width - $logoSize) / 2);
    $offsetY = (int)(($height - $logoSize) / 2) - 50;
    
    imagecopyresampled(
        $splashImage, $sourceImage,
        $offsetX, $offsetY, 0, 0,
        (int)$logoSize, (int)$logoSize,
        $sourceWidth, $sourceHeight
    );
    
    $filename = $splashPath . "splash-{$width}x{$height}.png";
    imagepng($splashImage, $filename);
    imagedestroy($splashImage);
    echo "Created: splash-{$width}x{$height}.png\n";
}

imagedestroy($sourceImage);
echo "\nDone! PWA icons generated successfully.\n";

function createSvgIcons($iconsPath, $sizes) {
    $svgTemplate = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{SIZE}" height="{SIZE}" viewBox="0 0 {SIZE} {SIZE}">
  <rect width="{SIZE}" height="{SIZE}" fill="#16a34a"/>
  <text x="50%" y="50%" text-anchor="middle" dy=".35em" font-family="Arial, sans-serif" font-size="{FONTSIZE}" font-weight="bold" fill="white">P</text>
</svg>
SVG;

    foreach ($sizes as $size) {
        $fontSize = (int)($size * 0.5);
        $svg = str_replace(['{SIZE}', '{FONTSIZE}'], [$size, $fontSize], $svgTemplate);
        
        $filename = $iconsPath . "icon-{$size}x{$size}.svg";
        file_put_contents($filename, $svg);
        echo "Created SVG: icon-{$size}x{$size}.svg\n";
    }
}
