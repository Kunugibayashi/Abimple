<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

$success = '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // テーブル絞り込み条件
  $paramsDb = array();

  // DB接続
  $dbhUsers = connectRo(USERS_DB);

  $users = selectUserMyList($dbhUsers, getUserid(), getUsername());
  if (!usedArr($users)) {
    $errors[] = '登録がありません。';
    goto outputPage;
  }
  if (count($users) !== 1) {
    $errors[] = '登録データに不備があります。';
    goto outputPage;
  }
  $user = $users[0];

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
  <title>ユーザー管理</title>
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
  <h3 class="frame-title">ユーザー管理</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">管理者以外のすべてのユーザーの「削除」が可能</span>です。<br>
      </p>
    </div>
  <?php } ?>

  <div class="note-wrap">
    <p class="note">
      ユーザーは「削除」のみ可能です。<br>
    </p>
  </div>

  <?php if (usedArr($user) && usedStr($user['id'])) { /* データがある場合は表示 */ ?>
    <div class="table-wrap user-table-wrap">
      <table>
        <tr>
          <th class="cell-action">操作</th>
          <th class="cell-id">id</th>
          <th class="cell-username">ユーザー名</th>
          <th class="cell-created">作成日</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <tr>
          <td>
            <button type="button" class="warning delete-button" value="<?php echo h($user['id']); ?>">削除</button>
          </td>
          <td><?php echo h($user['id']); ?></td>
          <td><?php echo h($user['username']); ?></td>
          <td><?php echo h($user['created']); ?></td>
          <td><?php echo h($user['modified']); ?></td>
        </tr>
      </table>
    </div>
  <?php } ?>

  <form id="delete-form" class="hidden-form" action="./delete.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.delete-button').on('click', function(){
    var userid = jQuery(this).val();
    var deleteForm = jQuery('form#delete-form');
    deleteForm.find('input[name="id"]').val(userid);
    deleteForm.submit();
  });
});
</script>
</body>
</html>
