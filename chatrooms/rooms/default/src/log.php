<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$inputParams = array();

$inputParams['lognum'] = (int) (inputParam('lognum', 4) ? inputParam('lognum', 4) : '100');
$inputParams['logsec'] = inputParam('logsec', 3); // 初期値は後から

$inputParams['lognum'] = round($inputParams['lognum']);
$inputParams['lognum'] = min([$inputParams['lognum'], 1000]);
$inputParams['lognum'] = max([$inputParams['lognum'], 25]);

if ($inputParams['logsec'] === '') {
  // 空の時には初期値、ゼロの時には 0 を入れるため
  $inputParams['logsec'] = 25;
} else if ($inputParams['logsec'] != '0' && $inputParams['logsec'] < 25) {
  // 短時間更新にならないように
  $inputParams['logsec'] = 25;
} else {
  $inputParams['logsec'] = (int) $inputParams['logsec'];
}
$inputParams['logsec'] = round($inputParams['logsec']);
$inputParams['logsec'] = min([$inputParams['logsec'], 60]);
$inputParams['logsec'] = max([$inputParams['logsec'], 0]);


if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOM_DB);
  $dbhChatentries = connectRo(CHAT_ENTRIES_DB);
  $dbhChatlogs = connectRo(CHAT_LOGS_DB);

  $chatrooms = selectChatroomConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOM_DB);
    $chatrooms = selectChatroomConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0];

  $chatentries = selectChatentries($dbhChatentries);

  $chatlogs = selectChatlogs($dbhChatlogs, $inputParams['lognum']);

  goto outputPage;
}
/* 以降はPOST通信を想定。
 * POSTの場合、requireでの処理を前提に記載。
 * require元て、ファイル名、エントリーキーを設定すること。
 */
// 入力値はファイル出力用にセット
$inputParams['lognum'] = 10000;
$inputParams['logsec'] = 0;

if (!usedStr($logFileName)) {
  echo 'ファイル名が設定されていません。';
  exit;
}

if (!usedStr($entrykey)) {
  echo 'エントリーキーが設定されていません。';
  exit;
}

