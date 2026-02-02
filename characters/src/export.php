<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

require_once('../../core/src/lib/exportlib.php');
require_once('../../core/src/lib/templatelib.php');

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
    TEMPLATE_FILE_PATH .'characterexport.tpl.php',
    $tplVars,
  );

  $outPath = CHARACTER_STORAGE_DIR  .$targetCharacter['id'] .'.html';
  file_put_contents($outPath, $htmlString, LOCK_EX);
}

// CSS コピー
foreach (SITE_CSS_FILES as $css) {
  if (is_file($css)) {
    copy($css, CHARACTER_STORAGE_DIR .'css/' .basename($css));
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
  TEMPLATE_FILE_PATH .'characterindex.tpl.php',
  $indexTplVars,
);

$indexOutPath = CHARACTER_STORAGE_DIR  .'index.html';
file_put_contents($indexOutPath, $indexHtmlString, LOCK_EX);

// zip ファイル変換
$zipPath = CHARACTER_ZIP_DIR . '/Character.zip';
$zip = new ZipArchive();
$opened = false;

try {
  $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
  if ($result !== true) {
    throw new RuntimeException('ZIP open failed: ' . $result);
  }
  $opened = true;
  addDirToZip($zip, CHARACTER_STORAGE_DIR, CHARACTER_STORAGE_DIR);
} finally {
  if ($opened) {
    $zip->close();
  }
}

// DLとして返却
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="Character.zip"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);
exit;
