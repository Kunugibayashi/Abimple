<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

adminOnly();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhAdminroom = connectRo(ADMIN_ROOMS_DB);

  $roomList = selectEqualAdminroomsList($dbhAdminroom);

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
  <title>チャットルーム一覧</title>
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
  <h3 class="adminroom-title">チャットルーム一覧</h4>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        ここではチャットルームの追加、削除、表示順序の変更を行うことができます。<br>
        その他の詳細設定は各ルームごとの設定画面から行ってください。<br>
      </p>
    </div>
  <?php } ?>

  <div class="adminroom-menu">
    <ul class="adminroom-menu-item-group">
      <li class="adminroom-menu-item"><a href="./signup.php">新規チャットルーム追加</a></li>
    </ul>
  </div>

  <?php if (!usedArr($roomList)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($roomList)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap adminroom-table-wrap">
      <table>
        <tr>
          <?php if (isAdmin()) { ?>
            <th class="cell-action">操作</th>
          <?php } ?>
          <th class="cell-roomdir">roomdir</th>
          <th class="cell-roomtitle">ルームタイトル</th>
          <th class="cell-published">一覧に表示するか</th>
          <th class="cell-displayno">順序</th>
          <th class="cell-roomlink"></th>
          <th class="cell-roomlink"></th>
        </tr>
        <?php foreach ($roomList as $key => $value) { ?>
          <tr>
            <?php if (isAdmin()) { ?>
              <td>
                <button type="button" class="edit-button" value="<?php echo h($value['id']); ?>">編集</button>
                <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
              </td>
            <?php } ?>
            <td><?php echo h($value['roomdir']); ?></td>
            <td><?php echo h($value['roomtitle']); ?></td>
            <td><?php if ($value['published']) { echo '表示する'; } else { echo '表示しない'; } ?></td>
            <td><?php echo h($value['displayno']); ?></td>
            <td><a href="./../rooms/<?php echo h($value['roomdir']); ?>/src/roomtop.php">トップへ</a></td>
            <td><a href="./../rooms/<?php echo h($value['roomdir']); ?>/src/admin.php">管理画面へ</a></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <form id="edit-form" class="hidden-form" action="./edit.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

  <form id="delete-form" class="hidden-form" action="./delete.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.edit-button').on('click', function(){
    var id = jQuery(this).val();
    var editForm = jQuery('form#edit-form');
    editForm.find('input[name="id"]').val(id);
    editForm.submit();
  });
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
