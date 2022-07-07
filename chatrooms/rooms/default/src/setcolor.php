<?php
/* ログに発言を出力する。
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$jsonArray = array();
$inputParams = array();

$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['color'] = inputParam('color', 7);
$inputParams['bgcolor'] = inputParam('bgcolor', 7);

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';

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
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);

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

$myChatentries = selectEqualChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (!usedArr($myChatentries)) {
  // 不正アクセス
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '入室していません。';
  goto outputPage;
}
$myChatentry = $myChatentries[0];

// 色更新
$result = updateChatentries($dbhChatentries, $inputParams['characterid'], [
  'color' => $inputParams['color'],
  'bgcolor' => $inputParams['bgcolor'],
]);
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '色の更新に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';

/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:

$json =  json_encode($jsonArray);
setJsonHeader();
echo($json);
