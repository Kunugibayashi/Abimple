<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhBbs = connectRo(BBS_DB);

  $articles = selectBbsTitle($dbhBbs);

  // 最初の3件
  $articles = array_slice($articles, 0, 3);

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
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
</head>
<?php foreach ($articles as $key => $value) { ?>
  <div class="news-contents news-contents-area<?php echo h($value['id'] % 10); ?>">
    <ul class="news-row">
      <li class="news-col-item"><a href="<?php echo h(SITE_ROOT.'/bbs/src/bbstop.php'); ?>?parentid=<?php echo h($value['parentid']); ?>#id-<?php echo h($value['id']); ?>"><?php echo h($value['id']); ?></a></li>
      <li class="news-col-item"><?php echo h($value['modified']); ?></li>
      <li class="news-col-item"><?php echo h($value['fromname']); ?></li>
      <li class="news-col-item"><a href="<?php echo h(SITE_ROOT.'/bbs/src/bbstop.php'); ?>?parentid=<?php echo h($value['parentid']); ?>#id-<?php echo h($value['id']); ?>"><?php echo h($value['title']); ?></a></li>
    </ul>
  </div>
<?php } ?>
