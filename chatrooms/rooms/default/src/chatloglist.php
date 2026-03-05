<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');

require_once(__DIR__ .'/./config.php');
require_once(__DIR__ .'/./functions.php');

$inputParams = array();
$jsonArray = array();

$inputParams['lognum'] = (int) (inputParam('lognum', 5) ? inputParam('lognum', 4) : '100');
$inputParams['lognum'] = round($inputParams['lognum']);
$inputParams['lognum'] = min([$inputParams['lognum'], 10000]);
$inputParams['lognum'] = max([$inputParams['lognum'], 25]);

$inputParams['domminid'] = inputParam('domminid', 20); // 小さい方。int最大桁
$inputParams['dommaxid'] = inputParam('dommaxid', 20); // 大きい方。int最大桁
$inputParams['syncmodifiedts'] = inputParam('syncmodifiedts', 20);

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';
$jsonArray['dommaxid'] = 0;
$jsonArray['syncmodifiedts'] = 0;
$jsonArray['appendlog'] = [];
$jsonArray['updatelog'] = [];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);
  $dbhChatlogs = connectRo(CHAT_LOGS_DB);
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
      $jsonArray['code'] = 1;
      $jsonArray['errorMessage'] = '秘匿ルームです。入室キーワードを入力してください。';
      goto outputPage;
    }
  }

  // 入室時はささやきを表示する
  if (isChatEntry()) {
    $isinroom = 1;
    $sessionChatEntry = getChatEntry();
    $chatlogs = selectEqualApendChatlogs(
      $dbhChatlogs,
      $inputParams['lognum'],
      $inputParams['dommaxid'],
      $isinroom,
      $sessionChatEntry['characterid'],
    );
  } else {
    $isinroom = 0;
    $chatlogs = selectEqualApendChatlogs(
      $dbhChatlogs,
      $inputParams['lognum'],
      $inputParams['dommaxid'],
      $isinroom,
      null,
    );
  }

}

// ログがない場合
if (!usedArr($chatlogs)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ログがありません。';
  goto outputPage;
}


// 最新データの目印を保持
$jsonArray['dommaxid'] = $chatlogs[0]['id'];
$jsonArray['syncmodifiedts'] = $chatlogs[0]['created'];
foreach ($chatlogs as $key => $chatline) {
  if ($chatline['fullname'] === CHAT_LOG_SYSTEM_NAME) {
      // システム
      $stringHtml = renderSystemLog($chatline);
      $jsonArray['appendlog'][] = [
        'id' => $chatline['id'],
        'loghtml' => $stringHtml,
      ];
    } else {
      // システム以外
      $stringHtml = renderChatLog($chatline, $chatroom);
      $jsonArray['appendlog'][] = [
        'id' => $chatline['id'],
        'loghtml' => $stringHtml,
      ];
  }
}


/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、出力開始ラベルのみ使用する。
 */
outputPage:

$json =  json_encode($jsonArray, JSON_UNESCAPED_UNICODE);
setJsonHeader();
echo($json);
exit;


/*
 *******************************************************************************
 * 関数定義
 *******************************************************************************
 */

// システムログ成形
function renderSystemLog(array $chatline): string {
  ob_start();
?>
<div class="chat-narr-wrap">
  <span class="chat-narr-fullname"><?php echo h($chatline['fullname']); ?></span>
  <span class="chat-narr-arrow">≫</span>
  <span class="chat-narr-message"><?php echo ht($chatline['message']); ?></span>
  <span class="chat-narr-created"><?php echo h($chatline['created']); ?></span>
  <div class="entrykey"><?php echo h($chatline['entrykey']); ?></div>
</div>
<?php
  return ob_get_clean();
}

// システムログ成形
function renderChatLog(array $chatline, array $chatroom): string {
  ob_start();
?>
<?php if ($chatroom['logtemplate'] === CHAT_LOG_TEMPLATE1) { ?>
  <div class="chat-wrap" style="background-color: unset; color: <?php echo h($chatline['color']); ?>;">
<?php } else { ?>
  <div class="chat-wrap" style="background-color: <?php echo h($chatline['bgcolor']); ?>; color: <?php echo h($chatline['color']); ?>;">
<?php } ?>
    <span class="chat-fullname">
      <?php if ($chatline['whisperflg'] != 0) {  /* ささやき */  ?>
        （<?php echo h($chatline['fullname']); ?>→<?php echo h($chatline['wtofullname']); ?>）
      <?php } else {  ?>
        <?php echo h($chatline['fullname']); ?>
        <div class="chat-memo">備考：<?php echo h($chatline['memo']); ?></div>
      <?php } ?>
    </span>
    <span class="chat-arrow">≫</span>
    <span class="chat-message"><?php echo ht($chatline['message']); ?></span>
    <span class="chat-editing"><?php echo h(($chatline['modified'] == $chatline['created']) ? '' : '（編集済み）') ?></span>
    <span class="chat-created"><?php echo h($chatline['created']); ?></span>
    <div class="entrykey"><?php echo h($chatline['entrykey']); ?></div>
  </div>
<?php
  return ob_get_clean();
}

