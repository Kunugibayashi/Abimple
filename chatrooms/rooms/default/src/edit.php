<?php
/*
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['id'] = inputParam('id', 20);
$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['message'] = inputParam('message', 3000);

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));
sessionLogChatEntryCharacter($inputParams['characterid']);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken($inputParams['characterid']);

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatlogs = connectRw(__DIR__ .'/' .CHAT_LOGS_DB);

if (!isAdmin()) {
  // 管理者でない場合は本人確認をする
  $characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
  if (!usedArr($characters)) {
    // 不正アクセス
    $jsonArray['code'] = 1;
    $jsonArray['errorMessage'] = '名簿が存在しません。';
    goto outputPage;
  }
  $character = $characters[0];

  // 本人確認
  identityUser($character['userid'], $character['username']);
}

if (!usedStr($inputParams['id'])) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'idが存在しません。';
  goto outputPage;
}

if (!usedStr($inputParams['message'])) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '発言が存在しません。';
  goto outputPage;
}

// HTML構文チェック
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$result = $doc->loadXML('<div>' . html_entity_decode($inputParams['message']) .'</div>');
libxml_clear_errors();
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '発言内のHTMLタグが正しくありません。';
  goto outputPage;
}

// 発言更新
updateChatlogs($dbhChatlogs, $inputParams['id'], [
  'message' => $inputParams['message'],
]);

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';

/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:

$json =  json_encode($jsonArray);
setJsonHeader();
echo($json);
