<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

adminOnly();

$success = '';
$errors = array();
$inputParams = array();

$inputParams['id'] = inputParam('id', 20);
$inputParams['roomdir'] = inputParam('roomdir', 20);
$inputParams['roomtitle'] = inputParam('roomtitle', 100);
$inputParams['published'] = inputParam('published', 1);
$inputParams['displayno'] = inputParam('displayno', 10000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhAdminroom = connectRw(ADMIN_ROOMS_DB);

  $adminroomList = selectAdminroomsId($dbhAdminroom, $inputParams['id']);
  $adminroom = $adminroomList[0];

  // データ更新
  $inputParams = $adminroom;

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 入力値チェック
if (!usedStr($inputParams['roomdir'])) {
  $errors[] = 'roomdir を入力してください。';
}
if ($inputParams['roomdir'] === 'default') {
  $errors[] = 'default は使用できません。';
}
if (preg_match('/[^A-Za-z0-9]/', $inputParams['roomdir'])) {
  $errors[] = 'roomdir に使用できるのは数字とアルファベットのみです。';
}
if (!usedStr($inputParams['roomtitle'])) {
  $errors[] = 'ルーム名を入力してください。';
}
if (!usedStr($inputParams['displayno'])) {
  $errors[] = '表示順序を入力してください。';
}
if (preg_match('/[^0-9]/', $inputParams['displayno'])) {
  $errors[] = '表示順序に使用できるのは数字のみです。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// DB接続
$dbhAdminroom = connectRw(ADMIN_ROOMS_DB);

$adminroomList = selectAdminroomsId($dbhAdminroom, $inputParams['id']);
$adminroom = $adminroomList[0];

$fromPath = './../rooms/'.$adminroom['roomdir'];
$toPath = './../rooms/'.$inputParams['roomdir'];

if (!rename($fromPath, $toPath)) {
  $errors[] = 'ルーム名の変更に失敗しました。もう一度お試しください。';
  goto outputPage;
}

// 登録
$result = updateAdminrooms($dbhAdminroom, $inputParams['id'], $inputParams);
if (!$result) {
  $errors[] = '登録に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = '編集しました。';


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
  <title>チャットルーム編集</title>
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
  <h3 class="frame-title">チャットルーム編集</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        roomdir を変更するとチャットルームの URL が変更されます。入室者がいる場合、<span class="point">入室者側でエラーが発生する</span>ためご注意ください。<br>
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

  <?php if (!usedStr($success)) { /* 成功以外にフォームを表示 */ ?>
    <div class="form-wrap">
      <form name="user-form" class="user-form" action="./edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
        <input type="hidden" name="id" value="<?php echo h($inputParams['id']); ?>">
        <ul class="form-row">
          <li class="form-col-title">roomdir<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="roomdir" value="<?php echo h($inputParams['roomdir']); ?>" maxlength="20"></li>
          <li class="form-col-note">最大 20 文字まで。チャットルームの URL に使用されます。</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">ルーム名<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="roomtitle" value="<?php echo h($inputParams['roomtitle']); ?>" maxlength="100"></li>
          <li class="form-col-note">最大 100 文字まで。一覧の部屋タイトルに使用されます。</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">一覧に表示するか<div class="mandatory-mark"></div></li>
          <li class="form-col-item">
            <div class="select-wrap">
              <select name="published">
                <option <?php echo selectedOption($inputParams['published'], '0'); ?> value="0">表示しない</option>
                <option <?php echo selectedOption($inputParams['published'], '1'); ?> value="1">表示する</option>
              </select>
            </div>
          </li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">表示順序<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="displayno" value="<?php echo h($inputParams['displayno']); ?>" maxlength="10000"></li>
          <li class="form-col-note">最大 10000。小さい番号ほど上に表示されます。</li>
        </ul>
        <div class="form-button-wrap">
          <button type="submit">登録</button>
        </div>
      </form>
    </div>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
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
