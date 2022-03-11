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
$inputParams['title'] = inputParam('title', 100);
$inputParams['message'] = inputParam('message', 10000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhInfomation = connectRw(INFOMATION_DB);

  $infoList = selectInfomationMessage($dbhInfomation, $inputParams['id']);
  $info = $infoList[0];

  // データ更新
  $inputParams = $info;

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 入力値チェック
if (!usedStr($inputParams['title'])) {
  $errors[] = 'タイトルを入力してください。';
}
if (!usedStr($inputParams['message'])) {
  $errors[] = 'メッセージを入力してください。';
}
if (usedStr($inputParams['message']) && mb_strlen($inputParams['message']) > 10000) {
  $errors[] = 'メッセージは最大 10000 文字です。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// DB接続
$dbhInfomation = connectRw(INFOMATION_DB);

// 登録
$result = updateInfomation($dbhInfomation, $inputParams['id'], $inputParams);
if (!$result) {
  $errors[] = '登録に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = '投稿しました。';


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
  <title>お知らせ登録</title>
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
  <h3 class="frame-title">お知らせ登録</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
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
          <li class="form-col-title">タイトル<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="title" value="<?php echo h($inputParams['title']); ?>" maxlength="100"></li>
          <li class="form-col-note">最大 100 文字まで</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">メッセージ<div class="mandatory-mark"></div><div class="htmltag-mark"></div></li>
          <li class="form-col-item"><textarea name="message" maxlength="10000"><?php echo h($inputParams['message']); ?></textarea></li>
          <li class="form-col-note">最大 10000 文字。<a href="../../manual/src/htmltag.php" target="_blank">使用可能なHTMLタグについてはこちら。</a></li>
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
