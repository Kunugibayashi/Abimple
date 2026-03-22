<?php
/**
 * 出力ログレベル
 *
 * 4:エラー
 * 3:警告
 * 2:情報メッセージ
 * 1:デバッグ
 * 0:ログを表示しない
 */
define('LOG_LEVEL', 1);
/* ログレベル */
define('ERROR', 4);
define('WARN',  3);
define('INFO',  2);
define('DEBUG', 1);
define('NONE',  0);
/* ログファイル */
define('LOG_OUTPUT_PATH', (__DIR__ . '/../../systemlog/')); // ファイル内で簡潔させるため相対PATH指定
define('LOG_FILE_ERROR',  (LOG_OUTPUT_PATH.'error.log'));
define('LOG_FILE_WARN',   (LOG_OUTPUT_PATH.'warning.log'));
define('LOG_FILE_INFO',   (LOG_OUTPUT_PATH.'info.log'));
define('LOG_FILE_DEBUG',  (LOG_OUTPUT_PATH.'debug.log'));
/* ログ出力SQLを表示するか */
define('IS_OUTPUT_LOG_SQL', 0);

/**
 * 共通ログ出力
 */
function writeLog($level, $message) {

  // レベル判定。閾値以上のみ出力。
  if ($level < LOG_LEVEL) {
    return;
  }

  // レベル文字列
  $levelStr = getLogLevelString($level);

  // 出力ファイル
  $logfile = getLogFilePath($level);

  // 呼び出し元取得
  $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
  $caller = isset($trace[2]) ? $trace[2] : [];

  $function = isset($caller['function']) ? $caller['function'] : 'global';
  $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';

  // セッション取得ラッパーの場合は global へ
  if ($function === 'sessionLogChatEntryCharacter') {
    $function = 'global';
  }

  // 頻繁に出力されるログ系SQLは出力しないように設定可能
  if ($function === 'myPrepare') {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
    $caller = isset($trace[3]) ? $trace[3] : [];
    $function = isset($caller['function']) ? $caller['function'] : 'global';

    if (!IS_OUTPUT_LOG_SQL &&
      ($function === 'selectCharactersId'
        || $function === 'selectEqualAppendChatlogs'
        || $function === 'selectEqualUpdateChatlogs'
        || $function === 'selectChatroomsConfig'
        || $function === 'selectEqualChatentries'
        || $function === 'insertChatlogs'
      )
    ) {
      return;
    }
  }

  // 日時
  $datetime = date('Y-m-d H:i:s');

  // 1行ログ生成
  $logline = sprintf(
    '[%s] %s：<%s> %s - %s' .PHP_EOL,
    $levelStr,
    $datetime,
    $function,
    sanitizeLogMessage($message),
    $file
  );

  // ディレクトリ作成
  if (!is_dir(LOG_OUTPUT_PATH)) {
    mkdir(LOG_OUTPUT_PATH, 0777, true);
  }

  // 書き込み
  file_put_contents($logfile, $logline, FILE_APPEND | LOCK_EX);
}

/**
 * ラッパー
 */
function logError($message) {
  writeLog(ERROR, $message);
}

function logWarn($message) {
  writeLog(WARN, $message);
}

function logInfo($message) {
  writeLog(INFO, $message);
}

function logDebug($message) {
  writeLog(DEBUG, $message);
}

/**
 * レベル→文字列
 */
function getLogLevelString($level) {
  switch ($level) {
    case ERROR: return 'ERROR';
    case WARN:  return 'WARN';
    case INFO:  return 'INFO';
    case DEBUG: return 'DEBUG';
  }
  return 'UNKNOWN';
}

/**
 * レベル→ファイル
 */
function getLogFilePath($level) {
  switch ($level) {
    case ERROR: return LOG_FILE_ERROR;
    case WARN:  return LOG_FILE_WARN;
    case INFO:  return LOG_FILE_INFO;
    case DEBUG: return LOG_FILE_DEBUG;
  }
  return LOG_FILE_ERROR;
}

/**
 * メッセージ整形
 */
function sanitizeLogMessage($message) {
  // 改行・制御文字除去
  $message = str_replace(["\r", "\n"], ' ', $message);

  return $message;
}

