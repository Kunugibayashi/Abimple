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

$inputParams['editid'] = inputParam('editid', 20);
$inputParams['fromname'] = inputParam('fromname', 20);
$inputParams['title'] = inputParam('title', 100);
$inputParams['message'] = inputParam('message', 10000);
$inputParams['image'] = inputParam('image', 200);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $editArticles = selectBbsId($dbhBbs, $inputParams['editid']);
  $editArticle = $editArticles[0];

  // データ更新
  $inputParams = $editArticle;
  $inputParams['editid'] = inputParam('editid', 20);

  // 本人確認
  identityUser($inputParams['userid'], $inputParams['username']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 入力値チェック
if (!usedStr($inputParams['fromname'])) {
  $errors[] = '名前を入力してください。';
}
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
$dbhBbsId = connectRw(BBS_PARENTS_DB);
$dbhBbs = connectRw(BBS_DB);

$editArticles = selectBbsId($dbhBbs, $inputParams['editid']);
$editArticle = $editArticles[0];

// 本人確認
identityUser($editArticle['userid'], $editArticle['username']);

// 画像指定がある場合は画像処理を行う
$imageFile = '';
if (is_uploaded_file($_FILES['image']['tmp_name'])) {
  // 画像削除
  $result = unlink(IMAGE_SAVE_PATH.$editArticle['image']);
  if (!$result) {
    $errors[] = 'ファイル削除中にエラーが発生しました。もう一度お試しください。';
  }

  // 画像アップロード
  list($errors, $uploadImageFile) = fileUpload();
  $imageFile = $uploadImageFile;
}
if (usedArr($errors)) {
  goto outputPage;
}

// 記事登録
$result = updateBbs($dbhBbs, $inputParams['editid'], [
  'fromname' => $inputParams['fromname'],
  'title' => $inputParams['title'],
  'message' => $inputParams['message'],
  'image' => $imageFile,
]);

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
  <title>編集</title>
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
  <h3 class="frame-title">編集</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての記事の「編集」が可能</span>です。<br>
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
        <input type="hidden" name="editid" value="<?php echo h($inputParams['editid']); ?>">
        <ul class="form-row">
          <li class="form-col-title">記事ID</li>
          <li class="form-col-item"><?php echo h($inputParams['editid']); ?></li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">名前<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="fromname" value="<?php echo h($inputParams['fromname']); ?>" maxlength="20"></li>
          <li class="form-col-note">最大 20 文字まで</li>
        </ul>
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
        <ul class="form-row">
          <li class="form-col-title">画像ファイル<div class="optional-mark"></div></li>
          <?php if (usedStr($inputParams['image'])) { ?>
            <li class="view-col-item" id="id-uploaded-image">
              <a href="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($inputParams['image']); ?>" target="_blank"><img src="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($inputParams['image']); ?>" height="100"></a>
            </li>
          <?php } ?>
          <li class="form-col-item">
            <input type="file" name="image" accept=".jpg, .gif, .png, image/gif, image/jpeg, image/png">
          </li>
          <li class="form-col-note">ファイルサイズ 10MB 以内</li>
        </ul>
        <div class="form-button-wrap">
          <button type="submit">編集</button>
        </div>
      </form>
    </div>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">トップに戻る</button>
  </div>

<script> <!-- 各ボタン制御 -->
jQuery(function(){
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getTopPrev()); ?>";
  });
  jQuery('input[name="image"]').on('change', function() {
    jQuery('#id-uploaded-image').html('');
  });
});
</script>
</body>
</html>
