<?php
/* ログに発言を出力する。
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

$jsonArray = array();
$inputParams = array();

$inputParams['characterid'] = inputParam('characterid', 20);

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));
sessionLogChatEntryCharacter($inputParams['characterid']);

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';
$jsonArray['whisperlist'] = '';

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
$dbhChatrooms  = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
$dbhChatentries = connectRw(__DIR__ .'/' .CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(__DIR__ .'/' .CHAT_LOGS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

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

// リストを取得
$toWhispers = selectEqualChatentries($dbhChatentries);

// リスト整形
$whisperlist = '<option value="">なし</option>';
foreach ($toWhispers as $toWhisper) {
  // 自分自身は含めない
  if($inputParams['characterid'] == $toWhisper['characterid']) {
    continue;
  }
  $whisperlist .= '<option value="' .$toWhisper['characterid'] .'">' .$toWhisper['fullname'] .'</option>';
}

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';
$jsonArray['whisperlist'] = $whisperlist;

/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:

$json =  json_encode($jsonArray);
setJsonHeader();
echo($json);
