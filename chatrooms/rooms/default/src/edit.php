<?php
/*
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['id'] = inputParam('id', 20);
$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['message'] = inputParam('message', 3000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken();

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);

if (!isAdmin()) {
  // 管理者でない場合は本人確認をする
  $characters = selectCharacterId($dbhCharacters, $inputParams['characterid']);
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

// 発言更新
updateChatlogsId($dbhChatlogs, $inputParams['id'], [
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
