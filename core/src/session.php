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
];
const SESSION_SCHEMA_CHAT = [
  'chatentry' => [
    'type' => 'array{
      roomdir:string,
      entrykey:string,
      characterid:int|string,
      color:string,
      bgcolor:string,
      memo:string
    }',
    'group' => 'chat',
    'note' => 'チャット入室情報',
  ],
  'chattoken' => [
    'type' => 'string',
    'group' => 'chat',
    'note' => 'チャット共通トークン',
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

/* チャットルームトークン
 * ページを跨ぐため、チャットで共通のトークンを使用。
 */
function setChatEntry(array $params): void {
  $_SESSION['chatentry'] = [
    'roomdir' => (string)($params['roomdir'] ?? ''),
    'entrykey' => (string)($params['entrykey'] ?? ''),
    'characterid' => $params['characterid'] ?? '',
    'color' => (string)($params['color'] ?? ''),
    'bgcolor' => (string)($params['bgcolor'] ?? ''),
    'memo' => (string)($params['memo'] ?? ''),
  ];
}
function getChatEntry(): array {
  return $_SESSION['chatentry'] ?? [];
}
function clearChatEntry(): void {
  unset($_SESSION['chatentry']);
}
function isChatEntry(): bool {
  return isset($_SESSION['chatentry']['roomdir']);
}
function getNowRoomEntry(): string {
  return $_SESSION['chatentry']['roomdir'] ?? '';
}
/* チャット情報 */
function setChatToken(): void {
  $_SESSION['chattoken'] = createToken();
}
function getChatToken(): string {
  return $_SESSION['chattoken'] ?? '';
}
function clearChatToken(): void {
  unset($_SESSION['chattoken']);
}
function checkChatToken(): void {
  $sessionToken = $_SESSION['chattoken'] ?? '';
  checkErrorToken($sessionToken);
}

/* 現在の入室チェック */
function isNowRoomEntry($roomdir) {
  $nowRoom = getNowRoomEntry();
  if ($nowRoom != $roomdir) {
    return false;
  }
  return true;
}

