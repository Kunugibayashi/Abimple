<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

ini_set('memory_limit', PHP_MEMORY_LIMIT);

// zipファイル出力時にも使用
$indexFile = ALL_LOG_FILE_DIR.'index.html';

// ローカルIndexファイル出力
$createLocalIndex = function() use ($indexFile) {
  // DBスコープが上書きされてしまうため無名関数使用
  ob_start();
  include('./localindex.php');
  $buffer = ob_get_contents();
  ob_end_clean();

  file_put_contents($indexFile, $buffer, LOCK_EX);
};
$createLocalIndex();

// zipファイル作成
$zip = new ZipArchive();
$allLogZipFile = ALL_LOG_ZIP_DIR.DL_ALL_LOG_ZIP_NAME;

if ($zip->open($allLogZipFile, ZipArchive::CREATE) !== TRUE) {
  echo 'ZIPファイルを作成できません。';
  exit;
}

// indexファイルは常に最新にする
$indexFileName = basename($indexFile);
$zip->addFile($indexFile, $indexFileName);

// ログファイル取得
$logFiles = glob(ALL_LOG_FILE_DIR.'*.html');

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

// zipファイルをダウンロード用に出力
header('Content-Type: application/zip');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' .filesize($allLogZipFile));
header('Content-Disposition: attachment; filename="' .basename($allLogZipFile) .'"');
header('Connection: close');

// 出力バッファリングを無効化
while (ob_get_level()) { ob_end_clean(); }

// ファイルの内容を出力
readfile($allLogZipFile);

exit;
