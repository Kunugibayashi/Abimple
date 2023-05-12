<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

$success = '';
$errors = array();
$inputParams['id'] = inputParam('id', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhOutbox = connectRw(OUTBOX_LETTERS_DB);

  $outbox = selectOutboxMessageId($dbhOutbox, $inputParams['id']);
  $letter = $outbox[0];

  // 本人確認
  identityUser($letter['userid'], $letter['username']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhOutbox = connectRw(OUTBOX_LETTERS_DB);

$outbox = selectOutboxMessageId($dbhOutbox, $inputParams['id']);
$letter = $outbox[0];

// 本人確認
identityUser($letter['userid'], $letter['username']);

// ユーザー登録削除
$result = deleteOutboxLetters($dbhOutbox, $inputParams['id']);
if (!$result) {
  $errors[] = '私書削除に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = '削除しました。';

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
  <title>私書詳細</title>
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
  <h3 class="frame-title">私書詳細</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての私書の「削除」が可能</span>です。<br>
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

  <?php if (!usedArr($letter)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (!usedStr($success) && !usedArr($errors)) { /* 成功でもエラーでもない場合にフォームを表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        以下の私書を削除します。<br>
      </p>
      <p class="note">
        送信箱から削除しても、相手の受信箱から削除はされません。<br>
        <span class="point">削除されたデータは復元できません。</span><br>
      </p>
      <p class="note">
        よろしいですか？<br>
      </p>
    </div>

    <form id="delete-form" class="hidden-form" action="./delete.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <input type="hidden" name="id" value="<?php echo h($inputParams['id']); ?>">
    </form>

    <div class="page-button-wrap">
      <button type="button" class="warning delete-button">はい</button>
    </div>

    <div class="view-wrap letters-view-wrap">
      <div class="view-contents">
        <ul class="view-row">
          <li class="view-col-title">宛先</li>
          <li class="view-col-item"><?php echo h($letter['tofullname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">差出人</li>
          <li class="view-col-item"><?php echo h($letter['fromfullname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">日付</li>
          <li class="view-col-item"><?php echo h($letter['modified']); ?></li>
        </ul>
        <ul class="view-row view-message-row">
          <li class="view-col-title view-message-title"><?php echo h($letter['title']); ?></li>
          <li class="view-col-item  view-message-item"><?php echo hb($letter['message']); ?></li>
        </ul>
      </div>
    </div>
    <form id="delete-form" class="hidden-form" action="./outboxdelete.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <input type="hidden" name="id" value="<?php echo h($inputParams['id']); ?>">
    </form>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
  </div>

</body>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getPrev()); ?>";
  });
  jQuery('button.delete-button').on('click', function(){
    var deleteForm = jQuery('form#delete-form');
    deleteForm.submit();
  });
});
</script>
</html>
