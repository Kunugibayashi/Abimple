<?php
/* セッション保持変数 設計図 */
const SESSION_SCHEMA_AUTH = [
  'userid' => [
    'type' => 'int|string',
    'group' => 'auth',
    'note' => 'ログインユーザーID',
  ],
  'username' => [
    'type' => 'string',
    'group' => 'auth',
    'note' => 'ログインユーザー名',
  ],
];
const SESSION_SCHEMA_NAV = [
  'prevpage' => [
    'type' => 'string',
    'group' => 'nav',
    'note' => '一覧に戻るページ',
  ],
];
const SESSION_SCHEMA_SECRET = [
  'secretkeyword' => [
    'type' => 'string',
    'group' => 'secret',
    'note' => '秘匿キーワード',
  ],
];
const SESSION_SCHEMA_SEARCH = [
  'search' => [
    'type' => 'array[pageKey][param]',
    'group' => 'search',
    'note' => 'ページ別検索条件',
  ],
];
const SESSION_SCHEMA_TOKEN = [
  'token' => [
    'type' => 'array[pageKey]',
    'group' => 'token',
    'note' => 'ページ別CSRFトークン',
  ],
  'chatentertokenkey' => [
    'type' => 'string',
    'group' => 'token',
    'note' => 'チャット入室専用トークンの保存キー',
  ],
];
const SESSION_SCHEMA_CHAT = [
  'chatentry' => [
    'type' => 'array',
    'group' => 'chat',
    'note' => 'chatentry[characterid] = entry data',
  ],
  'chattoken' => [
    'type' => 'array',
    'group' => 'chat',
    'note' => 'chattoken[characterid] = token',
  ],
];

/* 設計図全体 */
const SESSION_SCHEMA = [
  'auth' => SESSION_SCHEMA_AUTH,
  'nav' => SESSION_SCHEMA_NAV,
  'secret' => SESSION_SCHEMA_SECRET,
  'search' => SESSION_SCHEMA_SEARCH,
  'token' => SESSION_SCHEMA_TOKEN,
  'chat' => SESSION_SCHEMA_CHAT,
];

/* 使用定数 */
const PREV_ALLOWED_SCRIPTS = [
  'mylist.php',
  'list.php',
  'publiclist.php',
  'index-top.php',
  'inboxmylist.php',
  'outboxmylist.php',
  'inouthistorylist.php',
];

session_start();
setPrev(); // リクエストごとに取得

/* ユーザーID */
function setUserid($userid): void {
  $_SESSION['userid'] = $userid;
}
function getUserid() {
  return $_SESSION['userid'] ?? '';
}
function clearUserid(): void {
  unset($_SESSION['userid']);
}

/* ユーザー名 */
function setUsername($username): void {
  $_SESSION['username'] = $username;
}
function getUsername() {
  return $_SESSION['username'] ?? '';
}
function clearUsername(): void {
  unset($_SESSION['username']);
}

/* 一覧に戻る */
function setPrev(): void {
  $reqUri = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'] ?? '';
  if ($reqUri === '') {
    return;
  }
  $path = parse_url($reqUri, PHP_URL_PATH) ?? '';
  $query = parse_url($reqUri, PHP_URL_QUERY) ?? '';
  $script = basename($path);
  if (!in_array($script, PREV_ALLOWED_SCRIPTS, true)) {
    return;
  }
  $prevpage = $path;
  if ($query !== '') {
    $prevpage .= '?' . $query;
  }
  $_SESSION['prevpage'] = $prevpage;
}
function getPrev(): string {
  return $_SESSION['prevpage'] ?? './';
}

/* 悪戯対策。管理人ではない場合、本人以外は処理をしない。 */
function identityUser($userid, $username): void {
  if (isAdmin()) {
    return;
  }
  if ((string)$userid !== (string)getUserid()
    || (string)$username !== (string)getUsername()
  ) {
    echo '不正なリクエストです。';
    exit;
  }
}

