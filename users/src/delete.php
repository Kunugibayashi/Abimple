<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

$success = '';
$errors = array();
$inputParams = array();
$user = array();

// 初期ページは指定されたidから
$inputParams['id'] = getParam('id');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhUsers = connectRo(USERS_DB);

  $users = selectUsersId($dbhUsers, $inputParams['id']);
  if (!usedArr($users)) {
    $errors[] = 'ユーザーが存在しません。';
    goto outputPage;
  }
  $user = $users[0];

  // 本人確認
  identityUser($user['id'], $user['username']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

if (isAdmin()) {
  // 管理者ユーザーの場合はinputから
  $userid = inputParam('id', 20);
} else {
  // 一般ユーザーの場合
  // 不正防止のため、削除するユーザーは必ずセッションから取得
  $userid = getUserid();
}

// DB接続
$dbhUsers = connectRw(USERS_DB);
$dbhCharacters = connectRw(CHARACTERS_DB);

$users = selectUsersId($dbhUsers, $userid);
if (!usedArr($users)) {
  $errors[] = 'ユーザーが存在しません。';
  goto outputPage;
}
$user = $users[0];

// 本人確認
identityUser($user['id'], $user['username']);

// 最新のデータに更新
$userid = $user['id'];
$username = $user['username'];

if ($username === ADMIN_USERNAME) {
  // サイト管理ができなくなるため、管理者ユーザーは削除不可
  $errors[] = '管理者ユーザーは削除できません。';
  goto outputPage;
}

/* 削除するDB順序。
 * ・キャラクターDB
 * ・ユーザーDB
 */
// キャラクター登録削除
$result = deleteEqualCharacters($dbhCharacters, $userid, $username);
if (!$result) {
  $errors[] = '名簿削除に失敗しました。もう一度お試しください。';
  goto outputPage;
}

// ユーザー登録削除
$result = deleteUsers($dbhUsers, $userid, $username);
if (!$result) {
  $errors[] = 'ユーザー削除に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = '削除しました。';
/* セッション切断を行うことが正しいが、一般ユーザーが自分自身を削除した場合、
 * 本人確認でエラーになってしまい、処理が終わったように見えないため、
 * ここではセッション切断を行わない。
 */


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
  <title>ユーザー削除</title>
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
  <h3 class="frame-title">ユーザー削除確認</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">管理者以外のすべてのユーザーの「削除」が可能</span>です。<br>
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

  <?php if (!usedStr($success) && !usedArr($errors)) { /* 成功でもエラーでもない場合にフォームを表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        <span class="point"><?php echo h($user['id']); ?>：<?php echo h($user['username']); ?></span> を削除します。<br>
      </p>
      <p class="note">
        以下を確認してください。<br>
      </p>
      <p class="note">
        削除を実行した場合、このユーザーで作成した名簿が同時に削除されます。<br>
        <span class="point">削除されたデータは復元できません。</span><br>
      </p>
      <p class="note">
        私書、チャットルーム、ログ保管庫のログは削除されません。<br>
        管理人以外に、このユーザーで作成したデータの<span class="point">編集・削除ができなくなります。</span><br>
      </p>
      <p class="note">
        よろしいですか？<br>
      </p>
    </div>
    <div class="page-button-wrap">
      <button type="button" class="warning delete-button">はい</button>
    </div>
    <form id="delete-form" class="hidden-form" action="./delete.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <input type="hidden" name="id" value="<?php echo h($inputParams['id']); ?>">
    </form>
  <?php } ?>

  <?php if (usedStr($success) || usedArr($errors)) { /* 成功かエラーが起こった場合 */ ?>
    <div class="page-back-wrap">
      <?php if (isAdmin()) { /* 管理ユーザーのみ表示 */ ?>
        <button type="button" class="tolist-button">一覧に戻る</button>
      <?php } ?>
      <button type="button" class="sitetop-button">サイトトップへ</button><!-- jQuery -->
    </div>
  <?php } ?>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.delete-button').on('click', function(){
    var deleteForm = jQuery('form#delete-form');
    deleteForm.submit();
  });
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getPrev()); ?>";
  });
});
</script>
</body>
</html>
