<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

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
      <li class="entries-item" style="background-color: <?php echo h($value['bgcolor']); ?>;" data-characterid="<?php echo h($value['characterid']); ?>">
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

// システムログ or 発言ログ を判定して該当 html を返却
function renderChatLog(array $chatline, array $chatroom): string {
  if ($chatline['fullname'] === CHAT_LOG_SYSTEM_NAME) {
    // システム
    return renderSystemLog($chatline);
  } else {
    // システム以外
    return renderCharacterLog($chatline, $chatroom);
  }
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

// 発言ログ成形
function renderCharacterLog(array $chatline, array $chatroom): string {
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

// DB参照値
function renderDbCssVariables(array $chatroom): string {
  ob_start();
?>
<style>
  :root {
    --chat-color: <?php echo h($chatroom['color']); ?>;
    --chat-bgcolor: <?php echo h($chatroom['bgcolor']); ?>;
    --chat-bgimage: <?php echo usedStr($chatroom['bgimage']) ? 'url("' . h($chatroom['bgimage']) . '")' : 'none'; ?>;
    --chat-bg-repeat: <?php echo ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2 || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) ? 'repeat' : 'initial'; ?>;
  }
</style>
<?php
  $html = ob_get_clean();
  return $html;
}

// CSSリンク
function renderCssLinkUrl(array $chatroom): string {
  ob_start();
?>
  <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>css/roombase.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE1) { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/toptemplate1.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } else if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2 ) { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/toptemplate2.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } else if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3 ) { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/toptemplate3.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } else { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/toptemplatedef.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } ?>
  <?php if ($chatroom['toptemplate'] === CHAT_LOG_TEMPLATE1) { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/logtemplate1.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } else { ?>
    <link rel="stylesheet" href="<?php echo h(CHAT_ROOM_SRC_LINK); ?>/css/logtemplatedef.css.php?up=<?php echo h(SITE_UPDATE); ?>">
  <?php } ?>
<?php
  $html = ob_get_clean();
  return $html;
}

// CSS文字列出力
function renderTemplateCssString(array $chatroom): string {
  define('CSS_STRING_MODE', true); // グローバル定数を使用するが、CSS出力時のみのため許容

  $css = '';

  ob_start();
  require(CHAT_ROOM_CSS_FILE_PATH .'roombase.css.php');
  $tmp = ob_get_clean();
  $css = $css .$tmp;

  if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE1) {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'toptemplate1.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  } else if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2) {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'toptemplate2.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  } else if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'toptemplate3.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  } else {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'toptemplatedef.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  }

  if ($chatroom['toptemplate'] === CHAT_LOG_TEMPLATE1) {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'logtemplate1.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  } else {
    ob_start();
    require(CHAT_ROOM_CSS_FILE_PATH .'logtemplatedef.css.php');
    $tmp = ob_get_clean();
    $css = $css .$tmp;
  }

  return $css;
}

// ログファイル出力
// filepath が指定されている場合は、そのファイルを出力。
// filepath が指定されていない場合は、デフォルトのログファイルを出力。
function exportChatLogFile(
  ?string $filepath,
  ?SQLite3 $dbhChatlogs,
  string $entrykey,
  array $chatroom,
  array $chatentries,
  array $dbParams = array()
): string {

  if ($dbhChatlogs === null) {
    return '';
  }

  // 100行ごとにループ
  $beforeid = 0;
  $chatrows = selectEqualChatlogsChunk($dbhChatlogs, 100, $entrykey, $beforeid, $dbParams);
  if ($chatrows === false) {
    return '';
  }

  $firstrow = $chatrows->fetchArray(SQLITE3_ASSOC);
  if ($firstrow === false) {
    return '';
  }

  // ファイルパスが指定されていなかった場合、最初の一行からファイルパスを作成する
  // 出力場所は固定とする
  if (!usedStr($filepath)) {
    $firstDate = $firstrow['created'];
    $dt = new DateTime($firstDate);

    $chatroomTitle = $chatroom['title'];
    $filename = removeUnsafeChars($chatroomTitle);

    $logFileName = $dt->format('Ymd_His') ."_" .$filename .'.html';
    $filepath = CHAT_LOG_HTML_PATH .$logFileName;
  }

  $fp = fopen($filepath, 'w');
  if ($fp === false) {
    return '';
  }

  try {
    // ログより上を出力
    $tplVars = [
      'chatroom' => $chatroom,
      'chatentries' => $chatentries,
    ];
    $htmlTopString = renderTemplateBuffer(
      CHAT_LOG_TEMPLATE_FILE_PATH .'chatlogstart.tpl.php',
      $tplVars,
    );
    fwrite($fp, $htmlTopString);

    // 最初に読んだ1件を先に書く
    $stringHtml = renderChatLog($firstrow, $chatroom);
    fwrite($fp, $stringHtml);
    $beforeid = $firstrow['id'];

    // 同じ結果セットの残りを書く
    while ($row = $chatrows->fetchArray(SQLITE3_ASSOC)) {
      $stringHtml = renderChatLog($row, $chatroom);
      fwrite($fp, $stringHtml);
      $beforeid = $row['id'];
    }

    // 2チャンク目以降
    while (true) {
      $chatrows = selectEqualChatlogsChunk($dbhChatlogs, 100, $entrykey, $beforeid, $dbParams);
      if ($chatrows === false) {
        return '';
      }

      $hasRows = false;
      while ($row = $chatrows->fetchArray(SQLITE3_ASSOC)) {
        $hasRows = true;
        $stringHtml = renderChatLog($row, $chatroom);
        fwrite($fp, $stringHtml);
        $beforeid = $row['id'];
      }

      // 現在のチャンクで1件も取れなかったら終了する
      if (!$hasRows) break;
    }
    // ログより下の出力
    $tplVars = [];
    $htmlEndString = renderTemplateBuffer(
      CHAT_LOG_TEMPLATE_FILE_PATH .'chatlogend.tpl.php',
      $tplVars,
    );
    fwrite($fp, $htmlEndString);
  } finally {
    fclose($fp);
  }

  return $filepath;
}

