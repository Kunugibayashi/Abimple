<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

$inputParams['id'] = inputParam('id', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhInfomation = connectRw(INFOMATION_DB);

  $infoList = selectInfomationMessage($dbhInfomation, $inputParams['id']);
  $info = $infoList[0];

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
  <title>お知らせ</title>
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
  <h3 class="frame-title">お知らせ</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($info) && usedStr($info['id'])) { /* データがある場合は表示 */ ?>
    <div class="view-wrap infomation-view-wrap">
      <div class="view-contents">
        <ul class="view-row">
          <li class="view-col-title-only"><?php echo h($info['title']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">ID</li>
          <li class="view-col-item"><?php echo h($info['id']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">件名</li>
          <li class="view-col-item"><?php echo h($info['title']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">作成日</li>
          <li class="view-col-item"><?php echo h($info['created']); ?></li>
        </ul>
        <ul class="view-row view-detail-row">
          <li class="view-col-item view-detail-item"><?php echo ht($info['message']); ?></li>
        </ul>
      </div>
    <?php } ?>

    <div class="page-back-wrap">
      <button type="button" class="tolist-button">一覧に戻る</button>
    </div>

  </div>
</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getPrev()); ?>";
  });
});
</script>
</body>
</html>
