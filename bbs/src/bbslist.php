<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $articles = selectBbsParentTitleList($dbhBbs);

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
  <title>掲示板一覧</title>
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
  <h3 class="frame-title">掲示板一覧</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての記事の「編集」「削除」が可能</span>です。<br>
        「編集」「削除」は詳細画面から行ってください。<br>
      </p>
    </div>
  <?php } ?>

  <div class="bbs-menu">
    <ul class="bbs-menu-item-group">
      <li class="bbs-menu-item"><a href="./signup.php">新規投稿</a></li>
    </ul>
  </div>

  <?php if (!usedArr($articles)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <div class="paging-wrap">
    <?php outputPaging($articles, getNowPage()); ?>
  </div>
  <div class="sumpaging-wrap">
    <?php outputSumPaging($articles, getNowPage()); ?>
  </div>
  <?php if (usedArr($articles)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap bbs-table-wrap">
      <table>
        <tr>
          <th class="cell-id">掲示板ID</th>
          <th class="cell-title">タイトル</th>
          <th class="cell-fullname">作成者</th>
          <th class="cell-modified">最終更新日</th>
          <th class="cell-articlenum">記事数</th>
        </tr>
        <?php foreach ($articles as $key => $value) { ?>
          <tr>
            <td><?php echo h($value['parentid']); ?></td>
            <td><a href="./bbstop.php?parentid=<?php echo h($value['parentid']); ?>"><?php echo h($value['title']); ?></a></td>
            <td><?php echo h($value['fromname']); ?></td>
            <td><?php echo h($value['modified']); ?></td>
            <td><?php echo h($value['articlenum']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
    <div class="paging-wrap">
      <?php outputPaging($articles, getNowPage()); ?>
    </div>
  <?php } ?>

</body>
</html>
