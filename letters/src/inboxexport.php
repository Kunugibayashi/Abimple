<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

require_once(__DIR__ . '/../../core/src/lib/exportlib.php');
require_once(__DIR__ . '/../../core/src/lib/templatelib.php');

loginOnly();

$success = '';
$errors = array();

// DB接続
$dbhInbox = connectRw(INBOX_LETTERS_DB);

// index 用データの取得
$inbox = selectInboxLettersMy($dbhInbox, getUserid(), getUsername());
$inboxIds = array_column($inbox, 'id');

// DL名に使用。使用不可文字が混じらないように変換。
$userName = getUsername();
$userName = removeUnsafeChars($userName);

// CSS コピー
foreach (SITE_CSS_FILES_PATH as $css) {
  if (is_file($css)) {
    copy($css, LETTER_INBOX_HTML_PATH .'css/' .basename($css));
  }
}

$indexFileName = $userName .'_index.html';
$indexOutPath = LETTER_INBOX_HTML_PATH  .$indexFileName;

$inboxFileName = $userName .'_inbox.html';
$letterOutPath = LETTER_INBOX_HTML_PATH  .$inboxFileName;

// indexファイル作成
$indexTplVars = [
  'title' => '受信BOX',
  'siteTemplate' => SITE_TEMPLATE,
  'updateDate' => nowYmdhi(),
  'inbox' => $inbox,
  'userName' => $userName,
  'indexFileName' => $indexFileName,
  'inboxFileName' => $inboxFileName,
];

$indexHtmlString = renderTemplateBuffer(
  LETTER_TEMPLATE_FILE_PATH .'letterindex.tpl.php',
  $indexTplVars,
);
file_put_contents($indexOutPath, $indexHtmlString, LOCK_EX);

// メッセージファイルの作成
$headerHtmlString = renderTemplateBuffer(
  LETTER_TEMPLATE_FILE_PATH .'letterheader.tpl.php',
  $indexTplVars, // 共通
);
file_put_contents($letterOutPath, $headerHtmlString, LOCK_EX);

foreach ($inboxIds as $key => $id) {
  $inboxDetail = selectInboxLettersId($dbhInbox, $id);
  if (!usedArr($inboxDetail)) {
    continue;
  }
  if (!usedArr($inboxDetail[0])) {
    continue;
  }
  $messageTplVars = [
    'id' => $inboxDetail[0]['id'] ?? '',
    'tofullname' => $inboxDetail[0]['tofullname'] ?? '',
    'fromfullname' => $inboxDetail[0]['fromfullname'] ?? '',
    'modified' => $inboxDetail[0]['modified'] ?? '',
    'title' => $inboxDetail[0]['title'] ?? '',
    'message' => $inboxDetail[0]['message'] ?? '',
    'indexFileName' => $indexFileName,
    'inboxFileName' => $inboxFileName,
  ];
  $messageHtmlString = renderTemplateBuffer(
    LETTER_TEMPLATE_FILE_PATH .'lettermessage.tpl.php',
    $messageTplVars,
  );
  file_put_contents($letterOutPath, $messageHtmlString, FILE_APPEND | LOCK_EX);
}

$footerHtmlString = renderTemplateBuffer(
  LETTER_TEMPLATE_FILE_PATH .'letterfooter.tpl.php',
  $indexTplVars, // 共通
);
file_put_contents($letterOutPath, $footerHtmlString, FILE_APPEND | LOCK_EX);

// zip ファイル変換
$zip = new ZipArchive();
$zipName = $userName .'_InboxData.zip';
$zipFilePath = LETTER_INBOX_ZIP_PATH .'' .$zipName;
$opened = false;

try {
  $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
  if ($result !== true) {
    echo 'ZIPファイルを作成できません。';
    exit;
  }
  $opened = true;
  addDirToZip($zip, LETTER_INBOX_HTML_PATH, LETTER_INBOX_HTML_PATH);
} finally {
  if ($opened) {
    $zip->close();
  }
}

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
$handle = fopen($zipFilePath, 'rb');
if ($handle) {
  while (!feof($handle)) {
    echo fread($handle, 1024 * 1024);
    flush();
  }
  fclose($handle);

  // ファイルを削除
  if (file_exists($zipFilePath)) {
    unlink($zipFilePath);
  }
}

exit;