// DB接続
$dbhChatrooms = connectRo(CHAT_ROOM_DB);
$dbhChatentries = connectRo(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRo(CHAT_LOGS_DB);

$chatrooms = selectChatroomConfig($dbhChatrooms);
$chatroom = $chatrooms[0];

$chatentries = selectLogChatentries($dbhChatentries, [
  'entrykey' => $entrykey,
]);

// 最大10000行
$chatlogs = selectChatlogs($dbhChatlogs, 10000, [
  'entrykey' => $entrykey,
]);

if (!usedArr($chatlogs)) {
 echo 'エントリーキーに対応するログありません。';
 exit;
}


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
  <?php if ($inputParams['logsec'] != 0) { ?>
    <script>
      // 自動画面更新
      var logReload = function() {
        location.reload();
      }
      setTimeout(logReload, <?php echo h($inputParams['logsec']); ?> * 1000);
    </script>
  <?php } ?>
</head>
<body>
<div class="content-wrap">

  <header class="chatroom-header-wrap">
    <h3 class="chatroom-header-title"><?php echo h($chatroom['title']); ?>
      <div class="chatroom-header-guide">
        <?php echo h($chatroom['guide']); ?>
      </div>
    </h3>
    <div class="chatroom-item-wrap">
      <ul class="chatroom-item-group">
        <li class="chatroom-item-title">ログ表示</li>
        <li class="chatroom-item"><?php echo h($inputParams['lognum']); ?>行</li>
      </ul>
      <ul class="chatroom-item-group">
        <li class="chatroom-item-title">ログ更新</li>
        <li class="chatroom-item"><?php echo h($inputParams['logsec']); ?>秒</li>
      </ul>
    </div>
  </header>

  <div class="entries-wrap">
    <h5 class="entries-title">参加者：</h5>
    <ul id="chat-entries" class="entries-item-group"><?php /* id="chat-entries" は変更しないこと */ ?>
      <?php if (!usedArr($chatentries)) { /* 参加者がいない場合 */ ?>
        <li class="entries-item">なし</li>
      <?php } ?>
      <?php if (usedArr($chatentries)) { /* 参加者がいる場合 */ ?>
        <?php foreach ($chatentries as $key => $value) { ?>
          <li class="entries-item" style="background-color: <?php echo h($value['bgcolor']); ?>;" >
            <span style="color: <?php echo h($value['color']); ?>;" ><?php echo h($value['fullname']); ?></span>
          </li>
        <?php } ?>
      <?php } ?>
    </ul>
  </div>

  <div class="log-wrap">
    <?php if (!usedArr($chatlogs)) { /* ログがない場合 */ ?>
      ログはありません。
    <?php } ?>
    <?php if (usedArr($chatlogs)) { /* ログがある場合 */ ?>
      <?php foreach ($chatlogs as $key => $value) { ?>
        <?php if ($value['fullname'] === CHAT_LOG_SYSTEM_NAME) {  /* システム */  ?>
          <div class="chat-narr-wrap">
            <div class="chat-narr-fullname"><?php echo h($value['fullname']); ?></div>
            <div class="chat-narr-message"><?php echo ht($value['message']); ?></div>
            <div class="chat-narr-created"><?php echo h($value['created']); ?></div>
            <div class="entrykey"><?php echo h($value['entrykey']); ?></div>
          </div>
        <?php } else if ($chatroom['logtemplate'] === CHAT_LOG_TEMPLATE3) { ?>
          <div class="chat-wrap" style="color: <?php echo h($value['color']); ?>;">
            <div class="chat-line-wrap">
              <div class="chat-fullname">
                <?php echo h($value['fullname']); ?>
                <div class="chat-memo">備考：<?php echo h($value['memo']); ?></div>
              </div>
              <div class="chat-created"><?php echo h($value['created']); ?></div>
              <div class="chat-editing"><?php echo h(($value['modified'] == $value['created']) ? '' : '（編集済み）') ?></div>
            </div>
            <div class="chat-message" style="<?php echo h("border-bottom: solid 1px ".$value['bgcolor'] .";")?>"><?php echo ht($value['message']); ?></div>
            <div class="entrykey"><?php echo h($value['entrykey']); ?></div>
          </div>
        <?php } else { /* デフォルト */ ?>
          <div class="chat-wrap" style="background-color: <?php echo h($value['bgcolor']); ?>; color: <?php echo h($value['color']); ?>;">
            <div class="chat-line-wrap">
              <div class="chat-fullname">
                <?php echo h($value['fullname']); ?>
                <div class="chat-memo">備考：<?php echo h($value['memo']); ?></div>
              </div>
              <div class="chat-created"><?php echo h($value['created']); ?></div>
              <div class="chat-editing"><?php echo h(($value['modified'] == $value['created']) ? '' : '（編集済み）') ?></div>
            </div>
            <div class="chat-message"><?php echo ht($value['message']); ?></div>
            <div class="entrykey"><?php echo h($value['entrykey']); ?></div>
          </div>
        <?php } ?>
      <?php } ?>
    <?php } ?>
  </div>

</div>
<style>
body, h1, h2, h3, h4, ul, li, div {
  margin: 0;
  padding: 0;
}
a {
  color: <?php echo h($chatroom['color']); ?>;
}
body {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
}
li {
  list-style-type: none;
}
div.content-wrap {
  margin: 0;
  padding: 0;
}
/* ヘッダー */
header.chatroom-header-wrap {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin: 0.5em;
  border-bottom: solid 2px;
}
h3.chatroom-header-title {
  font-size: 1.5em;
}
ul.chatroom-item-group {
  display: flex;
}
li.chatroom-item-title {
  font-weight: bold;
  font-size: 1em;
}
div.chatroom-item-wrap {
  font-size: 0.9em;
}
/* エントリーキー */
div.entrykey {
  display: none;
}
/* 参加者 */
div.entries-wrap {
  display: flex;
  align-items: center;
  margin-left: 0.5rem;
}
h5.entries-title {
  font-weight: bold;
  font-size: 1em;
  padding: 0;
  margin: 0;
}
ul.entries-item-group {
  display: flex;
  align-items: center;
}
li.entries-item {
  margin-right: 0.5em;
  border-radius: 0.2em;
  padding: 0.2em;
  font-size: 0.8em;
}
/* 部屋案内ポップアップ */
h3.chatroom-header-title {
  position: relative;
}
h3.chatroom-header-title:hover div.chatroom-header-guide {
  display: block;
  position: absolute;
  top: 2.5em;
  left: 2em;
  line-height: 1.2em;
}
div.chatroom-header-guide {
  position: absolute;
  display: none;
  padding: 1em;
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  border: 2px dotted;
  width: 80vw;
  left : -1%;
  font-size: 0.8rem;
  font-weight: normal;
}
/* 備考ポップアップ */
div.chat-fullname {
  position: relative;
}
div.chat-fullname:hover div.chat-memo {
  display: block;
  position: absolute;
  top: 2em;
  left: 2em;
  line-height: 1.2em;
}
div.chat-memo {
  position: absolute;
  display: none;
  padding: 1em;
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
  border: 2px dotted;
  width: 40vw;
  left : -1%;
  font-size: 0.8em;
  font-weight: normal;
}
/* システム */
div.chat-narr-wrap {
  display: flex;
  align-items: center;
  opacity: 0.7;
  margin: 0.5em 0 0.5em 2em;
}
div.chat-narr-fullname {
  margin-right: 0.5em;
  font-weight: bold;
}
div.chat-narr-fullname:after {
  content:"≫";
}
div.chat-narr-message {
  margin-right: 0 0.5em;
}
div.chat-narr-message>span.fullname {
  font-weight: bold;
}
div.chat-narr-created {
  font-size: 0.5em;
  margin-left: 2em;
}
<?php if ( $chatroom['logtemplate'] === CHAT_LOG_DEFAULT
        || $chatroom['logtemplate'] === CHAT_LOG_TEMPLATE1
        || $chatroom['logtemplate'] === CHAT_LOG_TEMPLATE2
        || $chatroom['logtemplate'] === CHAT_LOG_TEMPLATE3
      ) {
?>
  div.log-wrap {
    margin: 1em;
  }
  /* 発言欄 */
  div.chat-wrap {
    margin: 0.5em;
    padding: 1em;
    border-radius: 1em;
  }
  div.chat-editing,
  div.chat-created {
    font-size: 0.8em;
  }
  div.chat-line-wrap {
    display: flex;
    align-items: flex-end;
  }
  div.chat-fullname {
    font-weight: bold;
    margin-right: 1em;
  }
  div.chat-message {
    line-height: 1.5em;
    margin: 0.2em 0;
    padding: 0.5em 0;
    line-height: 1.5;
  }
<?php } ?>
<?php if ($chatroom['logtemplate'] === CHAT_LOG_TEMPLATE1) { ?>
  div.chat-message {
    border-top: solid 1px;
  }
<?php } ?>
<?php if ($chatroom['logtemplate'] === CHAT_LOG_TEMPLATE2) { ?>
  div.chat-message {
    border-bottom: solid 1px;
  }
<?php } ?>
</body>
</html>
