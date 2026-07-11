<?php
/**
 * thumb.php — Thumbnail generator with cache
 * Usage: /thumb.php?src=uploads/photos/xxx.jpg&s=80
 *
 * วางไฟล์นี้ที่ root ของ project (เดียวกับ index.php)
 */

// ── Config ────────────────────────────────────────────────────────────────
define('THUMB_CACHE_DIR', __DIR__ . '/uploads/thumbs/');  // cache folder
define('THUMB_MAX_SIZE',  400);                            // ขนาดสูงสุดที่อนุญาต
define('THUMB_DEFAULT',   80);                             // ขนาด default
define('THUMB_QUALITY',   82);                             // JPEG quality 0-100

// ── Validate input ────────────────────────────────────────────────────────
$src  = isset($_GET['src'])  ? trim($_GET['src'])    : '';
$size = isset($_GET['s'])    ? (int)$_GET['s']       : THUMB_DEFAULT;

// ป้องกัน path traversal
$src = str_replace(array('..', '\\', "\0"), '', $src);
$src = ltrim($src, '/');

if (empty($src) || $size < 10 || $size > THUMB_MAX_SIZE) {
    http_response_code(400);
    exit('Invalid parameters');
}

$file_path = __DIR__ . '/' . $src;

// ── ตรวจไฟล์ต้นทาง ───────────────────────────────────────────────────────
if (!file_exists($file_path) || !is_file($file_path)) {
    http_response_code(404);
    exit('File not found');
}

// ── ตรวจ MIME type ────────────────────────────────────────────────────────
$allowed_types = array(
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
);
$mime = mime_content_type($file_path);
if (!array_key_exists($mime, $allowed_types)) {
    http_response_code(415);
    exit('Unsupported image type');
}

// ── Cache path ────────────────────────────────────────────────────────────
if (!is_dir(THUMB_CACHE_DIR)) {
    mkdir(THUMB_CACHE_DIR, 0755, true);
}

$cache_key  = md5($src . '_' . $size . '_' . filemtime($file_path));
$cache_path = THUMB_CACHE_DIR . $cache_key . '.jpg';

// ── ถ้ามี cache → ส่งทันที ───────────────────────────────────────────────
if (file_exists($cache_path)) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=2592000'); // 30 วัน
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($cache_path)) . ' GMT');
    header('ETag: "' . $cache_key . '"');

    // 304 Not Modified
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $cache_key . '"') {
        http_response_code(304);
        exit;
    }

    readfile($cache_path);
    exit;
}

// ── โหลดรูปต้นทาง ────────────────────────────────────────────────────────
switch ($mime) {
    case 'image/jpeg': $img = @imagecreatefromjpeg($file_path); break;
    case 'image/png':  $img = @imagecreatefrompng($file_path);  break;
    case 'image/webp': $img = @imagecreatefromwebp($file_path); break;
    case 'image/gif':  $img = @imagecreatefromgif($file_path);  break;
    default: $img = false;
}

if (!$img) {
    // GD โหลดไม่ได้ → ส่งไฟล์เดิม
    header('Content-Type: ' . $mime);
    readfile($file_path);
    exit;
}

// ── คำนวณขนาดใหม่ (crop เป็นสี่เหลี่ยมจัตุรัส) ─────────────────────────
$orig_w = imagesx($img);
$orig_h = imagesy($img);

// crop จากตรงกลาง
$min_dim = min($orig_w, $orig_h);
$src_x   = (int)(($orig_w - $min_dim) / 2);
$src_y   = (int)(($orig_h - $min_dim) / 2);

// สร้าง canvas ใหม่
$thumb = imagecreatetruecolor($size, $size);

// รองรับ PNG transparency
if (in_array($mime, array('image/png', 'image/gif'))) {
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
    imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
}

imagecopyresampled($thumb, $img, 0, 0, $src_x, $src_y, $size, $size, $min_dim, $min_dim);
imagedestroy($img);

// ── Save cache ────────────────────────────────────────────────────────────
imagejpeg($thumb, $cache_path, THUMB_QUALITY);
imagedestroy($thumb);

// ── ส่ง response ─────────────────────────────────────────────────────────
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=2592000');
header('ETag: "' . $cache_key . '"');
readfile($cache_path);
