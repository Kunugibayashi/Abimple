<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$success = '';
$inputParams = array();

$inputParams['characterid'] = inputParam('characterid', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken();


// DB接続
$dbhChatrooms  = connectRo(CHAT_ROOM_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);

$chatrooms = selectChatroomConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

$characters = selectCharacterId($dbhCharacters, $inputParams['characterid']);
if (!usedArr($characters)) {
  // 不正アクセス
  echo '名簿が存在しません。';
  exit;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

$myChatentries = selectChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (usedArr($myChatentries)) {
  $myChatentry = $myChatentries[0];

  // 退室していない場合はログを出す
  insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
    'entrykey' => $myChatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => CHAT_LOG_SYSTEM_NAME,
    'color' => $chatroom['color'],
    'bgcolor' => $chatroom['bgcolor'],
    'message' => '<span class="fullname"><span style=" color:' .$character['color'] .';">' .$character['fullname'] .'</span></span>' .'が退室しました。'
  ]);
}

// 退室処理
updateChatentries($dbhChatentries, $inputParams['characterid'], [
  'deleteflg' => '1'
]);

// 入室情報の破棄
$save = array();
setChatEntry($save);


// 最終退室者の場合はログを出力
$chatentries = selectChatentries($dbhChatentries);
if (usedArr($myChatentry) && !usedArr($chatentries)) {
  $entrykey = $myChatentry['entrykey'];

  // 最大10000行
  $chatlogs = selectChatlogs($dbhChatlogs, 10000, [
    'entrykey' => $entrykey,
  ]);

  $firstDate = $chatlogs[0]['created'];
  $dt = new DateTime($firstDate);

  $logFileName = $dt->format('Ymd_His') ."_" .getPageRoomdir();

  // ログ出力
  ob_start();
  include('./log.php');
  $buffer = ob_get_contents();
  ob_end_clean();

  $filePath = OUTPUT_LOG_DIR .$logFileName .'.html';
  file_put_contents($filePath, $buffer, LOCK_EX);
}

$success = '退室しました。';


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
  <title><?php echo h($chatroom['title']); ?></title>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">

  <header class="header">
  </header>

  <div class="exit-wrap">
    <?php if (usedStr($success)) { /* 成功メッセージ */ ?>
      <div class="mes-wrap">
        <ul class="success-mes-wrap">
          <li class="success-mes"><?php echo h($success); ?></li>
        </ul>
      </div>
    <?php } ?>
    <div class="page-back-wrap">
      <button type="button" class="tochatroom-button">トップに戻る</button>
    </div>
  </div>

  <div class="chatroom-frame-wrap">
    <iframe id="log-top" name="log" title="ルームログ"
      src="./log.php">
    </iframe>
  </div>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.tochatroom-button').on('click', function(){
    window.location.href = './roomtop.php';
  });
});
</script>
<style>
/* 共通 */
a {
  color: <?php echo h($chatroom['color']); ?>;
}
body {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
}
div.content-wrap {
  margin: 0;
  padding: 0;
  width: 100vw;
  height: 100vh;
}
ul, li {
  list-style-type: none;
}
/* ヘッダー */
header.header {
  display: flex;
  justify-content: flex-end;
  font-size: 0.8em;
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
ul.header-item-group {
  display: flex;
  margin: 0.5em;
}
li.header-item {
  padding: 0 1em;
  list-style-type: none;
}
li.header-item>a {
  color: <?php echo h($chatroom['bgcolor']); ?>;
}
/* インラインフレーム */
div.chatroom-frame-wrap {
  border-top: solid 4px;
}
</style>
<style>
/* レイアウト */
div.content-wrap {
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: 2em 20em 1fr;
}
header.header {
  grid-column: 1 / 3;
  grid-row: 1 / 2;
}
div.exit-wrap {
  grid-column: 1 / 2;
  grid-row: 2 / 3;
}
div.chatroom-frame-wrap {
  grid-column: 1 / 3;
  grid-row: 3 / 4;
}
</style>
<style>
/* 退室メッセージ */
div.exit-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 3em;
}
div.page-back-wrap {
  margin-top: 2em;
}
</style>
</body>
</html>
