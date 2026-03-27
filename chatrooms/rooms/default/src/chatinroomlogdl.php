<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

$inputParams = array();
$jsonArray = array();

$inputParams['characterid'] = inputParam('id', 20);

// デバッグ用ログ。頻繁に出力されるため必要時のみ。
// logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));
// sessionLogChatEntryCharacter($inputParams['characterid']);

if (!usedStr($inputParams['characterid'])) {
  echo 'キャラクターIDが設定されていません。';
  exit;
}

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatrooms = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
$dbhChatentries = connectRo(__DIR__ .'/' .CHAT_ENTRIES_DB);
$dbhChatlogs = connectRo(__DIR__ .'/' .CHAT_LOGS_DB);
$dbhChatsecrets = connectRo(__DIR__ .'/' .CHAT_SECRETS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
if (!usedArr($chatrooms)) {
  firstAccessChatroom(__DIR__ .'/' .CHAT_ROOMS_DB);
  $chatrooms = selectChatroomsConfig($dbhChatrooms);
}
$chatroom = $chatrooms[0];

// 公開ルームでない場合
if ($chatroom['secrettype'] != CHAT_ROOM_OPEN) {
  $chatsecrets = selectChatsecrets($dbhChatsecrets);
  if (!usedArr($chatsecrets)) {
    firstAccessChatsecrets(__DIR__ .'/' .CHAT_SECRETS_DB);
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
  }
  $dbKeyword = $chatsecrets[0]['keyword'];
  $sessionKeyword = getSecretKeyword();
  if (!usedStr($dbKeyword) || !usedStr($sessionKeyword) || $dbKeyword != $sessionKeyword) {
    echo '入室キーワードを入力してください。';
    exit;
  }
}

// 入室者取得
$chatentries = selectEqualChatentries($dbhChatentries);
if (!usedArr($chatentries)) {
  echo '入室していません。';
  exit;
}
if (!isChatEntry($inputParams['characterid'])) {
  echo '入室していません。エラーが出続ける場合は入室しなおしてください。';
  exit;
}

// 本人確認をする
$characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
if (!usedArr($characters)) {
  // 不正アクセス
  echo '名簿が存在しません。';
  exit;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

$myChatentries = selectEqualChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (!usedArr($myChatentries)) {
  // 不正アクセス
  echo '入室していません。';
  exit;
}

// 入室時はささやきを含めてログを取得する
$characterid = (int)$inputParams['characterid'];
$chatrows = selectEqualInroomChatlogsChunk($dbhChatlogs, 100, $characterid);

// DL名
$siteTitle = removeUnsafeChars(SITE_TITLE);
$chatroomTitle = removeUnsafeChars($chatroom['title']);
$downloadName = nowYmdhi() .'_' .$siteTitle .'_' .$chatroomTitle .'_'. $chatroom['id'] .'.html';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header(
  'Content-Disposition: attachment; ' .
  'filename="' . $downloadName . '"; ' .
  "filename*=UTF-8''" . rawurlencode($downloadName)
);

// 出力バッファクリア
while (ob_get_level()) { ob_end_clean(); }

// ログ出力
echoChatLog($chatrows, $chatroom, $chatentries);

exit;
