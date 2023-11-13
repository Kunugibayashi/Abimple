<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhInouthistory = connectRo(ROOM_INOUT_HISTORIES_DB);

  $inouthistoryList = selectRoominouthistories($dbhInouthistory, 100);

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
  <title>チャットルーム入退室履歴</title>
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
  <h3 class="inouthistory-title">チャットルーム入退室履歴</h4>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
    <div class="note-wrap">
      <p class="note">
        入退室履歴は「削除」のみ可能です。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (!usedArr($inouthistoryList)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($inouthistoryList)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap inouthistory-table-wrap">
      <table>
        <tr>
          <?php if (isAdmin()) { ?>
            <th class="cell-action">操作</th>
          <?php } ?>
          <th class="cell-inoutdate">日付</th>
          <th class="cell-inoutroomtitle">ルーム</th>
          <th class="cell-inoutmessage">入退室</th>
        </tr>
        <?php foreach ($inouthistoryList as $key => $value) { ?>
          <tr>
            <?php if (isAdmin()) { ?>
              <td>
                <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
              </td>
            <?php } ?>
            <td><?php echo ht($value['modified']); ?></td>
            <td><?php echo h($value['roomtitle']); ?></td>
            <td><?php echo ht($value['message']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <form id="delete-form" class="hidden-form" action="./inouthistorydelete.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.delete-button').on('click', function(){
    var id = jQuery(this).val();
    var deleteForm = jQuery('form#delete-form');
    deleteForm.find('input[name="id"]').val(id);
    deleteForm.submit();
  });
});
</script>
</body>
</html>
