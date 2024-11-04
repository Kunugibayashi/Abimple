<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();
adminOnly();

$success = '';
$errors = array();
$inputParams = array();
$user = array();

// 初期ページは指定されたidから
$inputParams['id'] = inputParam('id', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhAllLogLists = connectRo(ALL_LOG_LISTS_DB);

  $logLists = selectAllLogListsId($dbhAllLogLists, $inputParams['id']);
  if (!usedArr($logLists)) {
    $errors[] = 'ログが存在しません。';
    goto outputPage;
  }
  $logfile = $logLists[0];

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhAllLogLists = connectRw(ALL_LOG_LISTS_DB);

$logLists = selectAllLogListsId($dbhAllLogLists, $inputParams['id']);
if (!usedArr($logLists)) {
  $errors[] = $inputParams['id'] .'：ログが存在しません。';
  goto outputPage;
}
$logfile = $logLists[0];

// ファイル削除
$filePath = (INDEX_ROOT. '/chatrooms/rooms/' .$logfile['roomdir'] .'/logs/' .$logfile['filename']);

// ファイルがない場合は削除処理をしない
if (file_exists($filePath)) {
  unlink($filePath);
} else {
  $success = $success .'ログファイルは削除済みです。';
}

if (file_exists($filePath)) {
  $errors[] = 'ログファイルの削除に失敗しました。';
  return $errors;
}

// DB削除
$result = deleteAllLogListsId($dbhAllLogLists, $inputParams['id']);
if (!$result) {
  $errors[] = '削除に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$success = $success .'一覧から表示情報を削除しました。';

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
  <title>ログ削除</title>
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
  <h3 class="frame-title">ログ削除確認</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">ログの「削除」のみが可能</span>です。<br>
        出力されたログファイルの内容を修正したい場合は chatrooms/rooms/《roomdir》/logs 配下のファイルを編集してください。<br>
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
        <span class="point"><?php echo h($logfile['id']); ?>：<?php echo h($logfile['filename']); ?></span> を削除します。<br>
      </p>
      <p class="note">
        以下を確認してください。<br>
      </p>
      <p class="note">
        削除を実行した場合、各ルーム内のログファイルが削除されます。<br>
        <span class="point">削除されたファイルは復元できません。</span><br>
      </p>
      <p class="note">
        「ファイル無し」が表示されているデータの場合、ログファイルは削除済みです。<br>
        表示情報のみ削除されます。<span class="point">削除された表示情報は復元できません。</span><br>
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
      <button type="button" class="tolist-button">一覧に戻る</button>
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
