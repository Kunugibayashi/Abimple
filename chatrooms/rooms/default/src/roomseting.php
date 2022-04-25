<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$success = '';
$errors = array();
$inputParams = array();

$inputParams['title'] = inputParam('title', 100);
$inputParams['guide'] = inputParam('guide', 2000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOM_DB);

  $chatrooms = selectChatroomConfig($dbhChatrooms);
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
$dbhChatrooms = connectRw(CHAT_ROOM_DB);

$chatrooms = selectChatroomConfig($dbhChatrooms);
$chatroom = $chatrooms[0];

$updateRoom = $inputParams;
$result = updateChatroomConfig($dbhChatrooms, $updateRoom);
if (!$result) {
  $errors[] = '更新に失敗しました。もう一度お試しください。';
  goto outputPage;
}

// 情報更新
$chatrooms = selectChatroomConfig($dbhChatrooms);
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

  <div class="chatroom-setting-wrap">
    <h3 class="chatroom-title">自由設定項目変更</h3>
    <div class="note-wrap">
      <p class="note">
        タイトルとルーム説明が変更できます。<br>
        入室者がいる場合は変更できません。<br>
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

    <div class="form-wrap">
      <form name="characters-form" class="characters-form" action="./roomseting.php" method="POST">
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
      <button type="button" class="tochatroom-button">戻る</button>
    </div>
  </div>

  <div class="chatroom-frame-wrap">
    <?php if ($chatroom['isframe']) {  /* フレームあり */ ?>
      <iframe id="log-top" name="log" title="ルームログ"
        src="./log.php">
      </iframe>
    <?php } ?>
  </div>
  <?php if (!$chatroom['isframe']) { /* フレームなし */ ?>
    <script>
      // 自動画面更新
      var lognum = 100;
      var logsec = 25;
      var logReload = function() {
        var resultContents = jQuery('.chatroom-frame-wrap');
        // log側のデフォルトと異なる場合に、画面表示と整合性を取るため、GETパラメータに指定する
        jQuery.ajax({
          url: './log.php?lognum=' + lognum + '&logsec=' + logsec,
          dataType: 'HTML',
        }).done((data, textStatus, jqXHR) => {
          resultContents.html(data);
        }).fail((jqXHR, textStatus, errorThrown) => {
          console.log(jqXHR);
          resultContents.html(errorThrown);
        }).always((data) => {
        });
      }
      logReload();
      setInterval(logReload, logsec * 1000);
    </script>
  <?php } ?>

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
  grid-template-rows: 2em 30em 1fr;
}
header.header {
  grid-column: 1 / 3;
  grid-row: 1 / 2;
}
div.chatroom-setting-wrap {
  grid-column: 1 / 2;
  grid-row: 2 / 3;
  overflow: auto;
}
div.chatroom-frame-wrap {
  grid-column: 1 / 3;
  grid-row: 3 / 4;
}
</style>
<style>
div.chatroom-setting-wrap {
  padding: 2em;
}
/* 入力フォーム */
div.form-wrap {
  display: flex;
  justify-content: center;
}
form {
  margin: 1em 0;
  padding: 2em;
  border-radius: 1em;
  border: solid 1px;
}
ul.form-row {
  margin: 1em 0;
}
li.form-col-title {
  font-weight: bold;
  margin-bottom: 2px;
}
li.form-col-note {
  font-size: 0.8em;
  opacity: 0.6;
}
div.form-button-wrap {
  display: flex;
  justify-content: center;
}
input[name="title"] {
  width: 70vw;
}
textarea[name="guide"] {
  width: 70vw;
  height: 5em;
}
/* ページを戻る */
div.page-back-wrap {
  display: flex;
  justify-content: center;
  margin-top: 2em;
}
/* メッセージ */
div.mes-wrap {
  display: flex;
  justify-content: center;
  margin: 2em 0;
}
</style>
</body>
</html>
