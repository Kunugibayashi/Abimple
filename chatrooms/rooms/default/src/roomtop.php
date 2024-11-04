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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策はフォーム表示時にセット

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);
  $dbhChatsecrets = connectRo(CHAT_SECRETS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOMS_DB);
    $chatrooms = selectChatroomsConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0];

  // 秘匿ルームの場合
  if ($chatroom['issecret'] == 1) {
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
    if (!usedArr($chatsecrets)) {
      firstAccessChatsecrets(CHAT_SECRETS_DB);
      $chatsecrets = selectChatsecrets($dbhChatsecrets);
    }
    $dbKeyword = $chatsecrets[0]['keyword'];
    $sessionKeyword = getSecretKeyword();
    if (!usedStr($dbKeyword) || !usedStr($sessionKeyword) || $dbKeyword != $sessionKeyword) {
      header('Location: ./secrettop.php');
      exit;
    }
  }

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// POSTは処理をしない。
exit;


/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h($chatroom['title']); ?></title>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <!-- 直接記載があるため下部に移動 -->
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomtop-content-wrap" class="content-wrap">

  <header id="id-roomtop-header" class="header">
    <nav class="header-menu">
      <ul class="header-item-group">
        <?php if ($chatroom['isfree']) { ?>
          <li class="header-item"><a href="./roomseting.php">ルーム設定変更</a></li>
        <?php } ?>
        <li class="header-item"><a href="./log.php?lognum=100&logsec=25" target="_blank">ログ別窓表示</a></li>
        <?php if (isAdmin()) { ?>
          <li class="header-item"><a href="./admin.php">管理画面</a></li>
        <?php } ?>
      </ul>
    </nav>
  </header>

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <div class="chatconfig-wrap">
    <div class="chatconfig-title-wrap">
      <h3 class="chatconfig-title"><?php echo h($chatroom['title']); ?></h3>
    </div>

    <div class="chatconfig-guide"><?php echo hb($chatroom['guide']); ?></div>

    <div class="form-wrap roomenter-form-wrap">
      <form name="roomenter-form" class="roomenter-form" action="./roomenter.php" method="GET">
        <div class="form-button-wrap submit-wrap">
          <button type="submit">入室キャラクター選択</button>
        </div>
      </form>
    </div>
  </div>

  <div class="chatroom-frame-wrap">
    <iframe id="log-top" name="log" title="ルームログ"
      src="./log.php">
    </iframe>
  </div>

</div>
<style>
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
  grid-template-rows: 2em 28em 1fr;
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
  font-size: 0.8em;
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
ul.header-item-group {
  display: flex;
  margin: 0.5em;
}
li.header-item {
  padding: 0 1em;
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
  width: 8em;
  padding: 0.2em;
  margin-top: 0.6em;
}
li.form-col-item {
  margin-top: 0.5em;
}
div.form-button-wrap {
  display: flex;
  justify-content: flex-end;
}

<?php if ($chatroom['toptemplate'] === CHAT_TOP_DEFAULT
       || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2
) { ?>
  div.chatconfig-wrap {
    padding: 3em 3em 1em 3em;
  }
  /* チャット画面レイアウト */
  div.chatconfig-wrap {
    display: grid;
    grid-template-rows: 4em 1fr 5em; /* 縦 */
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
    font-size: 3em;
    text-align: center;
  }
  /* チャットルーム説明 */
  div.chatconfig-guide {
    margin: 1em 0;
    height: 14em;
    overflow: auto;
  }
<?php } ?>
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE1
       || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3
) { ?>
  div.chatconfig-wrap {
    margin: 2em;
  }
  /* チャット画面レイアウト */
  div.chatconfig-wrap {
    display: grid;
    grid-template-rows: 1fr 2.5em; /* 縦 */
    grid-template-columns: 24em 1fr; /* 横 */
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
    width: 24em;
    height: 24em;
    padding: 2em;
    color: <?php echo h($chatroom['bgcolor']); ?>;
    background-color: <?php echo h($chatroom['color']); ?>;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  h3.chatconfig-title {
    letter-spacing: 0.2em;
  }
  /* チャットルーム説明 */
  div.chatconfig-guide {
    margin: 1em;
    overflow: auto;
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
</style>
<?php if (usedStr($chatroom['roomcss'])) { ?>
  <style>
    /* DB登録のCSS記載 */
    <?php echo h($chatroom['roomcss']) ?>
  </style>
<?php } ?>
<!-- レスポンシブ用 -->
<link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
</body>
</html>
