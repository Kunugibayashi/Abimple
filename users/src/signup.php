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
$inputParams['password2'] = inputParam('password2', 20);

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
$dbhUsers = connectRw(USERS_DB);

// 入力値チェック
if (!usedStr($inputParams['username'])) {
  $errors[] = 'ユーザー名を入力してください。';
} else if(mb_strlen($inputParams['username']) < 4 || 20 < mb_strlen($inputParams['username'])) {
  $errors[] = 'ユーザー名は 4 文字以上 20 文字以内で入力してください。';
} else if(!ctype_alnum($inputParams['username'])) {
  $errors[] = 'ユーザー名は英数字で入力してください。';
} else if(count(selectUsersUsername($dbhUsers, $inputParams['username'])) > 0) {
  $errors[] = 'このユーザーは登録されています。';
}
if (!usedStr($inputParams['password'])) {
  $errors[] = 'パスワードを入力してください。';
} else if(mb_strlen($inputParams['password']) < 4 || 20 < mb_strlen($inputParams['password'])) {
  $errors[] = 'パスワードは 4 文字以上 20 文字以内で入力してください。';
}
if (!usedStr($inputParams['password2'])) {
  $errors[] = 'パスワード確認を入力してください。';
} else if($inputParams['password'] !== $inputParams['password2']) {
  $errors[] = 'パスワードとパスワード確認が一致しません。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// 登録
$result = insertUsers($dbhUsers, $inputParams['username'], $inputParams['password']);
if (!$result) {
  $errors[] = '登録に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = '登録が完了しました。各機能を使用するにはログイン画面からログインして下さい。';

// セッションハイジャック対策
session_regenerate_id(true);


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
  <title>ユーザー登録画面</title>
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
  <h3 class="frame-title">ユーザー登録</h3>

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
    <?php if (!isLogin() || isAdmin()) { /* ログアウト時 & 管理ユーザーは常に表示 */ ?>
      <div class="note-wrap">
        <p class="note">
          サイトを利用する場合はユーザー登録をおこなってください。<br>
          <span class="point">アカウント、パスワードは変更・再発行できません。</span><br>
          忘れてしまうとログイン不可となります。ご注意ください。<br>
        </p>
      </div>
      <div class="form-wrap">
        <form name="user-form" class="user-form" action="./signup.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
          <ul class="form-row">
            <li class="form-col-title">ユーザー名<div class="mandatory-mark"></div></li>
            <li class="form-col-item"><input type="text" name="username" value="<?php echo h($inputParams['username']); ?>" maxlength="20"></li>
            <li class="form-col-note">英数字 4 文字以上 20 文字まで</li>
          </ul>
          <ul class="form-row">
            <li class="form-col-title">パスワード<div class="mandatory-mark"></div></li>
            <li class="form-col-item"><input type="password" name="password" value="<?php echo h($inputParams['password']); ?>" maxlength="20"></li>
            <li class="form-col-note">英数字 4 文字以上 20 文字まで</li>
          </ul>
          <ul class="form-row">
            <li class="form-col-title">パスワード確認<div class="mandatory-mark"></div></li>
            <li class="form-col-item"><input type="password" name="password2" value="<?php echo h($inputParams['password2']); ?>" maxlength="20"></li>
          </ul>
          <div class="form-button-wrap">
            <button type="submit">新規登録</button>
          </div>
        </form>
      </div>
    <?php } ?>
  <?php } ?>

  <?php if (usedStr($success)) { /* 成功時 */ ?>
    <div class="page-back-wrap">
      <button type="button" class="tologin-button">ログイン画面へ</button>
    </div>
  <?php } ?>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.tologin-button').on('click', function(){
    window.location.href = './login.php';
  });
});
</script>
</body>
</html>
