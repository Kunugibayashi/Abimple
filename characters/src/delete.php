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

  if (!usedStr($inputParams['id'])) {
    $errors[] = 'キャラクターIDが不正です。';
    goto outputPage;
  }

  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectCharactersId($dbhCharacters, $inputParams['id']);
  if (!usedArr($characters)) {
    $errors[] = 'キャラクターが存在しません。';
    goto outputPage;
  }
  $character = $characters[0];

  // 本人確認
  identityUser($character['userid'], $character['username']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// POST 通信から id を取得
$inputParams['id'] = inputParam('id', 20);

if (!usedStr($inputParams['id'])) {
  $errors[] = 'キャラクターIDが不正です。';
  goto outputPage;
}

// DB接続
$dbhCharacters = connectRw(CHARACTERS_DB);

$characters = selectCharactersId($dbhCharacters, $inputParams['id']);
if (!usedArr($characters)) {
  $errors[] = 'キャラクターが存在しません。';
  goto outputPage;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

// 最新の情報で更新
$userid = $character['userid'];
$username = $character['username'];

// キャラクター登録削除
$result = deleteEqualCharacters($dbhCharacters, $userid, $username, [
  'id' => $inputParams['id']
]);
if (!$result) {
 $errors[] = '名簿削除に失敗しました。もう一度お試しください。';
 goto outputPage;
}

$success = '削除しました。';


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
        管理ユーザーは<span class="point">すべてのキャラクターの削除が可能</span>です。<br>
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

  <?php if (!usedStr($success) && usedArr($character) && usedStr($character['id'])) { /* 処理が成功でない & データがある場合は表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        <span class="point"><?php echo h($character['id']); ?>：<?php echo h($character['fullname']); ?></span> を削除します。<br>
      </p>
      <p class="note">
        私書、チャットルーム、ログ保管庫のログは削除されません。<br>
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

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
  </div>

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
