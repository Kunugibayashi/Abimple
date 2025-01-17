<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$errors = array();
$inputParams = array();

$inputParams['color'] = inputParam('color', 7) ? inputParam('color', 7) : '#000000';
$inputParams['bgcolor'] = inputParam('bgcolor', 7) ? inputParam('bgcolor', 7) : '#ffffff';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策はフォーム表示時にセット

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);
  $dbhCharacters = connectRo(CHARACTERS_DB);
  $dbhChatsecrets = connectRo(CHAT_SECRETS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOMS_DB);
    $chatrooms = selectChatroomsConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0];

  // 秘匿ルームの場合
  if ($chatroom['issecret'] == 1) {
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
    if (!usedArr($chatsecrets)) {
      firstAccessChatsecrets(CHAT_SECRETS_DB);
      $chatsecrets = selectChatsecrets($dbhChatsecrets);
    }
    $dbKeyword = $chatsecrets[0]['keyword'];
    $sessionKeyword = getSecretKeyword();
    if (!usedStr($dbKeyword) || !usedStr($sessionKeyword) || $dbKeyword != $sessionKeyword) {
      header('Location: ./secrettop.php');
      exit;
    }
  }

  $characters = selectCharactersMy($dbhCharacters, getUserid(), getUsername());

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
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <!-- 直接記載があるため下部に移動 -->
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomtop-content-wrap" class="content-wrap">

  <header class="header">
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
    <h3 class="chatconfig-title">入室キャラクター選択</h3>

    <div class="form-wrap roomchat-form-wrap">
      <?php if (!isNowRoomEntry(getPageRoomdir()) && isChatEntry()) { /* 他のルームに入室している場合はメッセージのみ */ ?>
        <div class="note-wrap">
          <p class="note">
            他のルーム（<?php echo h(getNowRoomEntry()); ?>）に入室しています。<br>
          </p>
        </div>
      <?php } else if (isChatEntry()) { /* 入室している場合は入室ボタンのみ */ ?>
        <div class="note-wrap">
          <p class="note">
            すでに入室しています。<br>
          </p>
        </div>
        <form name="roomchat-form" class="roomchat-form" action="./roomchat.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <div class="form-button-wrap submit-wrap">
            <button type="submit">再入室</button>
          </div>
        </form>
      <?php } else if (usedArr($characters)) { /* 入室していない & キャラクター登録をしている場合のみに入室を表示 */ ?>
        <?php setChatToken(); /* フォーム表示時にトークンをセット */ ?>
        <form name="roomchat-form" class="roomchat-form" action="./roomchat.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <ul class="form-row fullname-wrap">
            <li class="form-col-title">キャラクター</li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="characterid">
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
<?php if (usedArr($characters)) { /* キャラクター登録をしている場合のみに表示 */ ?>
  <script>
  jQuery(function(){
    var characterColors = {};
    <?php foreach ($characters as $key => $value) { ?>
      characterColors['<?php echo h($value['id']); ?>'] = {'color' : '<?php echo h($value['color']); ?>', 'bgcolor' : '<?php echo h($value['bgcolor']); ?>' };
    <?php } ?>

    // キャラクター選択によって文字色を変更
    jQuery('select[name="characterid"]').on('change', function(){
      var characterid = jQuery(this).val();

      jQuery('input[name="color"]').val(characterColors[characterid].color);
      jQuery('input.select-color').val(characterColors[characterid].color);

      jQuery('input[name="bgcolor"]').val(characterColors[characterid].bgcolor);
      jQuery('input.select-bgcolor').val(characterColors[characterid].bgcolor);
    }).trigger('change');

  });
  </script>
<?php } ?>
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
/* レイアウト */
div.content-wrap {
  display: grid;
  grid-template-rows: 2em 28em 1fr;
}
header.header {
  grid-row: 1 / 2;
}
div.chatconfig-wrap {
  grid-row: 2 / 3;
  overflow: auto;
}
div.chatroom-frame-wrap {
  grid-row: 3 / 4;
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
/* チャット画面フォーム */
div.roomchat-form-wrap {
  display: flex;
  justify-content: center;
}
div.form-wrap {
  margin: 1em 0;
}
ul.form-row {
  display: flex;
}
li.form-col-title {
  width: 8em;
  padding: 0.2em;
  margin-top: 0.6em;
}
li.form-col-item {
  margin-top: 0.5em;
}
div.form-button-wrap {
  margin-top: 2em;
  margin-right: 2em;
  display: flex;
  justify-content: flex-end;
}
div.form-col-item-group {
  display: flex;
  align-items: flex-start;
}
select[name="inoutmesflg"],
select[name="characterid"] {
  width: 20em;
}
input[name="color"],
input[name="bgcolor"] {
  width: 8em;
}
input[name="memo"] {
  width: 20em;
}
/* チャットルームタイトル */
h3.chatconfig-title {
  border-bottom: solid 2px;
  font-size: 2em;
  text-align: center;
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
<!-- レスポンシブ用 -->
<link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
</body>
</html>
