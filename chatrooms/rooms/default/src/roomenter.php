<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

$errors = array();
$inputParams = array();

$inputParams['color'] = inputParam('color', 7) ?: '#000000';
$inputParams['bgcolor'] = inputParam('bgcolor', 7) ?: '#ffffff';

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_SRC_LINK = SITE_ROOT .'/chatrooms/rooms/'. $roomdir .'/src/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策はフォーム表示時にセット

  // DB接続
  $dbhChatrooms = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
  $dbhCharacters = connectRo(CHARACTERS_DB);
  $dbhChatsecrets = connectRo(CHAT_SECRETS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(__DIR__ .'/' .CHAT_ROOMS_DB);
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
      header('Location: ' .$ROOMDIR_SRC_LINK .'secrettop.php');
      exit;
    }
  }

  // 自分のキャラクターを選択肢に表示するため
  $characters = selectCharactersMy($dbhCharacters, getUserid(), getUsername());

  // DB接続
  $dbhChatentries = connectRo(__DIR__ .'/' .CHAT_ENTRIES_DB);

  // 入室者一覧取得
  $chatentries = selectEqualChatentries($dbhChatentries);

  // 既に入室しているキャラクターがいる場合は一覧を取得
  $myEntryCharacternames = getRoomChatCharacternames($roomdir);

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

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <div class="roomenter-wrap">
    <h3 class="roomenter-title">入室キャラクター選択</h3>

    <div class="form-wrap roomenter-form-wrap">
      <div class="note-wrap roomenter-note-wrap">
        <p class="note">
          この画面は同ブラウザで複数開くと入室エラーとなります。ご注意ください。エラーとなった場合は画面を更新するか、前のページに戻ってください。<br>
        </p>
        <?php if (usedArr($myEntryCharacternames)) { /* 入室している場合は名前の一覧を出力する */ ?>
          <p class="note">
            すでに『<?php echo h(implode('』『', $myEntryCharacternames)); ?>』で入室しています。<br>
            入室ナレーションを表示させたくない場合は、同じキャラクターを選んで入室してください。<br>
          </p>
        <?php } ?>
      </div>
      <?php if (usedArr($characters)) { /* キャラクター登録をしている場合のみに入室を表示 */ ?>
        <form name="roomenter-form" class="roomenter-form" action="<?php echo h($ROOMDIR_SRC_LINK); ?>roomchat.php" method="POST">
          <input type="hidden" name="token" value="<?php setChatEnterToken(); echo h(getChatEnterToken()); ?>">
          <ul class="form-row fullname-wrap">
            <li class="form-col-title">キャラクター</li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="viewcharacterid">
                  <?php foreach ($characters as $character) { ?>
                    <option value="<?php echo h($character['id']); ?>"><?php echo h($character['fullname']); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
          <ul class="form-row color-wrap">
            <li class="form-col-title">文字色</li>
            <li class="form-col-item">
              <div class="form-col-item-group">
                <input type="text" name="color" value="<?php echo h($inputParams['color']); ?>" maxlength="7">
                <input type="color" class="select-color" value="<?php echo h($inputParams['color']); ?>">
              </div>
            </li>
          </ul>
          <ul class="form-row bgcolor-wrap">
            <li class="form-col-title">背景色</li>
            <li class="form-col-item">
              <div class="form-col-item-group">
                <input type="text" name="bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>" maxlength="7">
                <input type="color" class="select-bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>">
              </div>
            </li>
          </ul>
          <ul class="form-row memo-wrap">
            <li class="form-col-title">備考</li>
            <li class="form-col-item"><input type="text" name="memo" value="" maxlength="200"></li>
          </ul>
          <ul class="form-row fullname-wrap">
            <li class="form-col-title">入室メッセージ</li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="inoutmesflg">
                  <option value="1">入室メッセージを表示する</option>
                  <option value="0">入室メッセージを表示しない</option>
                </select>
              </div>
            </li>
          </ul>
          <div class="form-button-wrap submit-wrap">
            <button type="submit">入室</button>
          </div>
        </form>
      <?php } else { /* キャラクター登録がない場合は案内を表示 */ ?>
        <div class="note-wrap">
          <p class="note">
            入室する場合はログインし、名簿登録をしてください。<br>
          </p>
        </div>
      <?php } ?>
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
<?php if (usedArr($characters)) { /* キャラクター登録をしている場合のみに表示 */ ?>
  <script>
  jQuery(function(){
    var characterColors = {};
    <?php foreach ($characters as $key => $value) { ?>
      characterColors['<?php echo h($value['id']); ?>'] = {'color' : '<?php echo h($value['color']); ?>', 'bgcolor' : '<?php echo h($value['bgcolor']); ?>' };
    <?php } ?>

    // キャラクター選択によって文字色を変更
    jQuery('select[name="viewcharacterid"]').on('change', function(){
      var characterid = jQuery(this).val();

      jQuery('input[name="color"]').val(characterColors[characterid].color);
      jQuery('input.select-color').val(characterColors[characterid].color);

      jQuery('input[name="bgcolor"]').val(characterColors[characterid].bgcolor);
      jQuery('input.select-bgcolor').val(characterColors[characterid].bgcolor);
    }).trigger('change');

  });
  </script>
<?php } ?>
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
