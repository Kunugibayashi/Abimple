<?php
if (!defined('CSS_STRING_MODE')) {
  header('Content-Type: text/css; charset=UTF-8');
}

?>
/* ------------------------------------------------------------------------------------------------- */
/* チャットテンプレート2                                                                             */
/* ------------------------------------------------------------------------------------------------- */
body {
  background-image: var(--chat-bgimage);
  background-repeat: var(--chat-bg-repeat);
}
.chatconfig-wrap {
  padding: 2rem 2rem 1rem 2rem;
}
/* チャット画面レイアウト */
.chatconfig-wrap {
  display: grid;
  grid-template-rows: 4rem 1fr 5rem; /* 縦 */
}
.chatconfig-title-wrap {
  grid-row: 1 / 2; /* 縦 */
}
.chatconfig-guide {
  grid-row: 2 / 3; /* 縦 */
}
.form-wrap {
  grid-row: 3 / 4; /* 縦 */
}
/* チャットルームタイトル */
.chatconfig-title {
  border-bottom: solid 2px;
  font-size: 3rem;
  text-align: center;
  word-break: break-all;
}
/* チャットルーム説明 */
.chatconfig-guide {
  margin: 1rem 0;
  height: 14rem;
  overflow: auto;
  word-break: break-all;
}
