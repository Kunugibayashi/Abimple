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

// セッションが切れていても退出はできるようにフォームから値を取得
$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['inoutmesflg'] = inputParam('inoutmesflg', 1);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken();

// DB接続
$dbhChatrooms  = connectRo(CHAT_ROOMS_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);
$dbhInouthistory = connectRw(ROOM_INOUT_HISTORIES_DB);
$dbhChatsecrets = connectRw(CHAT_SECRETS_DB);
$dbhAllLogLists = connectRw(ALL_LOG_LISTS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

$characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
if (!usedArr($characters)) {
  // 不正アクセス
  echo '名簿が存在しません。';
  exit;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

$myChatentries = selectEqualChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (usedArr($myChatentries)) {
  $myChatentry = $myChatentries[0];

  // 退室していない場合はログを出す
  if ($inputParams['inoutmesflg'] == 1) {
    insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
      'entrykey' => $myChatentry['entrykey'],
      'characterid' => $character['id'],
      'fullname' => CHAT_LOG_SYSTEM_NAME,
      'color' => $chatroom['color'],
      'bgcolor' => $chatroom['bgcolor'],
      'message' => '<span class="fullname">' .$character['fullname'] .'</span>' .'が退室しました。'
    ]);

    // 秘匿ルームでない場合のみ履歴に登録
    if ($chatroom['issecret'] != 1) {
      insertRoominouthistories($dbhInouthistory, [
        'roomtitle' => $chatroom['title'],
        'message' => '<span style="font-weight: bold;">' .$character['fullname'] .'</span>' .'が退室しました。',
      ]);
    }
    deleteRoominouthistoriesLimit1000($dbhInouthistory);
  }
}

// 退室処理
updateChatentries($dbhChatentries, $inputParams['characterid'], [
  'deleteflg' => '1'
]);

// 入室情報の破棄
$save = array();
setChatEntry($save);

$chatentries = selectEqualChatentries($dbhChatentries);

if ($chatroom['issecret'] == 1 && usedArr($myChatentry) && !usedArr($chatentries)) {
  // 秘匿ルーム、かつ、最終退室者の場合はログを削除
  deleteChatlogs($dbhChatlogs);

  // 秘匿パスワードのリセット
  updateChatsecrets($dbhChatsecrets, '');

  // 余分なログを削除
  deleteChatentriesExit($dbhChatentries);

} else if (usedArr($myChatentry) && !usedArr($chatentries)) {
  // 最終退室者の場合はログを出力
  $entrykey = $myChatentry['entrykey'];

  // 最大10000行
  $chatlogs = selectEqualChatlogs($dbhChatlogs, 10000, [
    'entrykey' => $entrykey,
  ]);

  // 入退室ログも発言もなければログ出力しない
  if (usedArr($chatlogs)) {
    $firstDate = $chatlogs[0]['created'];
    $dt = new DateTime($firstDate);

    $logFileName = $dt->format('Ymd_His') ."_" .getPageRoomdir().'.html';

    // ログ出力
    $logoutput = function($logFileName, $entrykey, $logoutputFlg) {
      // DBスコープが上書きされてしまうため無名関数使用
      ob_start();
      include('./log.php');
      $buffer = ob_get_contents();
      ob_end_clean();

      $filePath = ALL_LOG_OUTPUT_DIR .$logFileName;
      file_put_contents($filePath, $buffer, LOCK_EX);
    };
    $logoutput($logFileName, $entrykey, 1);

    // roomdir の取得。DBからの取得は読み込みを増やす必要があるため、共通関数で対応
    $roomdir = getPageRoomdir();

    // ログの中から参加者を取得
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTMLFile(ALL_LOG_OUTPUT_DIR .$logFileName);
    libxml_clear_errors();
    $entries = $doc->saveHTML($doc->getElementById('chat-entries'));
    // 出力時に改行コードが <br> に変換されてしまうため削除
    $entries = str_replace(array("\r\n", "\r", "\n"), '', $entries);

    // ログ倉庫に登録
    insertAllloglists($dbhAllLogLists,
                      $entrykey,
                      $roomdir,
                      $chatroom['title'],
                      $logFileName,
                      $entries
                    );
  }

  // 余分なログを削除
  deleteChatlogsLimit1000($dbhChatlogs);
  deleteChatentriesExit($dbhChatentries);
}

// 秘匿ルームの場合は保持キーワードをリセット
if ($chatroom['issecret'] == 1) {
  setSecretKeyword('');
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
  width: 100%;
  height: 99vh;
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
/* 戻るボタン */
div.page-back-wrap {
  display: flex;
  justify-content: center;
  margin-top: 2em;
}
div.page-back-wrap>button:active,
div.page-back-wrap>button:hover,
div.page-back-wrap>button {
  margin: 0 1em;
  padding: 1em;
  background-color: #3e463b;
  color: #e3e2dc;
  background-image: unset;
  background-origin: unset;
  border: unset;
  border-radius: 10em;
  box-shadow: unset;
  display: inline-block;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  filter: none;
}
</style>
<?php if (usedStr($chatroom['roomcss'])) { ?>
  <style>
    /* DB登録のCSS記載 */
    <?php echo h($chatroom['roomcss']) ?>
  </style>
<?php } ?>
</body>
</html>
