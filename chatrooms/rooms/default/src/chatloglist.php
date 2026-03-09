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

$inputParams['lognum'] = (int) (inputParam('lognum', 5) ? inputParam('lognum', 5) : '100');
$inputParams['lognum'] = round($inputParams['lognum']);
$inputParams['lognum'] = min([$inputParams['lognum'], 10000]);
$inputParams['lognum'] = max([$inputParams['lognum'], 25]);

$inputParams['domminid'] = inputParam('domminid', 20); // 小さい方。int最大桁
$inputParams['dommaxid'] = inputParam('dommaxid', 20); // 大きい方。int最大桁
$inputParams['syncmodifiedts'] = inputParam('syncmodifiedts', 20);

// 日付ではない場合は 0 に変換
$tmpsync = trim((string)$inputParams['syncmodifiedts']);
if (!usedStr($tmpsync)  || strtotime($tmpsync) === false) {
  $inputParams['syncmodifiedts'] = 0;
}

// 戻り値初期値
$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';
$jsonArray['dommaxid'] = 0;
$jsonArray['syncmodifiedts'] = 0;
$jsonArray['chatentry'] = '';
$jsonArray['appendlog'] = [];
$jsonArray['updatelog'] = [];

// DB接続
$dbhChatrooms = connectRo(CHAT_ROOMS_DB);
$dbhChatentries = connectRo(CHAT_ENTRIES_DB);
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

// 入室者取得
$chatentries = selectEqualChatentries($dbhChatentries);
// 入室者がいない場合も何らかの表示を行うため整形処理をする
$stringHtml = renderChatentries($chatentries);
$jsonArray['chatentry'] = $stringHtml;

// 追加用ログ
// 入室時はささやきを含めてログを取得する
if (isChatEntry()) {
  $isinroom = 1;
  $sessionChatEntry = getChatEntry();
  $appendlogs = selectEqualAppendChatlogs(
    $dbhChatlogs,
    $inputParams['lognum'],
    $inputParams['dommaxid'],
    $isinroom,
    $sessionChatEntry['characterid'],
  );
} else {
  $isinroom = 0;
  $appendlogs = selectEqualAppendChatlogs(
    $dbhChatlogs,
    $inputParams['lognum'],
    $inputParams['dommaxid'],
    $isinroom,
    null,
  );
}
// ログがない場合は整形処理をしない
if (usedArr($appendlogs)) {
  // 最新データの目印を保持
  $jsonArray['dommaxid'] = $appendlogs[0]['id'];
  $jsonArray['syncmodifiedts'] = $appendlogs[0]['created'];
  foreach ($appendlogs as $key => $chatline) {
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
}


// 編集時更新用ログ
// 初期取得時は処理をしない
if ((int)$inputParams['dommaxid'] === 0 || (int)$inputParams['domminid'] === 0) {
  goto outputPage;
}
// 入室時はささやきを含めてログを取得する
if (isChatEntry()) {
  $isinroom = 1;
  $sessionChatEntry = getChatEntry();
  $updatelogs = selectEqualUpdateChatlogs(
    $dbhChatlogs,
    $inputParams['lognum'],
    $inputParams['dommaxid'],
    $inputParams['domminid'],
    $inputParams['syncmodifiedts'],
    $isinroom,
    $sessionChatEntry['characterid'],
  );
} else {
  $isinroom = 0;
  $updatelogs = selectEqualUpdateChatlogs(
    $dbhChatlogs,
    $inputParams['lognum'],
    $inputParams['dommaxid'],
    $inputParams['domminid'],
    $inputParams['syncmodifiedts'],
    $isinroom,
    null,
  );
}
// ログがない場合は整形処理をしない
if (usedArr($updatelogs)) {
  // 最新データの日付を保持
  $syncTs = $jsonArray['syncmodifiedts'];
  $updateTs = $updatelogs[0]['modified'];
  if ($syncTs === null || $updateTs > $syncTs) {
    $jsonArray['syncmodifiedts'] = $updateTs;
  }

  foreach ($updatelogs as $key => $chatline) {
    if ($chatline['fullname'] === CHAT_LOG_SYSTEM_NAME) {
        // システム
        $stringHtml = renderSystemLog($chatline);
        $jsonArray['updatelog'][] = [
          'id' => $chatline['id'],
          'loghtml' => $stringHtml,
        ];
      } else {
        // システム以外
        $stringHtml = renderChatLog($chatline, $chatroom);
        $jsonArray['updatelog'][] = [
          'id' => $chatline['id'],
          'loghtml' => $stringHtml,
        ];
    }
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

// 参加者整形
function renderChatentries(array $chatentries): string {
  ob_start();
?>
  <?php if (!usedArr($chatentries)) { /* 参加者がいない場合 */ ?>
    <li class="entries-no-item">なし</li>
  <?php } ?>
  <?php if (usedArr($chatentries)) { /* 参加者がいる場合 */ ?>
    <?php foreach ($chatentries as $key => $value) { ?>
      <li class="entries-item" style="background-color: <?php echo h($value['bgcolor']); ?>;" value="<?php echo h($value['characterid']); ?>">
        <span style="color: <?php echo h($value['color']); ?>;" ><?php echo h($value['fullname']); ?></span>
      </li>
    <?php } ?>
  <?php } ?>
<?php
  $html = ob_get_clean();
  $html = preg_replace('/^[ \t]+/m', '', $html);
  $html = preg_replace('/>\s+</', '><', $html);
  return $html;
}

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
  $html = ob_get_clean();
  $html = preg_replace('/^[ \t]+/m', '', $html);
  $html = preg_replace('/>\s+</', '><', $html);
  return $html;
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
  $html = ob_get_clean();
  $html = preg_replace('/^[ \t]+/m', '', $html);
  $html = preg_replace('/>\s+</', '><', $html);
  return $html;
}

