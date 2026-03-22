<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

loginOnly();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhOutbox = connectRw(OUTBOX_LETTERS_DB);

  $outbox = selectOutboxLettersMy($dbhOutbox, getUserid(), getUsername());

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
  <title>私書管理</title>
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
  <h3 class="outbox-title">送信箱</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
  <?php } ?>

  <div class="note-wrap">
    <p class="note">
      私書機能は完全な秘匿ではなく、管理者が確認することが可能です。<br>
      個人情報など重要な情報は書き込まないでください。<br>
    </p>
  </div>

  <?php if (!usedArr($outbox)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($outbox)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap my-outbox-table-wrap">
      <div class="export-wrap">
        <a href="<?php echo h(LETTER_SRC_LINK); ?>outboxexport.php" class="link-pseudo-button">一括DL</a>
      </div>
      <table>
        <tr>
          <th class="cell-modified">更新日</th>
          <th class="cell-status">ステータス</th>
          <th>宛先</th>
          <th>差出人</th>
          <th>タイトル</th>
        </tr>
        <?php foreach ($outbox as $key => $value) { ?>
          <tr>
              <td><?php echo h($value['modified']); ?></td>
              <td><?php echo h($value['status']); ?></td>
              <td><?php echo h($value['tofullname']); ?></td>
              <td><?php echo h($value['fromfullname']); ?></td>
              <td><a href="<?php echo h(LETTER_SEC_LINK); ?>outboxview.php?id=<?php echo h($value['id']); ?>"><?php echo h($value['title']); ?></a></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

</body>
</html>
