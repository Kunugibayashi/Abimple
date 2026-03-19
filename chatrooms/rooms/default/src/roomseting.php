<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['title'] = inputParam('title', 100);
$inputParams['guide'] = inputParam('guide', 2000);

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_SRC_LINK = SITE_ROOT .'/chatrooms/rooms/'. $roomdir .'/src/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  $chatroom = $chatrooms[0];

  // 情報表示のため詰め替え
  $inputParams = $chatroom;

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhChatrooms = connectRw(CHAT_ROOMS_DB);
$dbhChatentries = connectRo(CHAT_ENTRIES_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0];

// 入力値チェック
if (!usedStr($inputParams['title'])) {
  $errors[] = 'ルームタイトルを入力してください。';
}
if (isUnsafeChars($inputParams['title'])) {
  $errors[] = 'ルームタイトルに利用不可な制御文字、あるいは記号が含まれています。';
}
if (!usedStr($inputParams['guide'])) {
  $errors[] = 'ルーム説明を入力してください。';
}
if (usedArr($errors)) {
  goto outputPage;
}

$chatentries = selectEqualChatentries($dbhChatentries);
if (usedArr($chatentries)) {
  $errors[] = '入室者がいるため、変更できません。';
  goto outputPage;
}

$updateRoom = $inputParams;
$result = updateChatroomsConfig($dbhChatrooms, $updateRoom);
if (!$result) {
  $errors[] = '更新に失敗しました。もう一度お試しください。';
  goto outputPage;
}

// 情報更新
$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0];

$success = '更新が完了しました。';

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
  <?php if (usedStr($chatroom['roomcss'])) echo '<style>' . h($chatroom['roomcss']) . '</>'; ?>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
  <script src="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/js/chatlog-sync.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomtop-content-wrap" class="content-wrap"><!-- roomtopと共通 -->

  <header id="id-roomtop-header" class="roomtop-header"><!-- roomtopと共通 -->
  </header>

  <div class="chatroom-setting-wrap">
    <h3 class="chatroom-setting-title">自由設定項目変更</h3>
    <div class="note-wrap">
      <p class="note">
        タイトルとルーム説明が変更できます。入室者がいる場合は変更できません。<br>
      </p>
    </div>

    <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
      <div class="note-wrap">
        <p class="note">
          管理ユーザーでログインしています。<br>
        </p>
      </div>
    <?php } ?>

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

    <div class="setting-form-wrap">
      <form name="setting-form" class="setting-form" action="<?php echo h($ROOMDIR_SRC_LINK); ?>roomseting.php" method="POST">
        <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
        <ul class="form-row">
          <li class="form-col-title">ルームタイトル<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="title" value="<?php echo h($inputParams['title']); ?>" maxlength="100"></li>
          <li class="form-col-note">最大 100 文字</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">ルーム説明<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><textarea name="guide" maxlength="2000"><?php echo h($inputParams['guide']); ?></textarea></li>
          <li class="form-col-note">最大 2000 文字</li>
        </ul>
        <div class="form-button-wrap">
          <button type="submit">更新</button>
        </div>
      </form>
    </div>

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
    var url = '<?php echo h(NAMELIST_VIEW_LINK); ?>' + '?id=' + characterId;
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
