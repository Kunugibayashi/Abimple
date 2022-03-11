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
$inputParams['dice'] = inputParam('dice', 6);

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
$dbhChatrooms  = connectRo(CHAT_ROOM_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);

$chatrooms = selectChatroomConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

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

$myChatentries = selectChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (!usedArr($myChatentries)) {
  // 不正アクセス
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '入室していません。';
  goto outputPage;
}
$myChatentry = $myChatentries[0];

// ダイス
if (!usedStr($inputParams['dice'])) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ダイス目が入力されていません。';
  goto outputPage;
}
preg_match('/^([0-9]{1,2})d([0-9]{1,3})$/i', $inputParams['dice'], $matches);
if (count($matches) != 3) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ダイス目の入力値が形式にあっていません。';
  goto outputPage;
}
$diceMen = min($matches[2], 100);
$diceNum = min($matches[1], 10);

$result = array();
$sum = 0;
for ($i = 0; $i < $diceNum; $i++) {
  $me = mt_rand(1, $diceMen);
  $result[] = $me;
  $sum = $sum + $me;
}

// 発言
$result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
  'entrykey' => $myChatentry['entrykey'],
  'characterid' => $character['id'],
  'fullname' => CHAT_LOG_SYSTEM_NAME,
  'color' => $chatroom['color'],
  'bgcolor' => $chatroom['bgcolor'],
  'message' => ('<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
               .'<span class="dice">（' .$diceNum .'d' .$diceMen .'）＞ ' .$sum .'[' .implode(',', $result) .'] ＞ ' .$sum .'</span>'
  ),
]);
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ダイスに失敗しました。もう一度お試しください。';
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
