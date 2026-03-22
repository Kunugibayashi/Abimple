<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

require_once(__DIR__ .'/../../../../core/src/lib/exportlib.php');
require_once(__DIR__ .'/../../../../core/src/lib/templatelib.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

$success = '';
$errors = array();
$inputParams = array();

// セッションが切れていても退出はできるようにフォームから値を取得
$inputParams['characterid'] = inputParam('characterid', 20) ?? '';
$inputParams['inoutmesflg'] = inputParam('inoutmesflg', 1) ?? 0;

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));
sessionLogChatEntryCharacter($inputParams['characterid']);

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_SRC_LINK = SITE_ROOT .'/chatrooms/rooms/'. $roomdir .'/src/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken($inputParams['characterid']);

// DB接続
$dbhChatrooms  = connectRo(CHAT_ROOMS_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);
$dbhInouthistory = connectRw(ROOM_INOUT_HISTORIES_DB);
$dbhChatsecrets = connectRw(CHAT_SECRETS_DB);
$dbhChatlogfiles = connectRw(CHAT_LOG_FILES_DB);

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
      'logtype' => LOGTYPE_SYSTEM,
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

$nowChatentries = selectEqualChatentries($dbhChatentries);

if ($chatroom['issecret'] == 1 && usedArr($myChatentry) && !usedArr($nowChatentries)) {
  // 秘匿ルーム、かつ、最終退室者の場合はログを削除
  deleteChatlogs($dbhChatlogs);

  // 秘匿パスワードのリセット
  updateChatsecrets($dbhChatsecrets, '');

  // 余分な参加者ログを削除
  deleteChatentriesExit($dbhChatentries);

} else if (isset($myChatentry) && usedArr($myChatentry) && !usedArr($nowChatentries)) {
  // 最終退室者の場合はログを出力
  $entrykey = $myChatentry['entrykey'];
  $chatentries = selectEqualLogChatentries($dbhChatentries, $entrykey);

  // ささやきはログ出力しない
  $dbParams = [
    'whisperflg' => '0'
  ];

  $filepath = exportChatLogFile(null, $dbhChatlogs, $entrykey, $chatroom, $chatentries, $dbParams);
  if (!usedStr($filepath)) {
    $success = '退室しました。'; // 退室処理済みのため、退室のメッセージは表示する。
    $errors[] = 'ログ出力が正常に実行できませんでした。管理者にお問い合わせください。';
    goto outputPage;
  }
  $logFileName = basename($filepath);

  // ログの中から参加者を取得
  $doc = new DOMDocument();
  libxml_use_internal_errors(true);
  $doc->loadHTMLFile($filepath);
  libxml_clear_errors();
  $entries = $doc->saveHTML($doc->getElementById('id-chat-entries'));
  // 出力時に改行コードが <br> に変換されてしまうため削除
  $entries = str_replace(array("\r\n", "\r", "\n"), '', $entries);

  // ログ倉庫に登録
  insertChatlogfiles(
    $dbhChatlogfiles,
    $entrykey,
    $roomdir,
    $chatroom['title'],
    $logFileName,
    $entries
  );

  // 不可対策として指定数以上のログを削除
  deleteChatlogsLimit5000($dbhChatlogs);
  // 余分な参加者ログを削除
  deleteChatentriesExit($dbhChatentries);
}

// 秘匿ルームの場合は保持キーワードをリセット
if ($chatroom['issecret'] == 1) {
  setSecretKeyword('');
  clearSecretKeyword();
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
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- DB参照値用 -->
  <?php echo renderDbCssVariables($chatroom); ?>
  <!-- チャット画面用CSS -->
  <?php echo renderCssLinkUrl($chatroom); ?>
  <!-- チャット画面のみ設定 -->
  <style>
    li.entries-item {
      cursor: pointer;
    }
  </style>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- DB登録のCSS記載 -->
  <?php if (usedStr($chatroom['roomcss'])) echo '<style>' . h($chatroom['roomcss']) . '</style>'; ?>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
  <script src="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/js/chatlog-sync.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomtop-content-wrap" class="content-wrap"><!-- roomtopと共通 -->

  <header id="id-roomtop-header" class="roomtop-header"><!-- roomtopと共通 -->
  </header>

  <div class="exit-wrap">
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
    <div class="page-back-wrap">
      <button type="button" class="tochatroom-button">トップに戻る</button>
    </div>
  </div>

  <div class="content-log-wrap">
    <header class="chatroom-header-wrap">
      <h3 class="chatroom-header-title">
        <?php if ($chatroom['issecret']) { ?>【秘匿】<?php } ?><?php echo h($chatroom['title']); ?>
        <div class="chatroom-header-guide">
          <?php echo h($chatroom['guide']); ?>
        </div>
      </h3>
      <div class="chatroom-item-wrap">
        <ul class="chatroom-item-group">
          <li class="chatroom-item-title">ログ表示</li>
          <li class="chatroom-item"><span id="id-info-lognum">100</span>行</li>
        </ul>
        <ul class="chatroom-item-group">
          <li class="chatroom-item-title">ログ更新</li>
          <li class="chatroom-item"><span id="id-info-logsec">60</span>秒</li>
        </ul>
      </div>
    </header>
    <div class="entries-wrap">
      <h5 class="entries-title">参加者：</h5>
      <ul id="id-chat-entries" class="entries-item-group"></ul><?php /* id="id-chat-entries" は変更しないこと。ログ一覧で使うため */ ?>
    </div>
    <input type="hidden" id="id-lognum" value="100">
    <input type="hidden" id="id-logsec" value="60000"><?php /* 60000 = 60秒 */ ?>
    <input type="hidden" id="id-domminid" value="0">
    <input type="hidden" id="id-dommaxid" value="0">
    <input type="hidden" id="id-syncmodifiedts" value="0">
    <div id="id-log-error" class="log-error"></div>
    <div id="id-log-wrap" class="log-wrap">
    </div>
  </div>

</div>

<script> <!-- 各ボタン制御 -->
  jQuery(function(){
    jQuery('button.tochatroom-button').on('click', function(){
      window.location.href = '<?php echo h($ROOMDIR_SRC_LINK); ?>roomtop.php';
    });
  });
</script>
<!-- チャット画面のみ設定 -->
<style>
  li.entries-item {
    cursor: pointer;
  }
</style>
<script>
  jQuery(document).on('click', '#id-chat-entries li.entries-item', function() {
    var characterId = jQuery(this).data('characterid');
    var url = '<?php echo h(NAMELIST_VIEW_LINK); ?>' + '?id=' + characterId + '&from=log';
    window.open(url);
  });
</script>
<script>
// js 内使用変数
var CHATLOG_API = "<?php echo h($ROOMDIR_SRC_LINK); ?>chatloglist.php";

// ログ取得起動
jQuery(function() {

  syncHiddenIdsFromDom();
  chatReload();

  var logsec = parseInt(jQuery('#id-logsec').val(), 10);
  if (Number.isNaN(logsec) || logsec < 0) logsec = 60000;

  startChatTimer(logsec);

});
</script>

</body>
</html>
