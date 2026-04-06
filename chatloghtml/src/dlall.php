<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

loginOnly();

ini_set('memory_limit', PHP_MEMORY_LIMIT);

// zipファイル出力時にも使用
$indexFile = CHAT_LOG_HTML_PATH.'index.html';

// ローカルIndexファイル出力
$createLocalIndex = function() use ($indexFile) {
  // DBスコープが上書きされてしまうため無名関数使用
  ob_start();
  include(CHAT_LOG_SRC_PATH .'localindex.php');
  $buffer = ob_get_contents();
  ob_end_clean();

  file_put_contents($indexFile, $buffer, LOCK_EX);
};
$createLocalIndex();

// zipファイル作成
$zip = new ZipArchive();
$zipName = 'SiteLogData.zip';
$zipFilePath = CHAT_LOG_ZIP_PATH .$zipName;

if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
  echo 'ZIPファイルを作成できません。';
  exit;
}

// indexファイルは常に最新にする
$indexFileName = basename($indexFile);
$zip->addFile($indexFile, $indexFileName);

// ログファイル取得
$logFiles = glob(CHAT_LOG_HTML_PATH.'*.html');

// ログファイルをzipファイルに格納
foreach ($logFiles as $logFile) {
  $logFileName = basename($logFile);

  // zipファイル内に存在する場合は格納しない
  if ($zip->locateName($logFileName) !== false) {
    continue;
  }

  $zip->addFile($logFile, $logFileName);
}

$zip->close();

// DL名。使用不可文字が混じらないように変換。
$siteTitle = removeUnsafeChars(SITE_TITLE);
$downloadName = nowYmdhi() .'_' .$siteTitle .'_' .$zipName;

// zipファイルをダウンロード用に出力
header('Content-Type: application/zip');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' .filesize($zipFilePath));
header(
  'Content-Disposition: attachment; ' .
  'filename="' .$zipName .'"; ' .
  "filename*=UTF-8''" .rawurlencode($downloadName)
);
header('Connection: close');

// バイナリ出力前に出力バッファを全クリア
while (ob_get_level()) { ob_end_clean(); }

// ファイルの内容を出力
readfile($zipFilePath);

exit;
