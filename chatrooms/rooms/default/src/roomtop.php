<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

$errors = array();
$inputParams = array();

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_SRC_LINK = SITE_ROOT .'/chatrooms/rooms/'. $roomdir .'/src/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策はフォーム表示時にセット

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);
  $dbhChatsecrets = connectRo(CHAT_SECRETS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOMS_DB);
    $chatrooms = selectChatroomsConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0] ?? [];

  // 秘匿ルームの場合、パスワードチェック
  if ($chatroom['issecret'] == 1) {
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
    if (!usedArr($chatsecrets)) {
      firstAccessChatsecrets(CHAT_SECRETS_DB);
      $chatsecrets = selectChatsecrets($dbhChatsecrets);
    }
    // DBとセッションのパスワードが異なる場合は弾く
    $dbKeyword = $chatsecrets[0]['keyword'];
    $sessionKeyword = getSecretKeyword();
    if (!usedStr($dbKeyword) || !usedStr($sessionKeyword) || $dbKeyword != $sessionKeyword) {
      // TODO ./の書き方は壊れやすいため修正すること
      header('Location: ./secrettop.php');
      exit;
    }
  }

  // DB接続
  $dbhChatentries = connectRo(CHAT_ENTRIES_DB);

  // 入室者一覧取得
  $chatentries = selectEqualChatentries($dbhChatentries);

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
  <title><?php echo h($chatroom['title']); ?></title>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- DB参照値用 -->
  <?php echo renderDbCssVariables($chatroom); ?>
  <!-- チャット画面用CSS -->
  <?php echo renderCssLinkUrl($chatroom); ?>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- DB登録のCSS記載 -->
  <?php if (usedStr($chatroom['roomcss'])) echo '<style>' . h($chatroom['roomcss']) . '</>'; ?>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
  <script src="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/js/chatlog-sync.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomtop-content-wrap" class="content-wrap"><!-- roomtopと共通 -->

  <header id="id-roomtop-header" class="roomtop-header"><!-- roomtopと共通 -->
    <nav class="roomtop-header-menu">
      <ul class="roomtop-header-item-group">
        <?php if ($chatroom['isfree']) { ?>
          <li class="roomtop-header-item"><a href="<?php echo h($ROOMDIR_SRC_LINK); ?>roomseting.php">ルーム設定変更</a></li>
        <?php } ?>
        <?php if (isAdmin()) { ?>
          <li class="roomtop-header-item"><a href="<?php echo h($ROOMDIR_SRC_LINK); ?>admin.php">管理画面</a></li>
        <?php } ?>
      </ul>
    </nav>
  </header>

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <div class="chatconfig-wrap">
    <div class="chatconfig-title-wrap">
      <h3 class="chatconfig-title"><?php echo h($chatroom['title']); ?></h3>
    </div>
    <div class="chatconfig-guide"><?php echo hb($chatroom['guide']); ?></div>
    <div class="form-wrap roomtop-form-wrap">
      <form name="roometop-form" class="roometop-form" action="<?php echo h($ROOMDIR_SRC_LINK); ?>roomenter.php" method="GET">
        <div class="form-button-wrap submit-wrap">
          <button type="submit">入室キャラクター選択</button>
        </div>
      </form>
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
