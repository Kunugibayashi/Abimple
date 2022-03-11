<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

/* セッション切断
 * 関数内で $_SESSION を呼ぶと上書きされなかったため、ここで明示的にIDをクリア
 */
$_SESSION = array();
$_SESSION['userid'] = '';
$_SESSION['username'] = '';
session_destroy();


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
  <title>ログアウト画面</title>
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
  <h3 class="frame-title">ログアウト</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (!isLogin()) { /* ログアウト時 */ ?>
    <div class="note-wrap">
      <p class="note">
        ログアウトしました。
      </p>
    </div>
  <?php } ?>

  <?php if (isLogin()) { /* ログイン時 */ ?>
    <div class="note-wrap">
      <p class="note">
        <?php echo h(getUsername()); ?>からログアウトします。よろしいですか？<br>
      </p>
    </div>
    <div class="page-button-wrap">
      <button type="button" class="warning logout-button">はい</button>
    </div>
  <?php } ?>

  <form id="logout-form" class="hidden-form" action="./logout.php" method="POST">
    <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
  </form>

  <?php if (!isLogin()) { /* ログアウト時 */ ?>
    <div class="page-back-wrap">
      <button type="button" class="sitetop-button">サイトトップへ</button><!-- jQuery -->
    </div>
  <?php } ?>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.logout-button').on('click', function(){
    var deleteForm = jQuery('form#logout-form');
    deleteForm.submit();
  });
});
</script>
</body>
</html>
