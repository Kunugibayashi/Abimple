<?php
header('Content-Type: text/css; charset=UTF-8');

?>
/* ------------------------------------------------------------------------------------------------- */
/* チャットテンプレートデフォルト                                                                    */
/* ------------------------------------------------------------------------------------------------- */
/* 各種テンプレート個別設定 */
div.chatconfig-wrap {
  padding: 2rem 2rem 1rem 2rem;
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
