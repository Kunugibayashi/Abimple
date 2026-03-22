<?php
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

require_once(__DIR__ .'/../../../../core/src/lib/exportlib.php');
require_once(__DIR__ .'/../../../../core/src/lib/templatelib.php');

require_once(__DIR__ .'/../../../src/chatlogexport.php');

adminOnly();

ini_set('memory_limit', PHP_MEMORY_LIMIT);

$success = '';
$errors = array();
$inputParams = array();

// roomdir
$roomdir = getPageRoomdir();
$ROOMDIR_TMP_PATH = INDEX_ROOT .'/chatrooms/tmp';

// DB接続
$dbhChatrooms  = connectRo(CHAT_ROOMS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

// 全て出力するため必要項目以外は設定しない
$entrykey = '';
$chatentries = [];

// ささやきログを取得
$dbParams = [
  'whisperflg' => '1'
];

// tmp ファイルに出力する
$filepath = $ROOMDIR_TMP_PATH .'/' .'admin_whisperlog_'. $chatroom['id'] .'.html';

// ログ出力
$filepath = exportChatLogFile($filepath, $dbhChatlogs, $entrykey, $chatroom, $chatentries, $dbParams);
if (!usedStr($filepath)) {
}
$logFileName = basename($filepath);

// DL名
$siteTitle = removeUnsafeChars(SITE_TITLE);
$downloadName = nowYmdhi() .'_' .$siteTitle .'_' .'admin_whisperlog_'. $chatroom['id'] .'.html';

// HTMLとしてダウンロード
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header(
  'Content-Disposition: attachment; ' .
  'filename="' .$downloadName .'"; ' .
  "filename*=UTF-8''" .rawurlencode($downloadName)
);
header('Connection: close');

// 出力バッファクリア
while (ob_get_level()) { ob_end_clean(); }

// HTML内容を出力
readfile($filepath);

exit;
