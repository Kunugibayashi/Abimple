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

$inputParams['deleteid'] = inputParam('deleteid', 20);
$inputParams['fromname'] = inputParam('fromname', 20);
$inputParams['title'] = inputParam('title', 100);
$inputParams['message'] = inputParam('message', 10000);
$inputParams['image'] = inputParam('image', 200);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $deleteArticles = selectBbsId($dbhBbs, $inputParams['deleteid']);
  $deleteArticle = $deleteArticles[0];

  // データ更新
  $inputParams = $deleteArticle;
  $inputParams['deleteid'] = inputParam('deleteid', 20);

  // 本人確認
  identityUser($deleteArticle['userid'], $deleteArticle['username']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhBbs = connectRw(BBS_DB);

$deleteArticles = selectBbsId($dbhBbs, $inputParams['deleteid']);
$deleteArticle = $deleteArticles[0];

// データ更新
$inputParams = $deleteArticle;
$inputParams['deleteid'] = inputParam('deleteid', 20);

// 本人確認
identityUser($deleteArticle['userid'], $deleteArticle['username']);

// 画像指定がある場合は画像処理を行う
if ($deleteArticle['image']) {
  // 画像削除
  $result = unlink(IMAGE_SAVE_PATH.$deleteArticle['image']);
  if (!$result) {
    $errors[] = 'ファイル削除中にエラーが発生しました。もう一度お試しください。';
  }
}
if (usedArr($errors)) {
  goto outputPage;
}

$result = deleteBbsId($dbhBbs, $inputParams['deleteid']);
if (!$result) {
 $errors[] = '削除に失敗しました。もう一度お試しください。';
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
  <title>掲示板記事トップ</title>
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
  <h3 class="frame-title">掲示板記事トップ</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての記事の削除が可能</span>です。<br>
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

  <?php if (!usedStr($success) && usedArr($inputParams) && usedStr($inputParams['id'])) { /* 処理が成功でない & データがある場合は表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        <span class="point"><?php echo h($inputParams['id']); ?>：<?php echo h($inputParams['title']); ?></span> を削除します。<br>
      </p>
      <p class="note">
        よろしいですか？<br>
      </p>
    </div>
    <div class="view-wrap bbs-parent-wrap">
      <div class="view-contents" id="id-<?php echo h($inputParams['id']); ?>">
        <ul class="view-row">
          <li class="view-col-title">宛先</li>
          <li class="view-col-item"><?php echo h($inputParams['toid']); ?></li>
          <li class="view-col-item"><?php echo h($inputParams['toname']); ?></li>
          <li class="view-col-item"><?php echo h($inputParams['totitle']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">記事ID</li>
          <li class="view-col-item"><?php echo h($inputParams['id']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">作成者</li>
          <li class="view-col-item"><?php echo h($inputParams['fromname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">作成日</li>
          <li class="view-col-item"><?php echo h($inputParams['created']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">更新日</li>
          <li class="view-col-item"><?php echo h($inputParams['modified']); ?><?php echo h($inputParams['created'] === $inputParams['modified'] ? '' : '（編集済）') ?></li>
        </ul>
        <ul class="view-row view-message-row">
          <li class="view-col-title view-message-title"><?php echo h($inputParams['title']); ?></li>
          <li class="view-col-item view-message-item"><?php echo ht($inputParams['message']); ?></li>
        </ul>
        <?php if (usedStr($inputParams['image'])) { ?>
          <ul class="view-row view-image-row">
            <li class="view-col-item view-image-item">
              <a href="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($inputParams['image']); ?>" target="_blank"><img src="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($inputParams['image']); ?>" height="100"></a>
            </li>
          </ul>
        <?php } ?>
      </div>
    </div>
    <div class="page-button-wrap">
      <button type="button" class="warning delete-button">はい</button>
    </div>
    <form id="delete-form" class="hidden-form" action="./delete.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <input type="hidden" name="deleteid" value="<?php echo h($deleteArticle['id']); ?>">
    </form>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">トップに戻る</button>
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
    window.location.href = "<?php echo h(getTopPrev()); ?>";
  });
});
</script>
</body>
</html>
