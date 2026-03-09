<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');

require_once(__DIR__ .'/./config.php');
require_once(__DIR__ .'/./functions.php');

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

