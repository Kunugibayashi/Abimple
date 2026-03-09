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
div.content-log-wrap {
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
/* 戻るボタン */
div.page-back-wrap {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}
div.page-back-wrap>button:active,
div.page-back-wrap>button:hover,
div.page-back-wrap>button {
  margin: 0 1rem;
  padding: 1rem;
  background-color: #3e463b;
  color: #e3e2dc;
  background-image: unset;
  background-origin: unset;
  border: unset;
  border-radius: 10rem;
  box-shadow: unset;
  display: inline-block;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  filter: none;
}
/* 通信メッセージ */
div.mes-wrap {
  display: flex;
  justify-content: center;
  margin: 2rem 0;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャットTOP画面                                                                                   */
/* ------------------------------------------------------------------------------------------------- */
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) { ?>
  div.chatconfig-title-wrap {
    <?php if (usedStr($chatroom['bgimage'])) { ?>
      background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    <?php } ?>
    background-repeat: repeat;
  }
<?php } ?>
div.roomtop-form-wrap {
  overflow: auto;
  margin: 0;
  padding: 0;
}
div.form-button-wrap {
  display: flex;
  justify-content: flex-end;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャット入室フォーム画面                                                                          */
/* ------------------------------------------------------------------------------------------------- */
div.roomenter-form-wrap {
  display: flex;
  justify-content: center;
  margin: 1rem 0;
}
form.roomenter-form ul.form-row {
  display: flex;
}
form.roomenter-form li.form-col-title {
  width: 8rem;
  padding: 0.2rem;
  margin-top: 0.6rem;
}
form.roomenter-form li.form-col-item {
  margin-top: 0.5rem;
}
form.roomenter-form div.form-button-wrap {
  margin-top: 2rem;
  margin-right: 2rem;
  display: flex;
  justify-content: flex-end;
}
div.form-col-item-group {
  display: flex;
  align-items: flex-start;
}
select[name="inoutmesflg"],
select[name="characterid"] {
  width: 20rem;
}
input[name="color"],
input[name="bgcolor"] {
  width: 8rem;
}
input[name="memo"] {
  width: 20rem;
}
/* チャットルームタイトル */
h3.roomenter-title {
  border-bottom: solid 2px;
  font-size: 2rem;
  text-align: center;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャット画面（発言）                                                                              */
/* ------------------------------------------------------------------------------------------------- */
div.roomchat-content-wrap {
  display: grid;
  grid-template-columns: 1fr 16rem;
  grid-template-rows: 28rem 1fr;
}
div.chat-form-wrap {
  grid-column: 1 / 2;
  grid-row: 1 / 2;
  overflow: auto;
}
div.random-wrap {
  grid-column: 2 / 3;
  grid-row: 1 / 2;
  overflow: auto;
}
/* 入力 */
.chat-form input[name="color"],
.chat-form input[name="bgcolor"] {
  width: 8rem;
}
.chat-form input[name="memo"] {
  width: 30rem;
}
.chat-form select[name="whisperid"],
.reload-form select[name="lognum"],
.reload-form select[name="logsec"] {
  width: 8rem;
}
.chat-form textarea[name="message"] {
  resize: both;
  width: 30rem;
  height: 4rem;
}
/* エラーメッセージ */
div.result-mes-wrap {
  display: flex;
  justify-content: center;
}
/* ボタン */
div.chat-button-wrap {
  display: flex;
  justify-content: center;
}
div.chat-button-wrap>button {
  margin: 0.5rem;
}
/* チャットフォーム */
div.chat-form-wrap {
  margin-bottom: 0;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}
.reload-form ul.form-row,
.chat-form ul.form-row {
  border-bottom: dotted 1px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}
.reload-form li.form-col-title:not(:first-child),
.chat-form li.form-col-title:not(:first-child) {
  margin-left: 2rem;
}
.reload-form li.form-col-title,
.chat-form li.form-col-title {
  width: 7rem;
  min-width: 7rem;
  margin: 0.5rem 0;
}
.reload-form li.form-col-item,
.chat-form li.form-col-item {
  margin: 0.5rem 0;
  width: 10rem;
}
.reload-form li.form-col-item-name,
.chat-form li.form-col-item-name {
  width: 25rem;
}
.reload-form div.form-row-item-group,
.chat-form div.form-row-item-group {
  display: flex;
  align-content: center;
  width: 13rem;
}
.reload-form div.form-col-item-group,
.chat-form div.form-col-item-group {
  display: flex;
  flex-direction: column;
}
.reload-form div.form-col-note,
.chat-form div.form-col-note {
  font-size: 0.8rem;
  opacity: 0.6;
  width: 20rem;
}
.reload-form div.form-col-note-message,
.chat-form div.form-col-note-message {
  width: 30rem;
}
/* ダイス おみくじ 山札 */
div.random-border-wrap {
  margin: 1rem 0 0 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}
div.dice-form-wrap {
  width: 13rem;
}
h3.deck-title,
h3.omi-title,
h3.dice-title {
  font-size: 1.2rem;
  font-weight: bold;
  border-bottom: dotted 1px;
  width: 13rem;
  margin-bottom: 0.2rem;
}
button.deck-reset-button,
button.deck-button,
button.omi-button,
input[name="dice"] {
  width: 13rem;
}
div.form-omi-note {
  font-size: 0.75rem;
  opacity: 0.6;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャットログ                                                                                      */
/* ------------------------------------------------------------------------------------------------- */
.content-log-wrap {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  border-top: 3px solid <?php echo h($chatroom['color']); ?>;
  margin: 0;
  padding: 0;
  display: block;
  overflow: scroll;
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
  margin: 0 0.5rem;
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
  word-break: break-all;
}
