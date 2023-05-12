<?php
session_start();
setPrev(); // リクエストごとに取得

/* 一覧に戻るページを保持する
 */
function setPrev() {
  $reqUri = $_SERVER['REQUEST_URI'];
  if (!isset($reqUri)) {
    $reqUri = $_SERVER['SCRIPT_NAME'];
  }
  $path = parse_url($reqUri, PHP_URL_PATH);
  $query = parse_url($reqUri, PHP_URL_QUERY); // なければ NULL
  $reqUri = preg_replace('/^.*\//', '', $reqUri);
  $script = preg_replace('/\?.*$/', '', $reqUri);

  // 以下の処理は前ページを詐称されても戻るページに影響しないようにする
  if ($script === 'mylist.php'
    || $script === 'list.php'
    || $script === 'publiclist.php'
    || $script === 'bbslist.php'
    || $script === 'index-top.php'
    || $script === 'inboxmylist.php'
    || $script === 'outboxmylist.php'
  ) {
    $prevPage = $path;
    if (((boolean) $query)) {
      $prevPage = $prevPage .'?' . $query;
    }
    $_SESSION['prevPage'] = $prevPage;
  }
  if ($script === 'bbstop.php') {
    $prevTopPage = $path;
    if (((boolean) $query)) {
      $prevTopPage = $prevTopPage .'?' . $query;
    }
    $_SESSION['prevTopPage'] = $prevTopPage;
  }
  // その他は設定しない
}
function getPrev() {
  if (isset($_SESSION['prevPage'])) {
    return $_SESSION['prevPage'];
  }
  // ない場合は自分自身
  return './';
}
function getTopPrev() {
  if (isset($_SESSION['prevTopPage'])) {
    return $_SESSION['prevTopPage'];
  }
  // ない場合は自分自身
  return './';
}

/* ユーザーID
 */
function setUserid($userid) {
  $_SESSION['userid'] = $userid;
}

function getUserid() {
  if (isset($_SESSION['userid'])) {
    return $_SESSION['userid'];
  }
  return '';
}

/* ユーザー名
 */
function setUsername($username) {
  $_SESSION['username'] = $username;
}

function getUsername() {
  if (isset($_SESSION['username'])) {
    return $_SESSION['username'];
  }
  return '';
}

/* 悪戯対策。管理人ではない場合、本人以外は処理をしない。
 */
function identityUser($userid, $username) {
  if (isAdmin()){
    // 管理人の場合は全て許可する
  } else if ($userid != getUserid() || $username != getUsername()) { /* セッションは文字列のため厳密な比較はしない */
    echo '不正なリクエストです。';
    exit;
  }
}

/* 表示ページごとに一意のキーを発行。
 * ページごとにセッションを管理したい場合に使用。
 */
function getPageKey() {
  $key = preg_replace('/(\/)|(\.)/i', '_', $_SERVER['SCRIPT_NAME']);
  return $key;
}

/* 検索値
 * ページごとに保存。
 */
function getSearchKey() {
  $searchKey = getPageKey();
  $searchKey = $searchKey .'_search';
  return $searchKey;
}

function setSearchParam($params) {
  $searchKey = getSearchKey();
  // そのままセッションに保存するため、input内容が巨大にならないように注意すること
  $_SESSION[$searchKey] = $params;
}

function searchParam($key, $num) {
  $searchKey = getSearchKey();
  if (isset($_SESSION[$searchKey]) && isset($_SESSION[$searchKey][$key])) {
    $value = $_SESSION[$searchKey][$key];
    return mb_substr($value, 0, $num);
  }
  return '';
}

/* トークン
 * ページごとに保存。
 */
function createToken() {
 $token = sha1(uniqid(mt_rand(), true));
 return $token;
}

function getTokenKey() {
  $key = getPageKey();
  $key = $key .'_token';
  return $key;
}

function setToken() {
  $tokenKey = getTokenKey();
  $token = createToken();
  $_SESSION[$tokenKey] = $token;
}

function getToken() {
  $tokenKey = getTokenKey();
  if (isset($_SESSION[$tokenKey])) {
    return $_SESSION[$tokenKey];
  }
  return '';
}

function checkToken() {
  $tokenKey = getTokenKey();
  chekErrorToken($tokenKey);
}

function chekErrorToken($tokenKey) {
  if (!usedStr($_SESSION[$tokenKey])) {
    echo 'トークンがありません。画面更新をしてください。';
    exit;
  }
  if ($_SESSION[$tokenKey] !== $_POST['token']) {
    echo 'POSTに失敗しました。画面更新をしてください。';
    echo ' tokenKey=' .$tokenKey;
    echo ' SESSION=' .$_SESSION[$tokenKey];
    echo ' POST=' .$_POST['token'];
    exit;
  }
}

/* チャットルームトークン
 * ページを跨ぐため、チャットで共通のトークンを使用。
 */
function setChatToken() {
  $tokenKey = 'chat_token';
  $token = createToken();
  $_SESSION[$tokenKey] = $token;
}

function getChatToken() {
  $tokenKey = 'chat_token';
  if (isset($_SESSION[$tokenKey])) {
    return $_SESSION[$tokenKey];
  }
  return '';
}

function checkChatToken() {
  $tokenKey = 'chat_token';
  chekErrorToken($tokenKey);
}

/* チャット情報
 */
function setChatEntry($params) {
  $_SESSION['chat_entry'] = $params;
}

function getChatEntry() {
  if (isset($_SESSION['chat_entry'])) {
    return $_SESSION['chat_entry'];
  }
  return '';
}

function isChatEntry() {
  if (!isset($_SESSION['chat_entry'])) {
    return false;
  }
  if (!isset($_SESSION['chat_entry']['roomdir'])) {
    return false;
  }
  return true;
}

function getNowRoomEntry() {
  if (!isset($_SESSION['chat_entry'])) {
    return '';
  }
  if (!isset($_SESSION['chat_entry']['roomdir'])) {
    return '';
  }
  return $_SESSION['chat_entry']['roomdir'];
}

function isNowRoomEntry($roomdir) {
  $nowRoom = getNowRoomEntry();
  if ($nowRoom != $roomdir) {
    return false;
  }
  return true;
}
