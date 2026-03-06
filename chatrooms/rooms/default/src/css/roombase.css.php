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
/* ------------------------------------------------------------------------------------------------- */
/* 共通                                                                                              */
/* ------------------------------------------------------------------------------------------------- */
body, h1, h2, h3, h4, ul, li, div {
  margin: 0;
  padding: 0;
}
body {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
}
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2) { ?>
  body {
    <?php if (usedStr($chatroom['bgimage'])) { ?>
      background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    <?php } ?>
    background-repeat: repeat;
  }
<?php } ?>
a {
  color: <?php echo h($chatroom['color']); ?>;
}
header.roomtop-header {
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
li.roomtop-header-item>a {
  color: <?php echo h($chatroom['bgcolor']); ?>;
}
#id-roomtop-content-wrap {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 99vh;
}
ul, li {
  list-style-type: none;
}
/* レイアウト */
#id-roomtop-content-wrap {
  display: grid;
  grid-template-rows: 2rem 28rem 1fr;
}
header.roomtop-header {
  grid-row: 1 / 2;
}
div.chatconfig-wrap {
  grid-row: 2 / 3;
  overflow: auto;
}
div.chatroom-frame-wrap {
  grid-row: 3 / 4;
}
/* ヘッダー */
header.roomtop-header {
  display: flex;
  justify-content: flex-end;
  font-size: 0.8rem;
}
ul.roomtop-header-item-group {
  display: flex;
  margin: 0.5rem;
}
li.roomtop-header-item {
  padding: 0 1rem;
  list-style-type: none;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャット画面フォーム                                                                              */
/* ------------------------------------------------------------------------------------------------- */
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) { ?>
  div.chatconfig-title-wrap {
    <?php if (usedStr($chatroom['bgimage'])) { ?>
      background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    <?php } ?>
    background-repeat: repeat;
  }
<?php } ?>
div.roomenter-form-wrap {
  overflow: auto;
}
div.form-wrap {
  margin: 0;
  padding: 0;
}
ul.form-row {
  display: flex;
}
li.form-col-title {
  width: 8rem;
  padding: 0.2rem;
  margin-top: 0.6rem;
}
li.form-col-item {
  margin-top: 0.5rem;
}
div.form-button-wrap {
  display: flex;
  justify-content: flex-end;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャットログ                                                                                      */
/* ------------------------------------------------------------------------------------------------- */
.content-log-wrap {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  margin: 0;
  padding: 0;
  display: unset;
}
/* ヘッダー */
header.chatroom-header-wrap {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin: 0.5rem;
  border-bottom: solid 2px;
}
h3.chatroom-header-title {
  font-size: 1.5rem;
  word-break: break-all;
}
ul.chatroom-item-group {
  display: flex;
  min-width: 8rem;
}
li.chatroom-item-title {
  font-weight: bold;
  word-break: break-all;
}
.chatroom-item-wrap {
  font-size: 0.9rem;
}
/* エントリーキー */
div.entrykey {
  display: none;
}
/* 参加者 */
.entries-wrap {
  display: flex;
  align-items: center;
  margin-left: 0.5rem;
}
h5.entries-title {
  font-weight: bold;
  font-size: 1rem;
  padding: 0;
  margin: 0;
}
ul.entries-item-group {
  display: flex;
  align-items: center;
}
li.entries-item,
li.entries-no-item {
  margin-right: 0.5rem;
  border-radius: 0.2rem;
  padding: 0.2rem;
  font-size: 0.8rem;
}
li.entries-item {
  cursor: pointer;
}
/* 部屋案内ポップアップ */
h3.chatroom-header-title {
  position: relative;
}
h3.chatroom-header-title:hover .chatroom-header-guide {
  z-index: 10;
  display: block;
  position: absolute;
  top: 2.5rem;
  left: 2rem;
  line-height: 1.2rem;
}
.chatroom-header-guide {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  position: absolute;
  display: none;
  padding: 1rem;
  border: 2px dotted;
  width: 80vw;
  left : -1%;
  font-size: 0.8rem;
  font-weight: normal;
}
/* 備考ポップアップ */
.chat-fullname {
  position: relative;
  word-break: break-all;
}
.chat-fullname:hover .chat-memo {
  z-index: 10;
  display: block;
  position: absolute;
  top: 2rem;
  left: 2rem;
  line-height: 1.2rem;
}
.chat-memo {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  position: absolute;
  display: none;
  padding: 1rem;
  border: 2px dotted;
  width: 40vw;
  left : -1%;
  font-size: 0.8rem;
  font-weight: normal;
  word-break: break-all;
}
/* システム */
.chat-narr-wrap {
  opacity: 0.7;
  margin: 0.5em 0 0.5em 2rem;
}
.chat-narr-arrow {
  margin: 0 0.5em 0 0.2rem;
}
.chat-narr-fullname {
  font-weight: bold;
}
.chat-narr-message {
  margin-right: 0 0.5rem;
  word-break: break-all;
}
.chat-narr-message>span.fullname {
  font-weight: bold;
}
.chat-narr-created {
  font-size: 0.5rem;
  min-width: 12rem;
  opacity: 0.3;
}
.log-wrap {
  margin: 1rem;
}
.chat-arrow {
  margin: 0 0.5em 0 0.2rem;
}
.chat-editing,
.chat-created {
  font-size: 0.5rem;
  opacity: 0.3;
}
.chat-fullname {
  font-weight: bold;
}
.chat-message {
  line-height: 1.5rem;
  margin: 0.2em 0;
  padding: 0.5em 0;
  line-height: 1.5;
  word-break: break-all;
}
