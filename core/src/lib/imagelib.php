<?php
require_once(__DIR__ . '/../logger.php');

/**
 * 画像ファイルをアップロードして指定ディレクトリへ保存する
 * $file       $_FILES の対象要素
 * $dirpath    保存先ディレクトリの物理パス
 * $baseName   保存ファイル名のベース
 * $maxsize    許可する最大ファイルサイズ（バイト単位）
 * * 返却値    array: [code (0:成功, 1:失敗), errorMessage (文字列), fileName (文字列)]
 */
function uploadImageFile(array $file, string $dirpath, string $baseName, int $maxsize = 2097152): array
{
  $result = [
    'code' => 0,
    'errorMessage' => '',
    'fileName' => ''
  ];

  if (empty($file) || !isset($file['error'])) {
    logDebug('File array is empty or invalid structure.');
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイルが正しくアップロードされませんでした。';
    return $result;
  }

  if ($file['error'] === UPLOAD_ERR_NO_FILE) {
    logDebug('No file was uploaded.');
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイルが選択されていません。';
    return $result;
  }

  if ($file['error'] !== UPLOAD_ERR_OK) {
    logError('Upload error code: ' . $file['error']);
    $result['code'] = 1;
    $result['errorMessage'] = 'アップロード中にエラーが発生しました。エラーコード: ' . $file['error'];
    return $result;
  }

  if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    logError('Invalid uploaded file path.');
    $result['code'] = 1;
    $result['errorMessage'] = '一時ファイルの取得に失敗しました。';
    return $result;
  }

  $fileSize = (int)($file['size'] ?? 0);
  if ($fileSize <= 0 || $fileSize > $maxsize) {
    logDebug('File size exceeds limit: ' . $fileSize);
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイルサイズが制限を超えています。';
    return $result;
  }

  // --- シグネチャ判定 ---
  $fp = fopen($file['tmp_name'], 'rb');
  if ($fp === false) {
    logError('Failed to open temp file.');
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイルの読み込みに失敗しました。';
    return $result;
  }

  $head = fread($fp, 16);
  fclose($fp);

  if ($head === false || strlen($head) < 4) {
    logError('Failed to read file header.');
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイル形式を特定できませんでした。';
    return $result;
  }

  $extension = '';

  if (strncmp($head, "\xFF\xD8\xFF", 3) === 0) {
    $extension = 'jpg';
  }
  elseif (strncmp($head, "\x89PNG\x0D\x0A\x1A\x0A", 8) === 0) {
    $extension = 'png';
  }
  elseif (strncmp($head, "GIF87a", 6) === 0 || strncmp($head, "GIF89a", 6) === 0) {
    $extension = 'gif';
  }
  elseif (
    strncmp($head, "RIFF", 4) === 0 &&
    substr($head, 8, 4) === "WEBP"
  ) {
    $extension = 'webp';
  }
  else {
    logDebug('Unsupported file signature.');
    $result['code'] = 1;
    $result['errorMessage'] = '許可されていないファイル形式です。';
    return $result;
  }

  // ファイル名サニタイズ
  $baseName = basename($baseName);
  $baseName = pathinfo($baseName, PATHINFO_FILENAME);
  $safeBaseName = preg_replace('/[^\p{L}\p{N}._-]/u', '', $baseName);
  $safeBaseName = trim((string)$safeBaseName, ". \t\n\r\0\x0B");

  if ($safeBaseName === '') {
    logError('Base filename is invalid after sanitization.');
    $result['code'] = 1;
    $result['errorMessage'] = '保存するファイル名が正しくありません。';
    return $result;
  }

  $finalFileName = $safeBaseName . '.' . $extension;

  if (!is_dir($dirpath) || !is_writable($dirpath)) {
    logError('Directory not found or not writable: ' . $dirpath);
    $result['code'] = 1;
    $result['errorMessage'] = '保存先ディレクトリに書き込み権限がありません。';
    return $result;
  }

  $savePath = rtrim($dirpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $finalFileName;

  if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    logError('Failed to move uploaded file to: ' . $savePath);
    $result['code'] = 1;
    $result['errorMessage'] = 'ファイルの保存に失敗しました。';
    return $result;
  }

  // 成功時
  $result['fileName'] = $finalFileName;
  return $result;
}

/**
 * 画像ファイルを削除する
 * $dirpath   保存先ディレクトリの物理パス
 * $fileName  削除対象のファイル名
 * 返却値    array: [code (0:成功, 1:失敗), errorMessage (文字列)]
 */
function deleteImageFile(string $dirpath, string $fileName): array
{
  $result = [
    'code' => 0,
    'errorMessage' => ''
  ];

  if (!usedStr($dirpath)) {
    logError('Directory path is empty.');
    $result['code'] = 1;
    $result['errorMessage'] = '保存先ディレクトリが指定されていません。';
    return $result;
  }

  if (!usedStr($fileName)) {
    logDebug('Filename is empty.');
    $result['code'] = 1;
    $result['errorMessage'] = '削除するファイル名が指定されていません。';
    return $result;
  }

  $safeFileName = basename($fileName);
  if ($safeFileName !== $fileName) {
    logError('Invalid filename path traversal detected: ' . $fileName);
    $result['code'] = 1;
    $result['errorMessage'] = '削除するファイル名が不正です。';
    return $result;
  }

  if (!preg_match('/^[a-z0-9._-]+\.(jpg|jpeg|png|webp|gif)$/i', $safeFileName)) {
    logError('Invalid image filename format: ' . $safeFileName);
    $result['code'] = 1;
    $result['errorMessage'] = '削除するファイル名の形式が不正です。';
    return $result;
  }

  $realDirPath = realpath($dirpath);
  if ($realDirPath === false || !is_dir($realDirPath)) {
    logError('Directory not found: ' . $dirpath);
    $result['code'] = 1;
    $result['errorMessage'] = '保存先ディレクトリが存在しません。';
    return $result;
  }

  $filePath = rtrim($realDirPath, "/\\") . DIRECTORY_SEPARATOR . $safeFileName;

  if (!file_exists($filePath)) {
    logDebug('File does not exist: ' . $filePath);
    return $result;
  }

  if (!is_file($filePath)) {
    logError('Target is not a file: ' . $filePath);
    $result['code'] = 1;
    $result['errorMessage'] = '削除対象がファイルではありません。';
    return $result;
  }

  if (!unlink($filePath)) {
    logError('Failed to delete file: ' . $filePath);
    $result['code'] = 1;
    $result['errorMessage'] = '画像ファイルの削除に失敗しました。';
    return $result;
  }

  return $result;
}
