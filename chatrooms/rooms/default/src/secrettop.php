<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['keyword'] = inputParam('keyword', 20);
$inputParams['toroomchat'] = inputParam('toroomchat', 20);
$inputParams['setkeyword'] = inputParam('setkeyword', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhChatentries = connectRw(CHAT_ENTRIES_DB);

  $chatentries = selectEqualChatentries($dbhChatentries);
  $entryCount = count($chatentries);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatsecrets = connectRw(CHAT_SECRETS_DB);

$chatentries = selectEqualChatentries($dbhChatentries);
$entryCount = count($chatentries);


if (!usedStr($inputParams['keyword'])) {
  $errors[] = '入室キーワードを入力してください。';
} else if(mb_strlen($inputParams['keyword']) < 4 || 20 < mb_strlen($inputParams['keyword'])) {
  $errors[] = '入室キーワードは 4 文字以上 20 文字以内で入力してください。';
}
if (usedArr($errors)) {
  goto outputPage;
}

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

// 入室画面へ場合
if ($inputParams['toroomchat']) {

  $chatsecrets = selectChatsecrets($dbhChatsecrets);
  if (!usedArr($chatsecrets)) {
    firstAccessChatsecrets(CHAT_SECRETS_DB);
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
  }
  $dbKeyword = $chatsecrets[0]['keyword'];

  if ($dbKeyword != $inputParams['keyword']) {
    $errors[] = '入室キーワードが異なります。';
    goto outputPage;
  }

  setSecretKeyword($inputParams['keyword']);

  header('Location: ./roomtop.php');
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
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/<?php echo h(SITE_TEMPLATE); ?>.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/assets/css/user-edit.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">
  <h3 class="frame-title">入室キーワード確認</h3>

  <div class="note-wrap">
    <p class="note">
      秘匿ルームは入室キーワードを設定すると入室することができます。<br>
      入室キーワードは入室者が0人の時に設定することが可能です。<br>
    </p>
    <p class="note">
      ログは<span class="point">全員が退出時にすべて削除</span>されます。<br>
    </p>
    <p class="note">
      現在の入室人数：<?php echo h($entryCount); ?>人<br>
    </p>
  </div>

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
    <form name="secrettop-form" class="secrettop-form" action="./secrettop.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <ul class="form-row">
        <li class="form-col-title">入室キーワード<div class="mandatory-mark"></div></li>
        <li class="form-col-item"><input type="text" name="keyword" value="<?php echo h($inputParams['keyword']); ?>" maxlength="20"></li>
      </ul>
      <div class="form-button-wrap">
        <button type="submit" name="toroomchat" value="1">入室画面へ</button>
        <button type="submit" name="setkeyword" value="2">入室キーワードを設定</button>
      </div>
    </form>
  </div>

</div>
</body>
</html>
