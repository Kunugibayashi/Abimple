<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$errors = array();
$inputParams = array();

// DB接続
$dbhChatrooms = connectRo(CHAT_ROOMS_DB);
$dbhChatsecrets = connectRo(CHAT_SECRETS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
if (!usedArr($chatrooms)) {
  firstAccessChatroom(CHAT_ROOMS_DB);
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
div.content-wrap {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 99vh;
}
ul, li {
  list-style-type: none;
}
/* レイアウト */
div.content-wrap {
  display: grid;
  grid-template-rows: 2rem 28rem 1fr;
}
header.header {
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
header.header {
  display: flex;
  justify-content: flex-end;
  font-size: 0.8rem;
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
ul.header-item-group {
  display: flex;
  margin: 0.5rem;
}
li.header-item {
  padding: 0 1rem;
  list-style-type: none;
}
li.header-item>a {
  color: <?php echo h($chatroom['bgcolor']); ?>;
}
/* インラインフレーム */
div.chatroom-frame-wrap {
  border-top: solid 4px;
}
/* チャット画面フォーム */
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

<?php if ($chatroom['toptemplate'] === CHAT_TOP_DEFAULT || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2) { ?>
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
<?php } ?>

<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE1 || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) { ?>
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
    color: <?php echo h($chatroom['bgcolor']); ?>;
    background-color: <?php echo h($chatroom['color']); ?>;
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
<?php } ?>

<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2) { ?>
  body {
    <?php if (usedStr($chatroom['bgimage'])) { ?>
      background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    <?php } ?>
    background-repeat: repeat;
  }
<?php } ?>

<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) { ?>
  div.chatconfig-title-wrap {
    <?php if (usedStr($chatroom['bgimage'])) { ?>
      background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    <?php } ?>
    background-repeat: repeat;
  }
<?php } ?>

<?php if (usedStr($chatroom['roomcss'])) { ?>
    /* DB登録のCSS記載 */
    <?php echo h($chatroom['roomcss']) ?>
<?php } ?>
