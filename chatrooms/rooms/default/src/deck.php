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
$dbhChatrooms = connectRw(__DIR__ .'/' .CHAT_ROOMS_DB);
$dbhChatentries = connectRw(__DIR__ .'/' .CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(__DIR__ .'/' .CHAT_LOGS_DB);


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
  firstAccessChatroom(__DIR__ .'/' .CHAT_ROOMS_DB);
  $chatrooms = selectChatroomsConfig($dbhChatrooms);
}
$chatroom = $chatrooms[0];


$deckText = $chatroom['deck1text'];

if (!usedStr($deckText)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '山札が設定されていません。';
  goto outputPage;
}

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
  $errorString = (
    '<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
      .'<span class="deck">（' .$chatroom['deck1name'] .'）＞ ' .'山札が空です。リセットしてください。'
    .'</span>'
  );
  $result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'logtype' => LOGTYPE_SYSTEM,
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => $errorString,
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

// 表にする発言を取り出す
$showTextArray = explode('#', $showTailArray['deckValue']);

// メッセージ作成
$messageString = (
  '<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
    .'<span class="deck">（' .$chatroom['deck1name'] .'）'
    .'<span class="deck-arrow">＞</span>' .$showTextArray[1]
  .'</span>'
);
$announceString = (
  '<span class="fullname"><span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span></span>'
    .'<span class="deck">（' .$chatroom['deck1name'] .'）'
    .'<span class="deck-arrow">＞</span>' .$character['fullname'] .' がカードを引きました。'
  .'</span>'
);

$deck1type = $chatroom['deck1type'] ?? 0;
if ($deck1type == 1) {
  // アナウンスを通常の発言に追加
  $result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'logtype' => LOGTYPE_SYSTEM,
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => $announceString,
  ]);
  if (!$result) {
    $jsonArray['code'] = 1;
    $jsonArray['errorMessage'] = '山札に失敗しました。もう一度お試しください。';
    goto outputPage;
  }
  // ささやきで内容を追加
  $result2 = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'logtype' => LOGTYPE_SYSTEM,
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => $messageString,
    'whisperflg' => 1,
    'wtocharacterid' => $character['id'],
    'wtofullname' => $character['fullname'],
  ]);
  if (!$result2) {
    $jsonArray['code'] = 1;
    $jsonArray['errorMessage'] = '山札に失敗しました。もう一度お試しください。';
    goto outputPage;
  }
} else {
  // 通常の発言
  $result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'logtype' => LOGTYPE_SYSTEM,
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => $messageString,
  ]);
  if (!$result) {
    $jsonArray['code'] = 1;
    $jsonArray['errorMessage'] = '山札に失敗しました。もう一度お試しください。';
    goto outputPage;
  }
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
