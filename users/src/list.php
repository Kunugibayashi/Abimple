<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();
adminOnly();

$searchParams = array();

$searchParams['id'] = searchParam('id', SEARCHKEY_LIMIT);
$searchParams['username'] = searchParam('username', SEARCHKEY_LIMIT);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // ページングでない場合はパラメータリセット
  if (!usedStr(getParam('page'))) {
    foreach ($searchParams as $key => $value) {
      $searchParams[$key] = '';
    }
    setSearchParam($searchParams);
  }

  // DB接続
  $dbhUsers = connectRo(USERS_DB);
  $users = selectUserList($dbhUsers, $searchParams);

  $pages = splitPages($users, getNowPage());

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 検索値更新
$searchParams['id'] = inputParam('id', SEARCHKEY_LIMIT);
$searchParams['username'] = inputParam('username', SEARCHKEY_LIMIT);
setSearchParam($searchParams);

// DB接続
$dbhUsers = connectRo(USERS_DB);

$users = selectUserList($dbhUsers, $searchParams);
$pages = splitPages($users, getNowPage());


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
  <title>ユーザー一覧</title>
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
  <h3 class="frame-title">ユーザー一覧</h3>

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

  <div class="search-wrap">
    <form name="search-form" class="search-form" action="?page=1<?php // 戻る処理のため検索は明示的に1を指定 ?>" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <div class="search-key-wrap">
        <ul class="search-row">
          <li class="search-col-title">id</li>
          <li class="search-col-item"><input type="text" name="id" value="<?php echo h($searchParams['id']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
        <ul class="search-row">
          <li class="search-col-title">ユーザー名</li>
          <li class="search-col-item"><input type="text" name="username" value="<?php echo h($searchParams['username']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
      </div>
      <div class="search-button-wrap">
        <button type="submit" class="search-and-button">AND検索</button>
      </div>
    </form>
  </div>

  <?php if (!usedArr($pages)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <div class="paging-wrap">
    <?php outputPaging($users, getNowPage()); ?>
  </div>
  <div class="sumpaging-wrap">
    <?php outputSumPaging($users, getNowPage()); ?>
  </div>
  <?php if (usedArr($pages)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap user-table-wrap">
      <table>
        <tr>
          <th class="cell-action">操作</th>
          <th class="cell-id">id</th>
          <th class="cell-username">ユーザー名</th>
          <th class="cell-created">作成日</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <?php foreach ($pages as $key => $value) { ?>
          <tr>
            <td>
              <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
            </td>
            <td><?php echo h($value['id']); ?></td>
            <td><?php echo h($value['username']); ?></td>
            <td><?php echo h($value['created']); ?></td>
            <td><?php echo h($value['modified']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
    <div class="paging-wrap">
      <?php outputPaging($users, getNowPage()); ?>
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
