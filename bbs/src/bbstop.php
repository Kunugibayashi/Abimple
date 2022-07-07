<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['parentid'] = inputParam('parentid', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $parentArticles = selectBbsListParentid($dbhBbs, $inputParams['parentid']);
  if (usedArr($parentArticles)) {
    $parentArticle = $parentArticles[0];
  } else {
    $parentArticle = array();
  }

  $childArticles = selectBbsListChildParentid($dbhBbs, $inputParams['parentid']);

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
  <title>掲示板記事</title>
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
  <h3 class="frame-title">掲示板記事</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての記事の「編集」「削除」が可能</span>です。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedArr($parentArticle)) { /* 登録がある場合に表示 */ ?>
    <div class="view-wrap bbs-parent-wrap">
      <div class="view-contents">
        <ul class="view-row">
          <li class="view-col-title">記事ID</li>
          <li class="view-col-item"><?php echo h($parentArticle['id']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">作成者</li>
          <li class="view-col-item"><?php echo h($parentArticle['fromname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">作成日</li>
          <li class="view-col-item"><?php echo h($parentArticle['created']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title">更新日</li>
          <li class="view-col-item"><?php echo h($parentArticle['modified']); ?><?php echo h($parentArticle['created'] === $parentArticle['modified'] ? '' : '（編集済）') ?></li>
        </ul>
        <ul class="view-row view-message-row">
          <li class="view-col-title view-message-title"><?php echo h($parentArticle['title']); ?></li>
          <li class="view-col-item view-message-item"><?php echo ht($parentArticle['message']); ?></li>
        </ul>
        <?php if (usedStr($parentArticle['image'])) { ?>
          <ul class="view-row">
            <li class="view-col-item">
              <a href="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($parentArticle['image']); ?>" target="_blank"><img src="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($parentArticle['image']); ?>" height="100"></a>
            </li>
          </ul>
        <?php } ?>
        <ul class="view-row view-action-row">
          <?php if (isLogin()) { /* ログイン時のみ表示 */ ?>
            <li class="view-col-item"><a href="./reply.php?replyid=<?php echo h($parentArticle['id']); ?>">返信</a></li>
            <?php if (($parentArticle['userid'] === getUserid() && $parentArticle['username'] === getUsername()) || isAdmin()) { /* 本人か管理人の場合はメニューを表示 */ ?>
              <li class="view-col-item"><a href="./edit.php?editid=<?php echo h($parentArticle['id']); ?>">編集</a></li>
              <li class="view-col-item"><a href="./delete.php?deleteid=<?php echo h($parentArticle['id']); ?>">削除</a></li>
            <?php } ?>
          <?php } ?>
        </ul>
      </div>
    </div>
  <?php } else { ?>
    <div class="view-wrap bbs-parent-wrap">
      <div class="view-contents">
        （親記事は削除されています）
      </div>
    </div>
  <?php } ?>

  <?php if (usedArr($childArticles)) { /* 登録がある場合に表示 */ ?>
    <div class="view-wrap bbs-child-wrap">
      <?php foreach ($childArticles as $key => $value) { ?>
        <div class="view-contents" id="id-<?php echo h($value['id']); ?>">
          <?php if (usedStr($value['toid'])) { ?>
            <ul class="view-row">
              <li class="view-col-title">宛先</li>
              <li class="view-col-item"><?php echo h($value['toid']); ?>：<?php echo h($value['totitle']); ?>（<?php echo h($value['toname']); ?>）</li>
            </ul>
          <?php } ?>
          <ul class="view-row">
            <li class="view-col-title">記事ID</li>
            <li class="view-col-item"><?php echo h($value['id']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">作成者</li>
            <li class="view-col-item"><?php echo h($value['fromname']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">作成日</li>
            <li class="view-col-item"><?php echo h($value['created']); ?></li>
          </ul>
          <ul class="view-row">
            <li class="view-col-title">更新日</li>
            <li class="view-col-item"><?php echo h($value['modified']); ?><?php echo h($value['created'] === $value['modified'] ? '' : '（編集済）') ?></li>
          </ul>
          <ul class="view-row view-message-row">
            <li class="view-col-title view-message-title"><?php echo h($value['title']); ?></li>
            <li class="view-col-item view-message-item"><?php echo ht($value['message']); ?></li>
          </ul>
          <?php if (usedStr($value['image'])) { ?>
            <ul class="view-row">
              <li class="view-col-item">
                <a href="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($value['image']); ?>" target="_blank"><img src="<?php echo h(IMAGE_SAVE_PATH); ?><?php echo h($value['image']); ?>" height="100"></a>
              </li>
            </ul>
          <?php } ?>
          <ul class="view-row view-action-row">
            <?php if (isLogin()) { /* ログイン時のみ表示 */ ?>
              <li class="view-col-item"><a href="./reply.php?replyid=<?php echo h($value['id']); ?>">返信</a></li>
              <?php if (($value['userid'] === getUserid() && $value['username'] === getUsername()) || isAdmin()) { /* 本人か管理人の場合はメニューを表示 */ ?>
                <li class="view-col-item"><a href="./edit.php?editid=<?php echo h($value['id']); ?>">編集</a></li>
                <li class="view-col-item"><a href="./delete.php?deleteid=<?php echo h($value['id']); ?>">削除</a></li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
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
