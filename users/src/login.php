<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['username'] = inputParam('username', 20);
$inputParams['password'] = inputParam('password', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();
  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhUsers = connectRo(USERS_DB);

// 入力値チェック
if (!usedStr($inputParams['username'])) {
  $errors[] = 'ユーザー名を入力してください。';
} else if(mb_strlen($inputParams['username']) < 4 || 20 < mb_strlen($inputParams['username'])) {
  $errors[] = 'ユーザー名は 4 文字以上 20 文字以内で入力してください。';
}
if (!usedStr($inputParams['password'])) {
  $errors[] = 'パスワードを入力してください。';
} else if(mb_strlen($inputParams['password']) < 4 || 20 < mb_strlen($inputParams['password'])) {
  $errors[] = 'パスワードは 4 文字以上 20 文字以内で入力してください。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// ログイン可能かのチェック
$users = selectUsersUsername($dbhUsers, $inputParams['username']);
if (!usedArr($users)) {
  $errors[] = 'ユーザー名かパスワードが異なります。';
  goto outputPage;
}
$user = $users[0];
if(!password_verify($inputParams['password'], $user['password'])){
  $errors[] = '認証できませんでした。ユーザー名かパスワードが異なります。';
  goto outputPage;
}

// DBの値をログイン情報として保存
setUserid($user['id']);
setUsername($user['username']);

// セッションハイジャック対策
session_regenerate_id(true);
$success = 'ログインに成功しました。';


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
  <title>ログイン画面</title>
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
  <h3 class="frame-title">ログイン</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
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

  <?php if (!usedStr($success)) { /* 成功以外にフォームを表示 */ ?>
    <?php if (!isLogin()) { /* ログアウト時 */ ?>
      <div class="form-wrap">
        <form name="login-form" class="login-form" action="./login.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
          <ul class="form-row">
            <li class="form-col-title">ユーザー名<div class="mandatory-mark"></div></li>
            <li class="form-col-item"><input type="text" name="username" value="<?php echo h($inputParams['username']); ?>" maxlength="20"></li>
          </ul>
          <ul class="form-row">
            <li class="form-col-title">パスワード<div class="mandatory-mark"></div></li>
            <li class="form-col-item"><input type="password" name="password" value="<?php echo h($inputParams['password']); ?>" maxlength="20"></li>
          </ul>
          <div class="form-button-wrap">
            <button type="submit">ログイン</button>
          </div>
        </form>
      </div>
    <?php } ?>
  <?php } ?>

  <?php if (isLogin()) { /* ログイン時 */ ?>
    <div class="page-back-wrap">
      <button type="button" class="sitetop-button">サイトトップへ</button><!-- jQuery -->
    </div>
  <?php } ?>

</div>
</body>
</html>
