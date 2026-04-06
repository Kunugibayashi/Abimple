<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['keyword'] = inputParam('keyword', 20);
$inputParams['toroomchat'] = inputParam('toroomchat', 20) ?: 0; // ボタン押下時のみ値が入る
$inputParams['setkeyword'] = inputParam('setkeyword', 20) ?: 0; // ボタン押下時のみ値が入る

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));
sessionLogChatEntry();

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_SRC_LINK = SITE_ROOT .'/chatrooms/rooms/'. $roomdir .'/src/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhChatrooms = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
  $dbhChatentries = connectRw(__DIR__ .'/' .CHAT_ENTRIES_DB);

  $chatentries = selectEqualChatentries($dbhChatentries);
  $entryCount = count($chatentries);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(__DIR__ .'/' .CHAT_ROOMS_DB);
    $chatrooms = selectChatroomsConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0] ?? [];

  // 公開ルームの場合はリダイレクト
  if ($chatroom['secrettype'] == CHAT_ROOM_OPEN) {
    header('Location: ' .$ROOMDIR_SRC_LINK .'roomtop.php');
    exit;
  }

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhChatrooms = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
$dbhChatentries = connectRw(__DIR__ .'/' .CHAT_ENTRIES_DB);
$dbhChatsecrets = connectRw(__DIR__ .'/' .CHAT_SECRETS_DB);

$chatentries = selectEqualChatentries($dbhChatentries);
$entryCount = count($chatentries);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
if (!usedArr($chatrooms)) {
  firstAccessChatroom(__DIR__ .'/' .CHAT_ROOMS_DB);
  $chatrooms = selectChatroomsConfig($dbhChatrooms);
}
$chatroom = $chatrooms[0] ?? [];

if (!usedStr($inputParams['keyword'])) {
  $errors[] = '入室キーワードを入力してください。';
} else if(mb_strlen($inputParams['keyword']) < 4 || 20 < mb_strlen($inputParams['keyword'])) {
  $errors[] = '入室キーワードは 4 文字以上 20 文字以内で入力してください。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// 秘匿ルーム、または、キーワードルームかつ管理者のみパスワード設定可能
if ($chatroom['secrettype'] == CHAT_ROOM_SECRET || ($chatroom['secrettype'] == CHAT_ROOM_KEYWORD && isAdmin())) {
  // パスワード設定の場合
  if ($inputParams['setkeyword']) {
    if ($entryCount > 0) {
      $errors[] = '入室者がいるため、入室キーワードを変更できません。';
      goto outputPage;
    }

    updateChatsecrets($dbhChatsecrets, $inputParams['keyword']);
    $success = '入室キーワードを設定しました。';

    goto outputPage;
  }
}

// 入室画面へ場合
if ($inputParams['toroomchat']) {

  $chatsecrets = selectChatsecrets($dbhChatsecrets);
  if (!usedArr($chatsecrets)) {
    firstAccessChatsecrets(__DIR__ .'/' .CHAT_SECRETS_DB);
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
  }
  $dbKeyword = $chatsecrets[0]['keyword'];

  if ($dbKeyword != $inputParams['keyword']) {
    $errors[] = '入室キーワードが異なります。';
    goto outputPage;
  }

  setSecretKeyword($inputParams['keyword']);

  header('Location: ' .$ROOMDIR_SRC_LINK .'roomtop.php');
  exit;
}


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
  <title>入室キーワード確認</title>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/<?php echo h(SITE_TEMPLATE); ?>.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>assets/css/user-edit.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">
  <h3 class="frame-title">
    <?php if ($chatroom['secrettype'] == CHAT_ROOM_SECRET) { ?>【秘匿】<?php } ?>
    <?php if ($chatroom['secrettype'] == CHAT_ROOM_KEYWORD) { ?>【KEYWORD】<?php } ?>
    入室キーワード確認
  </h3>

  <?php if ($chatroom['secrettype'] == CHAT_ROOM_SECRET) { ?>
    <div class="note-wrap">
      <p class="note">
        秘匿ルームは入室キーワードを設定し、キーワードを入力することで入室することができます。<br>
        入室キーワードは入室者が0人の時に設定することが可能です。<br>
      </p>
      <p class="note">
        全員が退出時に<span class="point">ログがすべて削除</span>され、<span class="point">キーワードがリセット</span>されます。<br>
      </p>
      <p class="note">
        現在の入室人数：<?php echo h($entryCount); ?>人<br>
      </p>
    </div>
  <?php } ?>

  <?php if ($chatroom['secrettype'] == CHAT_ROOM_KEYWORD) { ?>
    <div class="note-wrap">
      <p class="note">
        キーワードルームはキーワードを入力することで入室することができます。<br>
        入室キーワードは入室者が0人の時に、<span class="point">管理者のみが設定することが可能</span>です。<br>
      </p>
      <p class="note">
        ログは退出しても残ります。管理者が変更しない限り、同じキーワードが使用できます。<br>
      </p>
      <p class="note">
        現在の入室人数：<?php echo h($entryCount); ?>人<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedStr($success)) { /* 成功メッセージ */ ?>
    <div class="mes-wrap">
      <ul class="success-mes-wrap">
        <li class="success-mes"><?php echo h($success); ?></li>
      </ul>
    </div>
  <?php } ?>

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <div class="form-wrap">
    <form name="secrettop-form" class="secrettop-form" action="<?php echo h($ROOMDIR_SRC_LINK); ?>secrettop.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <ul class="form-row">
        <li class="form-col-title">入室キーワード<div class="mandatory-mark"></div></li>
        <li class="form-col-item"><input type="text" name="keyword" value="<?php echo h($inputParams['keyword']); ?>" maxlength="20"></li>
      </ul>
      <div class="form-button-wrap">
        <button type="submit" name="toroomchat" value="1">入室画面へ</button>
        <?php if ($chatroom['secrettype'] == CHAT_ROOM_SECRET || ($chatroom['secrettype'] == CHAT_ROOM_KEYWORD && isAdmin())) { ?>
          <button type="submit" name="setkeyword" value="2">入室キーワードを設定</button>
        <?php } ?>
      </div>
    </form>
  </div>

</div>
</body>
</html>
