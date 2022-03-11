<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

letterPublicOnly();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhInbox = connectRw(INBOX_DB);

  $letters = selectInboxPublicTitleList($dbhInbox);

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
  <title>公開私書一覧</title>
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
  <h3 class="frame-title">公開私書一覧</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての私書の「削除」が可能</span>です。<br>
        私書を削除する場合は、詳細画面から「削除」を選択してください。<br>
      </p>
    </div>
  <?php } ?>

  <div class="note-wrap">
    <p class="note">
      公開私書箱は受信箱のみを表示しています。<br>
    </p>
  </div>

  <?php if (!usedArr($letters)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <div class="paging-wrap">
    <?php outputPaging($letters, getNowPage()); ?>
  </div>
  <div class="sumpaging-wrap">
    <?php outputSumPaging($letters, getNowPage()); ?>
  </div>
  <?php if (usedArr($letters)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap public-inbox-table-wrap">
      <table>
        <tr>
          <th class="cell-id">宛先ID</th>
          <th class="cell-fullname">宛先</th>
          <th class="cell-fullname">差出人</th>
          <th class="cell-title">タイトル</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <?php foreach ($letters as $key => $value) { ?>
          <tr>
              <td><?php echo h($value['tocharacterid']); ?></td>
              <td><?php echo h($value['tofullname']); ?></td>
              <td><?php echo h($value['fromfullname']); ?></td>
              <td><a href="./publicview.php?tocharacterid=<?php echo h($value['tocharacterid']); ?>"><?php echo h($value['title']); ?></a></td>
              <td><?php echo h($value['modified']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
    <div class="paging-wrap">
      <?php outputPaging($letters, getNowPage()); ?>
    </div>
  <?php } ?>

</body>
</html>
