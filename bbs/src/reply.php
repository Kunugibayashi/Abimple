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

$inputParams['replyid'] = inputParam('replyid', 20);
$inputParams['fromname'] = inputParam('fromname', 20);
$inputParams['title'] = inputParam('title', 100);
$inputParams['message'] = inputParam('message', 10000);
$inputParams['image'] = inputParam('image', 200);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $replyArticles = selectBbsId($dbhBbs, $inputParams['replyid']);
  $replyArticle = $replyArticles[0];

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
$dbhBbsId = connectRw(BBSID_DB);
$dbhBbs = connectRw(BBS_DB);

$replyArticles = selectBbsId($dbhBbs, $inputParams['replyid']);
$replyArticle = $replyArticles[0];

// 画像指定がある場合は画像処理を行う
$imageFile = '';
if (is_uploaded_file($_FILES['image']['tmp_name'])) {
  list($errors, $uploadImageFile) = fileUpload();
  $imageFile = $uploadImageFile;
}
if (usedArr($errors)) {
  goto outputPage;
}

// 記事登録
$result = insertBbs($dbhBbs, getUserid(), getUsername(), [
  'parentid' => $replyArticle['parentid'],
  'depth' => ($replyArticle['depth'] + 1),
  'toid' => $replyArticle['id'],
  'touserid' => $replyArticle['userid'],
  'tousername' => $replyArticle['username'],
  'toname' => $replyArticle['fromname'],
  'totitle' => $replyArticle['title'],
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
  <title>返信</title>
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
  <h3 class="frame-title">返信</h3>

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
      <form name="user-form" class="user-form" action="./reply.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
        <input type="hidden" name="replyid" value="<?php echo h($inputParams['replyid']); ?>">
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
          <li class="form-col-item">
            <input type="file" name="image" accept=".jpg, .gif, .png, image/gif, image/jpeg, image/png">
          </li>
          <li class="form-col-note">ファイルサイズ 10MB 以内</li>
        </ul>
        <div class="form-button-wrap">
          <button type="submit">返信</button>
        </div>
      </form>
    </div>

    <div class="note-wrap">
      <p class="note">
        以下の記事に返信します。<br>
      </p>
    </div>

    <?php if (usedArr($replyArticle)) { /* 登録がある場合に表示 */ ?>
      <div class="view-wrap bbs-parent-wrap">
        <div class="view-contents" id="id-<?php echo h($replyArticle['id']); ?>">
          <ul class="view-row">
            <li class="view-col-title">宛先</li>
            <li class="view-col-item"><?php echo h($replyArticle['toid']); ?></li>
            <li class="view-col-item"><?php echo h($replyArticle['toname']); ?></li>
            <li class="view-col-item"><?php echo h($replyArticle['totitle']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">記事ID</li>
            <li class="view-col-item"><?php echo h($replyArticle['id']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">作成者</li>
            <li class="view-col-item"><?php echo h($replyArticle['fromname']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">作成日</li>
            <li class="view-col-item"><?php echo h($replyArticle['created']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">更新日</li>
            <li class="view-col-item"><?php echo h($replyArticle['modified']); ?><?php echo h($replyArticle['created'] === $replyArticle['modified'] ? '' : '（編集済）') ?></li>
          </ul>
          <ul class="view-row view-message-row">
            <li class="view-col-title view-message-title"><?php echo h($replyArticle['title']); ?></li>
            <li class="view-col-item view-message-item"><?php echo ht($replyArticle['message']); ?></li>
          </ul>
          <?php if (usedStr($replyArticle['image'])) { ?>
            <ul class="view-row view-image-row">
              <li class="view-col-item view-image-item">
                <a href="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($replyArticle['image']); ?>" target="_blank"><img src="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($replyArticle['image']); ?>" height="100"></a>
              </li>
            </ul>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

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
});
</script>
</body>
</html>
