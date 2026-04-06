<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');
require_once(__DIR__ . '/../../core/src/logger.php');

// ファイル名取得（GET）
$inputParams['filename'] = inputParam('f', 40);

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));

$filename = $inputParams['filename'];

// ファイル名チェック
if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp|gif)$/', $filename)) {
  http_response_code(404);
  exit;
}

// フルパス
$filepath = CHARACTER_IMAGE_PATH .$filename;

// 存在確認
if (!is_file($filepath)) {
  http_response_code(404);
  exit;
}

// 拡張子からMIME決定
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

$mime = match ($ext) {
  'jpg', 'jpeg' => 'image/jpeg',
  'png' => 'image/png',
  'webp' => 'image/webp',
  'gif' => 'image/gif',
  default => 'application/octet-stream'
};

// 出力
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');
header('X-Robots-Tag: noindex, nofollow, noimageindex, nosnippet');

readfile($filepath);
exit;
