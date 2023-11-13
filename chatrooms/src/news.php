<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhRoomlist = connectRo(ROOMS_DB);

  $roomList = array();
  $roomList = selectEqualRoomsList($dbhRoomlist, [
    'published' => '1',
  ]);

  $chatrooms = array();
  foreach ($roomList as $key => $value) {
    $chatroom = array();

    $dbPath = INDEX_ROOT.'/chatrooms/rooms/'.$value['roomdir'].'/src/'.CHAT_ENTRIES_DB;

    if (file_exists($dbPath)) {
      $dbhChatentries = connectRo($dbPath);
      $chatentries = selectEqualChatentries($dbhChatentries);
      // この関数内のみでコネクションを完結する
      $dbhChatentries->close();
    } else {
      // DBが作成されていない場合は空を格納
      $chatentries = array();
    }

    $chatroom['roomtitle'] = $value['roomtitle'];
    $chatroom['chatentries'] = $chatentries;
    $chatroom['roomtop'] = SITE_ROOT.'/chatrooms/rooms/'.$value['roomdir'].'/src/roomtop.php';

    $chatrooms[] = $chatroom;
  }

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
<?php foreach ($chatrooms as $chatroom) { ?>
<div class="news-contents">
  <ul class="news-row">
    <li class="news-col-title"><a href="<?php echo h($chatroom['roomtop']); ?>"><?php echo h($chatroom['roomtitle']); ?></a>（<a href="<?php echo h($chatroom['roomtop']); ?>" target="_blank">別窓表示</a>）</li>
  </ul>
  <ul class="news-row">
    <li class="news-col-title">参加者：</li>
    <li class="news-col-item">
      <ul class="news-col-item-row">
        <?php if (!usedArr($chatroom['chatentries'])) { /* 参加者がいない場合 */ ?>
          <li class="news-col-item-row-item">なし</li>
        <?php } ?>
        <?php if (usedArr($chatroom['chatentries'])) { /* 参加者がいる場合 */ ?>
          <?php foreach ($chatroom['chatentries'] as $key => $value) { ?>
            <li class="news-col-item-row-item" style="background-color: <?php echo h($value['bgcolor']); ?>;" >
              <span style="color: <?php echo h($value['color']); ?>;" ><?php echo h($value['fullname']); ?></span>
            </li>
          <?php } ?>
        <?php } ?>
      </ul>
    </li>
  </ul>
</div>
<?php } ?>
