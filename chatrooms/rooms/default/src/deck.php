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
$dbhChatrooms = connectRw(CHAT_ROOMS_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);


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


$chatrooms = selectChatroomsConfig($dbhChatrooms);
if (!usedArr($chatrooms)) {
  firstAccessChatroom(CHAT_ROOMS_DB);
  $chatrooms = selectChatroomsConfig($dbhChatrooms);
}
$chatroom = $chatrooms[0];


$deckText = $chatroom['deck1text'];

$deckArray = explode(',', $deckText);
if (!usedArr($deckArray)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '山札が設定されていません。';
  goto outputPage;
}

// 改行コードを切り取る
foreach ($deckArray as $key => $deckValue) {
  $deckValue = trim($deckValue);
  $deckArray[$key] = trim($deckValue);
}

// 表と裏の数をカウント
$headArray = array();
$tailArray = array();
foreach ($deckArray as $key => $deckValue) {
  if(preg_match('/^0#.*/', $deckValue)){
    $tailArray[] = ['key' => $key, 'deckValue' => $deckValue];
  } else {
    $headArray[] = ['key' => $key, 'deckValue' => $deckValue];
  }
}

// 裏がゼロの場合はエラー
if (count($tailArray) === 0) {
  $result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => ('<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
                 .'<span class="deck">（' .$chatroom['deck1name'] .'）＞ ' .'山札が空です。リセットしてください。' .'</span>'
    ),
  ]);
  goto outputPage;
}

// 山札を引く
$randKey = array_rand($tailArray);
$showTailArray = $tailArray[$randKey];

// 表にする
$showTailArray['deckValue'] = preg_replace('/^0#/', '1#', $showTailArray['deckValue']);

// 元の配列に戻す
$deckArray[$showTailArray['key']] = $showTailArray['deckValue'];

// 元の文字列に戻す
$deckText = implode(",\n", $deckArray);

// 山札のDBを更新する
updateChatroomsConfig($dbhChatrooms, [
  'deck1text' => $deckText,
]);

// 発言取り出し
$showTextArray = explode('#', $showTailArray['deckValue']);

// 発言
$result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
  'entrykey' => $myChatentry['entrykey'],
  'characterid' => $character['id'],
  'fullname' => CHAT_LOG_SYSTEM_NAME,
  'color' => $chatroom['color'],
  'bgcolor' => $chatroom['bgcolor'],
  'message' => ('<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
               .'<span class="deck">（' .$chatroom['deck1name'] .'）＞ ' .$showTextArray[1] .'</span>'
  ),
]);
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '山札に失敗しました。もう一度お試しください。';
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
