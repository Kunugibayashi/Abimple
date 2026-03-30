<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');
require_once(__DIR__ . '/../../core/src/logger.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhRoomlist = connectRo(ROOMS_DB);

  $roomList = array();
  $roomList = selectEqualRoomsList($dbhRoomlist, [
    'published' => '1',
  ]);

  $chatrooms = array();
  foreach ($roomList as $key => $value) {

    $dbPathChatrooms = CHAT_ROOM_ROOMS_PATH .$value['roomdir'] .'/src/' .CHAT_ROOMS_DB;
    $dbPathEntryes = CHAT_ROOM_ROOMS_PATH .$value['roomdir'] .'/src/' .CHAT_ENTRIES_DB;

    if (file_exists($dbPathChatrooms)) {
      $dbhChatrooms = connectRo($dbPathChatrooms);
      $tmpChatrooms = selectChatroomsConfig($dbhChatrooms);
      $tmpChatroom = $tmpChatrooms[0] ?? [];
      $chatroom = $tmpChatroom;
      // この関数内のみでコネクションを完結する
      $dbhChatrooms->close();
    } else {
      // DBが作成されていない場合は空を格納
      $chatroom = [
        'secrettype' => 0,
        'title' => '',
        'bgcolor' => '#000000',
        'color' => '#ffffff',
      ];
    }

    if (file_exists($dbPathEntryes)) {
      $dbhChatentries = connectRo($dbPathEntryes);
      $chatentries = selectEqualChatentries($dbhChatentries);
      // この関数内のみでコネクションを完結する
      $dbhChatentries->close();
    } else {
      // DBが作成されていない場合は空を格納
      $chatentries = array();
    }

    $chatroom['chatentries'] = $chatentries;
    $chatroom['roomtop'] = CHAT_ROOM_ROOMS_LINK .$value['roomdir'] .'/src/roomtop.php';

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
    <li class="news-col-title">
<a href="<?php echo h($chatroom['roomtop']); ?>">
<?php if ($chatroom['secrettype'] == CHAT_ROOM_SECRET) { ?>【秘匿】<?php } ?>
<?php if ($chatroom['secrettype'] == CHAT_ROOM_KEYWORD) { ?>【KEYWORD】<?php } ?>
<?php echo h($chatroom['title']); ?>
</a>（<a href="<?php echo h($chatroom['roomtop']); ?>" target="_blank">別窓表示</a>）</li>
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
            <?php if ($chatroom['secrettype'] == CHAT_ROOM_OPEN) { ?>
              <li class="news-col-item-row-item" style="background-color: <?php echo h($value['bgcolor']); ?>;" >
                <span style="color: <?php echo h($value['color']); ?>;" ><?php echo h($value['fullname']); ?></span>
              </li>
            <?php } else if($chatroom['secrettype'] == CHAT_ROOM_KEYWORD) { ?>
              <li class="news-col-item-row-item" style="background-color: <?php echo h($chatroom['bgcolor']); ?>;" >
                <span style="color: <?php echo h($chatroom['color']); ?>;" >―</span>
              </li>
            <?php } ?>
            <?php /* 秘匿ルームは参加者を表示しない */ ?>
          <?php } ?>
        <?php } ?>
      </ul>
    </li>
  </ul>
</div>
<?php } ?>
