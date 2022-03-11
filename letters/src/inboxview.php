<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

$inputParams['id'] = inputParam('id', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhInbox = connectRw(INBOX_DB);

  $inbox = selectInboxMessageList($dbhInbox, [
    'id' => $inputParams['id'],
  ]);
  $letter = $inbox[0];

  // 本人確認
  identityUser($letter['touserid'], $letter['tousername']);

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
      </p>
    </div>
  <?php } ?>

  <?php if (!usedArr($letter)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($letter)) { /* 登録がある場合に表示 */ ?>
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
          <li class="view-col-item  view-message-item"><?php echo h($letter['message']); ?></li>
        </ul>
      </div>
    </div>
    <div class="page-button-wrap">
      <button type="button" class="letter-button">返信</button>
      <button type="button" class="warning delete-button">削除</button>
    </div>

    <form id="letter-form" class="hidden-form" action="./send.php" method="GET">
      <input type="hidden" name="tocharacterid" value="<?php echo h($letter['fromcharacterid']); ?>">
      <input type="hidden" name="fromcharacterid" value="<?php echo h($letter['tocharacterid']); ?>">
    </form>
    <form id="delete-form" class="hidden-form" action="./inboxdelete.php" method="GET">
      <input type="hidden" name="deleteid" value="<?php echo h($letter['id']); ?>">
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
  jQuery('button.letter-button').on('click', function(){
    var letterForm = jQuery('form#letter-form');
    letterForm.submit();
  });
  jQuery('button.delete-button').on('click', function(){
    var deleteForm = jQuery('form#delete-form');
    deleteForm.submit();
  });
});
</script>
</html>
