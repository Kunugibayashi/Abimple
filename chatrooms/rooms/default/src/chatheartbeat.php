<?php
/* ログに発言を出力する。
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

$jsonArray = array();

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';
$jsonArray['onlinecount'] = '―';

// DB接続
$dbhChatonlines = connectRw(__DIR__ .'/' .CHAT_ONLINES_DB);

$sessionid = session_id();
$userid = (int)getUserid() ?: 0;

$modified = date('Y-m-d H:i:s');
$modifiedlimit = date('Y-m-d H:i:s', strtotime('-60 seconds'));

// アクセスを登録
insertChatonlines($dbhChatonlines, $sessionid, $userid, $modified);

// カウント数を取得
$onlinecounts = selectOnlineCount($dbhChatonlines, $modifiedlimit);
$onlinecount = $onlinecounts[0]['onlinecount']; // インサート後のため、必ずある想定

$jsonArray['onlinecount'] = $onlinecount;

// 出力
$json =  json_encode($jsonArray);
setJsonHeader();
echo($json);