/* 秘匿キーワード */
function setSecretKeyword($param): void {
  $_SESSION['secretkeyword'] = $param;
}
function getSecretKeyword() {
  return $_SESSION['secretkeyword'] ?? '';
}
function clearSecretKeyword(): void {
  unset($_SESSION['secretkeyword']);
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
function setSearchParam($params): void {
  $pageKey = getPageKey();
  $_SESSION['search'][$pageKey] = $params;
}
function searchParam($key, $num) {
  $pageKey = getPageKey();
  if (isset($_SESSION['search'][$pageKey][$key])) {
    return mb_substr($_SESSION['search'][$pageKey][$key], 0, (int)$num);
  }
  return '';
}

/* トークン生成 */
function createToken() {
  $token = bin2hex(random_bytes(32));
  return $token;
}

/* ページごとに保存するトークン用 */
function setToken(): void {
  $pageKey = getPageKey();
  $_SESSION['token'][$pageKey] = createToken();
}
function getToken() {
  $pageKey = getPageKey();
  return $_SESSION['token'][$pageKey] ?? '';
}
function checkToken(): void {
  $pageKey = getPageKey();
  $sessionToken = $_SESSION['token'][$pageKey] ?? '';
  checkErrorToken($sessionToken);
}
function checkErrorToken($sessionToken): void {
  if (!usedStr($sessionToken)) {
    echo 'トークンがありません。画面更新をしてください。';
    exit;
  }
  $postToken = $_POST['token'] ?? '';
  if (!usedStr($postToken) || $sessionToken !== $postToken) {
    echo 'POSTに失敗しました。画面更新をしてください。';
    exit;
  }
}

/* チャットルーム入室の処理のみ特殊のため専用関数を設ける */
function setChatEnterToken(): void {
  $pageKeyRoomEnter = getPageKey();
  $_SESSION['chatentertokenkey'] = $pageKeyRoomEnter;
  $_SESSION['token'][$pageKeyRoomEnter] = createToken();
}
function getChatEnterToken(): string {
  $pageKeyRoomEnter = (string)($_SESSION['chatentertokenkey'] ?? '');
  if ($pageKeyRoomEnter === '') {
    return '';
  }
  return $_SESSION['token'][$pageKeyRoomEnter] ?? '';
}
function checkChatEnterToken(): void {
  $postToken = $_POST['token'] ?? '';
  $sessionToken = getChatEnterToken();
  if (!usedStr($sessionToken)) {
    echo 'トークンがありません。画面更新をしてください。';
    exit;
  }
  if (!usedStr($postToken) || $sessionToken !== $postToken) {
    echo 'POSTに失敗しました。画面更新をしてください。';
    exit;
  }
}
function clearChatEnterToken(): void {
  $pageKeyRoomEnter = (string)($_SESSION['chatentertokenkey'] ?? '');
  if ($pageKeyRoomEnter !== '') {
    unset($_SESSION['token'][$pageKeyRoomEnter]);
  }
  unset($_SESSION['chatentertokenkey']);
}

/* 入室情報の保存（characterid単位） */
function setChatEntry(array $params): void {
  $characterid = (string)($params['characterid'] ?? '');
  if ($characterid === '') {
    return;
  }
  $_SESSION['chatentry'][$characterid] = [
    'roomdir' => (string)($params['roomdir'] ?? ''),
    'entrykey' => (string)($params['entrykey'] ?? ''),
    'characterid' => $characterid,
    'charactername' => (string)($params['charactername'] ?? ''),
    'color' => (string)($params['color'] ?? ''),
    'bgcolor' => (string)($params['bgcolor'] ?? ''),
    'memo' => (string)($params['memo'] ?? ''),
    'inoutmesflg' => (string)($params['inoutmesflg'] ?? '0'),
  ];
}
/* 指定キャラの入室情報取得 */
function getChatEntry($characterid): array {
  $characterid = (string)$characterid;
  return $_SESSION['chatentry'][$characterid] ?? [];
}
/* 全入室情報取得 */
function getChatEntries(): array {
  return $_SESSION['chatentry'] ?? [];
}
/* 指定キャラの入室情報削除 */
function clearChatEntry($characterid): void {
  $characterid = (string)$characterid;
  unset($_SESSION['chatentry'][$characterid]);
}
/* 全入室情報削除 */
function clearChatEntries(): void {
  unset($_SESSION['chatentry']);
}
/* 指定キャラが入室しているか */
function isChatEntry($characterid): bool {
  $characterid = (string)$characterid;
  return isset($_SESSION['chatentry'][$characterid])
    && (string)($_SESSION['chatentry'][$characterid]['roomdir'] ?? '') !== '';
}
/* 指定キャラの現在ルーム取得 */
function getCharacterRoomEntry($characterid): string {
  $characterid = (string)$characterid;
  return $_SESSION['chatentry'][$characterid]['roomdir'] ?? '';
}
/* 指定キャラが特定ルームにいるか */
function isNowRoomEntry($characterid, $roomdir): bool {
  $characterid = (string)$characterid;
  $roomdir = (string)$roomdir;

  $nowRoom = $_SESSION['chatentry'][$characterid]['roomdir'] ?? '';
  return $nowRoom === $roomdir;
}
/* 指定ルームに入室しているキャラクター名一覧取得 */
function getRoomChatCharacternames($roomdir): array {
  $roomdir = (string)$roomdir;
  $entries = $_SESSION['chatentry'] ?? [];
  if (!is_array($entries) || $roomdir === '') {
    return [];
  }
  $result = [];
  foreach ($entries as $entry) {
    if (!is_array($entry)) {
      continue;
    }
    if ((string)($entry['roomdir'] ?? '') !== $roomdir) {
      continue;
    }
    $name = (string)($entry['charactername'] ?? '');
    if ($name !== '') {
      $result[] = $name;
    }
  }
  return $result;
}

/* チャットトークン保存（characterid単位） */
function setChatToken($characterid): void {
  $characterid = (string)$characterid;
  $_SESSION['chattoken'][$characterid] = createToken();
}
/* 指定キャラのトークン取得 */
function getChatToken($characterid): string {
  $characterid = (string)$characterid;
  return $_SESSION['chattoken'][$characterid] ?? '';
}
/* 指定キャラのトークン削除 */
function clearChatToken($characterid): void {
  $characterid = (string)$characterid;
  unset($_SESSION['chattoken'][$characterid]);
}
/* 全トークン削除 */
function clearChatTokens(): void {
  unset($_SESSION['chattoken']);
}
/* 指定キャラのトークンチェック */
function checkChatToken($characterid): void {
  $characterid = (string)$characterid;
  $sessionToken = $_SESSION['chattoken'][$characterid] ?? '';
  $postToken = $_POST['token'] ?? '';
  if (!usedStr($sessionToken)) {
    echo 'トークンがありません。画面更新をしてください。';
    exit;
  }
  if (!usedStr($postToken) || $sessionToken !== $postToken) {
    echo 'POSTに失敗しました。画面更新をしてください。';
    exit;
  }
}

