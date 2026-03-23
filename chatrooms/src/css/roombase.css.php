<?php
if (!defined('CSS_STRING_MODE')) {
  header('Content-Type: text/css; charset=UTF-8');
}

?>
/* ------------------------------------------------------------------------------------------------- */
/* 共通                                                                                              */
/* ------------------------------------------------------------------------------------------------- */
body, h1, h2, h3, h4, ul, li, div {
  margin: 0;
  padding: 0;
}
body {
  color: var(--chat-color);
  background-color: var(--chat-bgcolor);
  background-image: var(--chat-bgimage);
  background-repeat: var(--chat-bg-repeat);
}
a {
  color: var(--chat-color);
}
.note-wrap {
  margin: 0;
}
.note {
  margin: 1rem 0;
}
.roomtop-header {
  color: var(--chat-bgcolor);
  background-color: var(--chat-color);
}
.roomtop-header-item > a {
  color: var(--chat-bgcolor);
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
.roomtop-header {
  grid-row: 1 / 2;
}
.chatconfig-wrap {
  grid-row: 2 / 3;
  overflow: auto;
}
.content-log-wrap {
  grid-row: 3 / 4;
}
/* ヘッダー */
.roomtop-header {
  display: flex;
  justify-content: flex-end;
  font-size: 0.8rem;
}
.roomtop-header-item-group {
  display: flex;
  margin: 0.5rem;
}
.roomtop-header-item {
  padding: 0 1rem;
  list-style-type: none;
}
/* 戻るボタン */
.page-back-wrap {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}
.page-back-wrap>button:active,
.page-back-wrap>button:hover,
.page-back-wrap>button {
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
.mes-wrap {
  display: flex;
  justify-content: center;
  margin: 2rem 0;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャットTOP画面                                                                                   */
/* ------------------------------------------------------------------------------------------------- */
.chatconfig-title-wrap {
  background-image: var(--chat-bgimage);
  background-repeat: var(--chat-bg-repeat);
}
.roomtop-form-wrap {
  overflow: auto;
  margin: 0;
  padding: 0;
}
.form-button-wrap {
  display: flex;
  justify-content: flex-end;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャット入室フォーム画面                                                                          */
/* ------------------------------------------------------------------------------------------------- */
.roomenter-form-wrap {
  display: flex;
  justify-content: center;
  margin: 1rem 0;
}
.roomenter-note-wrap {
  width: 22rem;
  margin: 0 2rem;
  font-size: 0.8rem;
}
.roomenter-form .form-row {
  display: flex;
}
.roomenter-form .form-col-title {
  width: 8rem;
  padding: 0.2rem;
  margin-top: 0.6rem;
}
.roomenter-form .form-col-item {
  margin-top: 0.5rem;
}
.roomenter-form .form-button-wrap {
  margin-top: 2rem;
  margin-right: 2rem;
  display: flex;
  justify-content: flex-end;
}
.form-col-item-group {
  display: flex;
  align-items: flex-start;
}
select[name="inoutmesflg"],
select[name="characterid"],
select[name="viewcharacterid"] {
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
.roomenter-title {
  border-bottom: solid 2px;
  font-size: 2rem;
  text-align: center;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャット画面（発言）                                                                              */
/* ------------------------------------------------------------------------------------------------- */
.roomchat-content-wrap {
  display: grid;
  grid-template-columns: 1fr 16rem;
  grid-template-rows: 28rem 1fr;
}
.chat-form-wrap {
  grid-column: 1 / 2;
  grid-row: 1 / 2;
  overflow: auto;
}
.random-wrap {
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
  width: 60vw;
}
.chat-form select[name="whisperid"],
.reload-form select[name="lognum"],
.reload-form select[name="logsec"],
.reload-form select[name="usebell"] {
  width: 8rem;
}
.chat-form textarea[name="message"] {
  resize: both;
  width: 60vw;
  height: 4rem;
}
/* エラーメッセージ */
.result-mes-wrap {
  display: flex;
  justify-content: center;
}
/* ボタン */
.chat-button-wrap {
  display: flex;
  justify-content: center;
}
.chat-button-wrap>button {
  margin: 0.5rem;
}
/* チャットフォーム */
.chat-form-wrap {
  margin-bottom: 0;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}
.reload-form .form-row,
.chat-form .form-row {
  border-bottom: dotted 1px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}
.reload-form .form-col-title:not(:first-child),
.chat-form .form-col-title:not(:first-child) {
  margin-left: 2rem;
}
.reload-form .form-col-title,
.chat-form .form-col-title {
  width: 7rem;
  min-width: 7rem;
  margin: 0.5rem 0;
}
.reload-form .form-col-item,
.chat-form .form-col-item {
  margin: 0.5rem 0;
  width: 10rem;
}
.reload-form .form-col-item-name,
.chat-form .form-col-item-name {
  width: 25rem;
}
.reload-form .form-row-item-group,
.chat-form .form-row-item-group {
  display: flex;
  align-content: center;
  width: 13rem;
}
.reload-form .form-col-item-group,
.chat-form .form-col-item-group {
  display: flex;
  flex-direction: column;
}
.reload-form .form-col-note,
.chat-form .form-col-note {
  font-size: 0.8rem;
  opacity: 0.6;
  width: 20rem;
}
.reload-form .form-col-note-message,
.chat-form .form-col-note-message {
  width: 30rem;
}
/* ダイス おみくじ 山札 */
.random-border-wrap {
  margin: 1rem 0 0 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.dice-form-wrap {
  width: 13rem;
}
.deck-title,
.omi-title,
.dice-title {
  font-size: 1.2rem;
  font-weight: bold;
  border-bottom: dotted 1px;
  width: 13rem;
  margin-bottom: 0.2rem;
}
.deck-reset-button,
.deck-button,
.omi-button,
input[name="dice"] {
  width: 13rem;
}
.form-omi-note {
  font-size: 0.75rem;
  opacity: 0.6;
}
.form-deck-note {
  font-size: 0.75rem;
  opacity: 0.6;
}
/* ------------------------------------------------------------------------------------------------- */
/* 自由設定項目変更                                                                                  */
/* ------------------------------------------------------------------------------------------------- */
.chatroom-setting-title {
  border-bottom: solid 2px;
  font-size: 2rem;
  text-align: center;
}
.chatroom-setting-wrap {
  display: grid;
  grid-column: 1 / 2;
  grid-row: 2 / 3;
  overflow: auto;
  justify-content: center;
}
.chatroom-frame-wrap {
  grid-column: 1 / 3;
  grid-row: 3 / 4;
}
.chatroom-setting-wrap {
  padding: 2rem;
}
/* 入力フォーム */
.setting-form-wrap {
  display: flex;
  justify-content: center;
}
.setting-form {
  margin: 1rem 0;
  padding: 2rem;
  border-radius: 1rem;
  border: solid 1px;
}
.setting-form .form-row {
  margin: 1rem 0;
}
.setting-form .form-col-title {
  font-weight: bold;
  margin-bottom: 2px;
}
.setting-form .form-col-note {
  font-size: 0.8rem;
  opacity: 0.6;
}
.setting-form .form-button-wrap {
  display: flex;
  justify-content: center;
}
.setting-form input[name="title"] {
  width: 70vw;
}
.setting-form textarea[name="guide"] {
  width: 70vw;
  height: 5rem;
}
/* ------------------------------------------------------------------------------------------------- */
/* チャットログ                                                                                      */
/* ------------------------------------------------------------------------------------------------- */
.content-log-wrap {
  color: var(--chat-color);
  background-color: var(--chat-bgcolor);
  border-top: 3px solid var(--chat-color);
  margin: 0;
  padding: 0;
  display: block;
  overflow-y: auto;
}
/* ヘッダー */
header.chatroom-header-wrap {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin: 0.5rem;
  border-bottom: solid 2px;
}
.chatroom-header-title {
  font-size: 1.5rem;
  word-break: break-all;
}
.chatroom-item-group {
  display: flex;
  min-width: 8rem;
}
.chatroom-item-title {
  font-weight: bold;
  word-break: break-all;
}
.chatroom-item-wrap {
  font-size: 0.9rem;
}
/* エントリーキー */
.entrykey {
  display: none;
}
/* 参加者 */
.entries-wrap {
  display: flex;
  align-items: center;
  margin-left: 0.5rem;
}
.entries-title {
  font-weight: bold;
  font-size: 1rem;
  padding: 0;
  margin: 0;
}
.entries-item-group {
  display: flex;
  align-items: center;
}
.entries-item,
.entries-no-item {
  margin-right: 0.5rem;
  border-radius: 0.2rem;
  padding: 0.2rem;
  font-size: 0.8rem;
}
/* 部屋案内ポップアップ */
.chatroom-header-title {
  position: relative;
}
.chatroom-header-title:hover .chatroom-header-guide {
  z-index: 10;
  display: block;
  position: absolute;
  top: 2.5rem;
  left: 2rem;
  line-height: 1.2rem;
}
.chatroom-header-guide {
  color: var(--chat-color);
  background-color: var(--chat-bgcolor);
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
  color: var(--chat-color);
  background-color: var(--chat-bgcolor);
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
.log-error {
  margin: 0 1rem;
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
