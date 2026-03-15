<?php
if (!defined('CSS_STRING_MODE')) {
  header('Content-Type: text/css; charset=UTF-8');
}

?>
/* ------------------------------------------------------------------------------------------------- */
/* チャットテンプレート2                                                                             */
/* ------------------------------------------------------------------------------------------------- */
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
div.chatconfig-wrap {
  margin: 2rem;
}
/* チャット画面レイアウト */
div.chatconfig-wrap {
  display: grid;
  grid-template-rows: 1fr 2.5rem; /* 縦 */
  grid-template-columns: 24rem 1fr; /* 横 */
}
div.chatconfig-title-wrap {
  grid-column: 1 / 2; /* 横 */
  grid-row: 1 / 3; /* 縦 */
}
div.chatconfig-guide {
  grid-column: 2 / 3; /* 横 */
  grid-row: 1 / 2; /* 縦 */
}
div.form-wrap {
  grid-column: 2 / 3; /* 横 */
  grid-row: 2 / 3; /* 縦 */
}
/* チャットルームタイトル */
div.chatconfig-title-wrap {
  border: double 14px;
  width: 24rem;
  height: 24rem;
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  word-break: break-all;
}
h3.chatconfig-title {
  letter-spacing: 0.2rem;
}
/* チャットルーム説明 */
div.chatconfig-guide {
  margin: 1rem;
  overflow: auto;
  word-break: break-all;
}
