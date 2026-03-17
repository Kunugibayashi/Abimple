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
$dbhCharacters = connectRw(CHARACTERS_DB);

// 名簿の id を全取得
$tmpCharacterIds = getAllCharacterIds($dbhCharacters);
$characterIds = array_column($tmpCharacterIds, 'id');

$nameListColumns = buildNameListColumns();

// index 用データの取得
$characters = selectLikeCharactersList($dbhCharacters);

// キャラクターごとにファイル出力
foreach ($characterIds as $key => $characterId) {
  $targetCharacters = selectCharactersId($dbhCharacters, $characterId);

  if (!usedArr($targetCharacters)) {
    continue;
  }
  $targetCharacter = $targetCharacters['0'] ?? [];

  $tplVars = [
    'siteTemplate' => SITE_TEMPLATE,
    'updateDate' => nowYmdhi(),
    'columns' => $nameListColumns,
    'character' => $targetCharacter,
  ];

  $htmlString = renderTemplateBuffer(
    CHARACTER_TEMPLATE_FILE_PATH .'characterexport.tpl.php',
    $tplVars,
  );

  $outPath = CHARACTER_HTML_PATH  .$targetCharacter['id'] .'.html';
  file_put_contents($outPath, $htmlString, LOCK_EX);
}

// CSS コピー
foreach (SITE_CSS_FILES_PATH as $css) {
  if (is_file($css)) {
    copy($css, CHARACTER_HTML_PATH .'css/' .basename($css));
  }
}

// indexファイル作成
$indexTplVars = [
  'siteTemplate' => SITE_TEMPLATE,
  'updateDate' => nowYmdhi(),
  'columns' => $nameListColumns,
  'characters' => $characters,
];

$indexHtmlString = renderTemplateBuffer(
  CHARACTER_TEMPLATE_FILE_PATH .'characterindex.tpl.php',
  $indexTplVars,
);

$indexOutPath = CHARACTER_HTML_PATH  .'index.html';
file_put_contents($indexOutPath, $indexHtmlString, LOCK_EX);

// zip ファイル変換
$zip = new ZipArchive();
$zipName = 'CharacterData.zip';
$zipFilePath = CHARACTER_ZIP_PATH .'' .$zipName;
$opened = false;

try {
  $result = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
  if ($result !== true) {
    echo 'ZIPファイルを作成できません。';
    exit;
  }
  $opened = true;
  addDirToZip($zip, CHARACTER_HTML_PATH, CHARACTER_HTML_PATH);
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
readfile($zipFilePath);

exit;
