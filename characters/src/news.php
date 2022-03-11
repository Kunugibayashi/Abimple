<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectCharacterList($dbhCharacters);

  // 最初の3件
  $characters = array_slice($characters, 0, 3);

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
<?php foreach ($characters as $key => $value) { ?>
  <div class="news-contents news-contents-area<?php echo h($value['id'] % 10); ?>">
    <ul class="news-row">
      <li class="news-col-item"><a href="<?php echo h(SITE_ROOT.'/characters/src/view.php'); ?>?id=<?php echo h($value['id']); ?>"><?php echo h($value['id']); ?></a></li>
      <li class="news-col-item"><?php echo h($value['modified']); ?></li>
      <li class="news-col-item"><a href="<?php echo h(SITE_ROOT.'/characters/src/view.php'); ?>?id=<?php echo h($value['id']); ?>"><?php echo h($value['fullname']); ?></a></li>
      <li class="news-col-item"><a href="<?php echo h(SITE_ROOT.'/characters/src/view.php'); ?>?id=<?php echo h($value['id']); ?>"><?php echo h($value['comment']); ?></a></li>
    </ul>
  </div>
<?php } ?>
