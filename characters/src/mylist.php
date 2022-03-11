<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectCharacterMyList($dbhCharacters, getUserid(), getUsername());

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
  <title>名簿一覧</title>
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
  <h3 class="frame-title">名簿一覧</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (!usedArr($characters)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($characters)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap my-namelist-table-wrap">
      <table>
        <tr>
          <th class="cell-action">操作</th>
          <th class="cell-id"><?php echo h(NAMELIST_ID); ?></th>
          <th class="cell-fullname"><?php echo h(NAMELIST_NAME); ?></th>
          <th class="cell-color"><?php echo h(NAMELIST_COLOR); ?></th>
          <th class="cell-bgcolor"><?php echo h(NAMELIST_BGCOLOR); ?></th>
          <?php if (NAMELIST_COMMENT) { ?>
            <th class="cell-comment"><?php echo h(NAMELIST_COMMENT_NAME); ?></th>
          <?php } ?>
          <th class="cell-created">作成日</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <?php foreach ($characters as $key => $value) { ?>
          <tr>
              <td>
                <button type="button" class="edit-button" value="<?php echo h($value['id']); ?>">編集</button>
                <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
              </td>
            <td><?php echo h($value['id']); ?></td>
            <td><a class="character-view-link" href="./view.php?id=<?php echo h($value['id']); ?>"><?php echo h($value['fullname']); ?><a></td>
            <td><span style="color: <?php echo h($value['color']); ?>; "><?php echo h($value['color']); ?></span></td>
            <td><span style="color: <?php echo h($value['bgcolor']); ?>; "><?php echo h($value['bgcolor']); ?></span></td>
            <?php if (NAMELIST_COMMENT) { ?>
              <td><?php echo h($value['comment']); ?></td>
            <?php } ?>
            <td><?php echo h($value['created']); ?></td>
            <td><?php echo h($value['modified']); ?></td>
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

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.edit-button').on('click', function(){
    var characterid = jQuery(this).val();
    var editForm = jQuery('form#edit-form');
    editForm.find('input[name="id"]').val(characterid);
    editForm.submit();
  });
  jQuery('button.delete-button').on('click', function(){
    var characterid = jQuery(this).val();
    var deleteForm = jQuery('form#delete-form');
    deleteForm.find('input[name="id"]').val(characterid);
    deleteForm.submit();
  });
});
</script>
</body>
</html>
