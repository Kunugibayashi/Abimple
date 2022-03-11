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
$inputParams['omikujiid'] = inputParam('omikujiid', 4);

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

// おみくじ内容
if ($inputParams['omikujiid'] === OMIKUJI1_ID) {
  $omiName = $chatroom['omi1name'];
  $omiText = $chatroom['omi1text'];
} else if ($inputParams['omikujiid'] === OMIKUJI2_ID) {
  $omiName = $chatroom['omi2name'];
  $omiText = $chatroom['omi2text'];
} else if ($inputParams['omikujiid'] === OMIKUJI3_ID) {
  $omiName = $chatroom['omi3name'];
  $omiText = $chatroom['omi3text'];
} else {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'おみくじIDがありません。';
  goto outputPage;
}

if (!usedStr($omiText)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'おみくじが設定されていません。';
  goto outputPage;
}

$omiArray = explode(',', $omiText);
if (!usedArr($omiArray)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'おみくじが設定されていません。';
  goto outputPage;
}

// 改行コードを切り取る
foreach ($omiArray as $key => $value) {
  $value = rtrim($value);
  $value = trim($value);
  $omiArray[$key] = $value;
}

$cnt = count($omiArray);
$me = mt_rand(1, $cnt);
$text = $omiArray[$me - 1];

// 発言
$result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
  'entrykey' => $myChatentry['entrykey'],
  'characterid' => $character['id'],
  'fullname' => CHAT_LOG_SYSTEM_NAME,
  'color' => $chatroom['color'],
  'bgcolor' => $chatroom['bgcolor'],
  'message' => ('<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
               .'<span class="omikuji">（' .$omiName .'）＞ [' .$me .'] ＞ ' .$text .'</span>'
  ),
]);
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'おみくじに失敗しました。もう一度お試しください。';
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
