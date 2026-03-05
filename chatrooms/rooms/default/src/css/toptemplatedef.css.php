<?php
require_once(__DIR__ .'/../../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../../core/src/administrator.php');

require_once(__DIR__ .'/../config.php');
require_once(__DIR__ .'/../functions.php');

$errors = array();
$inputParams = array();

// DB接続
$dbhChatrooms = connectRo(__DIR__ .'/../' .CHAT_ROOMS_DB);
$dbhChatsecrets = connectRo(__DIR__ .'/../' .CHAT_SECRETS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
if (!usedArr($chatrooms)) {
  firstAccessChatroom(__DIR__ .'/../' .CHAT_ROOMS_DB);
  $chatrooms = selectChatroomsConfig($dbhChatrooms);
}
$chatroom = $chatrooms[0];

header('Content-Type: text/css; charset=UTF-8');

?>
/* 共通 */
a {
  color: <?php echo h($chatroom['color']); ?>;
}
body {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
}
/* ヘッダー */
header.header {
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
li.header-item>a {
  color: <?php echo h($chatroom['bgcolor']); ?>;
}

/* 各種テンプレート個別設定 */
div.chatconfig-wrap {
  padding: 3rem 3rem 1rem 3rem;
}
/* チャット画面レイアウト */
div.chatconfig-wrap {
  display: grid;
  grid-template-rows: 4rem 1fr 5rem; /* 縦 */
}
div.chatconfig-title-wrap {
  grid-row: 1 / 2; /* 縦 */
}
div.chatconfig-guide {
  grid-row: 2 / 3; /* 縦 */
}
div.form-wrap {
  grid-row: 3 / 4; /* 縦 */
}
/* チャットルームタイトル */
h3.chatconfig-title {
  border-bottom: solid 2px;
  font-size: 3rem;
  text-align: center;
  word-break: break-all;
}
/* チャットルーム説明 */
div.chatconfig-guide {
  margin: 1rem 0;
  height: 14rem;
  overflow: auto;
  word-break: break-all;
}

