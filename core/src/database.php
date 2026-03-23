<?php
require_once(__DIR__ .'/logger.php');

// SQL実行ラッパー（ログ出力あり）
function myPrepare($dbh, $sql, $params = array()) {

  $sql = preg_replace('/\s+/', ' ', $sql);
  logDebug('sql = ' .$sql);

  logDebug('arguments = ' .json_encode($params, JSON_UNESCAPED_UNICODE));

  $stmt = $dbh->prepare($sql);
  if ($stmt === false) {
    logError('SQL prepare failed: ' . $dbh->lastErrorMsg());
  }

  return $stmt;
}

// 日付形式のチェック
function isValidDatetime(?string $value): bool {
  if ($value === null || $value === '') {
    return false;
  }
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
  return $dt && $dt->format('Y-m-d H:i:s') === $value;
}

// 配列内の値を取得。ない場合も空で登録するため
function columnParam($array, $key) {
  if (!isset($array)) {
    return '';
  }
  $value = $array[$key];
  if (!isset($value)) {
    return '';
  }
  if ($value === NULL) {
    return '';
  }
  if ($value === '') {
    return '';
  }
  return $value;
}

// 結果を配列にして返却
function fetchArraytoArray($results) {
  $data = array();
  // カラム名をインデックスとする配列指定
  while ($result= $results->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $result;
  }
  return $data;
}

// 配列をLIKE文のAND条件としてSQLに結合
function setAndLikeArryParam($sql, $params) {
  if (!isset($params)) {
    return $sql;
  }
  if (!is_array($params)) {
    return $sql;
  }
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $sql = $sql ." AND $key LIKE :$key";
    }
  }
  return $sql;
}

// 配列を＝文のAND条件としてSQLに結合
function setAndEqualArryParam($sql, $params) {
  if (!isset($params)) {
    return $sql;
  }
  if (!is_array($params)) {
    return $sql;
  }
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $sql = $sql ." AND $key = :$key";
    }
  }
  return $sql;
}

// 配列をアップデート文としてSQLに結合
function setUpdateArryParam($sql, $params) {
  if (!isset($params)) {
    return $sql;
  }
  if (!is_array($params)) {
    return $sql;
  }
  $tmp = array();
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $tmp[] = "$key = :$key";
    } else if ($value === "") {
      $tmp[] = "$key = :$key";
    }
  }
  $sql = $sql .implode(", ", $tmp);
  return $sql;
}

// 配列をインサートのカラム文としてSQLに結合
function setInsertColumnArryParam($sql, $params) {
  if (!isset($params)) {
    return $sql;
  }
  if (!is_array($params)) {
    return $sql;
  }
  $tmp = array();
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $tmp[] = "$key";
    }
  }
  $sql = $sql .implode(", ", $tmp);
  return $sql;
}

function setInsertVluesArryParam($sql, $params) {
  if (!isset($params)) {
    return $sql;
  }
  if (!is_array($params)) {
    return $sql;
  }
  $tmp = array();
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $tmp[] = ":$key";
    }
  }
  $sql = $sql .implode(", ", $tmp);
  return $sql;
}

// 配列をバインド（前後一致）
function setLikeArryBindValue($stmt, $params) {
  if (!isset($params)) {
    return $stmt;
  }
  if (!is_array($params)) {
    return $stmt;
  }
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $stmt->bindValue(":$key", "%$value%");
    }
  }
  return $stmt;
}

// 配列をバインド（完全一致）
function setEqualArryBindValue($stmt, $params) {
  if (!isset($params)) {
    return $stmt;
  }
  if (!is_array($params)) {
    return $stmt;
  }
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $stmt->bindValue(":$key", $value);
    } else if ($value === "") {
      $stmt->bindValue(":$key", "");
    }
  }
  return $stmt;
}

/* ****************************************************************************
 * DB
 * ****************************************************************************
 */
// DB接続（テーブル作成）
function connect($dbname) {
  try {
    $dbh = new SQLite3($dbname);
    $dbh->exec('PRAGMA journal_mode = WAL;');
    $dbh->exec('PRAGMA synchronous = NORMAL;');
    $dbh->exec('PRAGMA busy_timeout = 3000;');
  } catch (Exception $e) {
    echo $e->getMessage();
  }
  return $dbh;
}

function checkDB($dbname) {
  // DBファイルが存在する場合は作成しない
  if (file_exists($dbname)) {
    return;
  }

  $dbname = (string)($dbname ?? '');
  if ($dbname === '') {
    return;
  }

  $dbh = connect($dbname);
  // DB作成
  if (USERS_DB === $dbname) {
    createUsers($dbh);
  } else if (INFOMATIONS_DB === $dbname) {
    createInfomations($dbh);
  } else if (CHARACTERS_DB === $dbname) {
    createCharacters($dbh);
  } else if (ROOMS_DB === $dbname) {
    creatRooms($dbh);
  } else if (ROOM_INOUT_HISTORIES_DB === $dbname) {
    createRoominouthistories($dbh);
  } else if (strpos($dbname, CHAT_ROOMS_DB) !== false) {
    createChatrooms($dbh);
  } else if (strpos($dbname, CHAT_ENTRIES_DB) !== false) {
    createChatentries($dbh);
  } else if (strpos($dbname, CHAT_LOGS_DB) !== false) {
    createChatlogs($dbh);
  } else if (CHAT_LOG_FILES_DB === $dbname) {
    createChatlogfiles($dbh);
  } else if (CHAT_SECRETS_DB === $dbname) {
    createChatsecrets($dbh);
  } else if (INBOX_LETTERS_DB === $dbname) {
    createInboxLetters($dbh);
  } else if (OUTBOX_LETTERS_DB === $dbname) {
    createOutboxLetters($dbh);
  }
  // この関数内のみでコネクションを完結する
  $dbh->close();
}

// DB接続（読み込みと書き込み）
function connectRw($dbname) {
  checkDB($dbname);
  $dbh = connect($dbname);
  return $dbh;
}

// DB接続（読み込み専用）
function connectRo($dbname) {
  checkDB($dbname);
  try {
    $dbh = new SQLite3($dbname, SQLITE3_OPEN_READONLY);
    $dbh->exec('PRAGMA busy_timeout = 3000;');
  } catch (Exception $e) {
    echo $e->getMessage();
  }
  return $dbh;
}

/* ****************************************************************************
 * ログインチェック
 * 管理側から削除された場合、ユーザー側でログイン時の処理ができないよう、
 * DBに存在するかをチェックする
 * ****************************************************************************
 */
function isLogin() {
  $userid = getUserid();
  $username = getUsername();

  if (!usedStr($userid)) {
    return false;
  }
  if (!usedStr($username)) {
    return false;
  }

  // DB接続
  $dbname = USERS_DB;
  $dbh = connectRo($dbname);

  $users = selectUsersId($dbh, $userid);
  if (!usedArr($users)) {
    return false;
  }

  $user = $users[0];
  if (!usedStr($user['username'])) {
    return false;
  }

  // DBの設定によって同じIDが使いまわされた場合のための処理
  if ($user['username'] !== $username) {
    return false;
  }

  // この関数内のみでコネクションを完結する
  $dbh->close();

  return true;
}

function loginOnly() {
  $result = isLogin();
  if (!$result) {
    echo 'この機能はログイン時のみ許可されています。';
    exit;
  }
}


/* ****************************************************************************
 * ユーザーテーブル
 * ****************************************************************************
 */
function createUsers($dbh) {
  $sql = "
    CREATE TABLE users (
      id       INTEGER      PRIMARY KEY AUTOINCREMENT,
      username VARCHAR(20)  NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertUsers($dbh, $username, $password) {
  // セキュリティのためパスワードをハッシュ化
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $sql = '
    INSERT INTO users (
      username,
      password
    ) VALUES (
      :username,
      :password
    )
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':username', $username);
  $stmt->bindValue(':password', $hash);
  $results = $stmt->execute();
  return $results;
}

function deleteUsers($dbh, $userid, $username) {
  $sql = '
    DELETE FROM users
    WHERE
      id = :userid
    AND
      username = :username
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  return $results;
}

function selectUsersId($dbh, $id) {
  $sql = '
    SELECT
      id,
      username
    FROM users
    WHERE
      id = :id
    LIMIT 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectUsersUsername($dbh, $username) {
  $sql = '
    SELECT
      id,
      username,
      password
    FROM users
    WHERE
      username = :username
    LIMIT 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectLikeUsersList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      username,
      created,
      modified
    FROM users
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndLikeArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectUsersMy($dbh, $userid, $username) {
  $sql = '
    SELECT
      id,
      username,
      created,
      modified
    FROM users
    WHERE
      id = :userid
    AND
      username = :username
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}


/* ****************************************************************************
 * お知らせテーブル
 * ****************************************************************************
 */
function createInfomations($dbh) {
  $sql = "
    CREATE TABLE informations (
      id              INTEGER        PRIMARY KEY AUTOINCREMENT,
      title           VARCHAR(100)   NOT NULL,
      message         TEXT           NOT NULL DEFAULT '',

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function selectEqualInfomationsList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      title,
      created,
      modified
    FROM informations
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInfomationsId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM informations
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function insertInfomations($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO informations (
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function updateInfomations($dbh, $id, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE informations
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql ."
    WHERE
      id = :id
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':id', $id);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteInfomations($dbh, $id) {
  $sql = '
    DELETE FROM informations
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * キャラクターテーブル
 * ****************************************************************************
 */
function createCharacters($dbh) {
  $sql = "
    CREATE TABLE characters (
      id        INTEGER      PRIMARY KEY AUTOINCREMENT,
      fullname  VARCHAR(20)  NOT NULL DEFAULT '',
      color     VARCHAR(7)   NOT NULL DEFAULT '#000000',
      bgcolor   VARCHAR(7)   NOT NULL DEFAULT '#ffffff',
      gender    VARCHAR(10)  NOT NULL DEFAULT '',
      species   VARCHAR(10)  NOT NULL DEFAULT '',
      team      VARCHAR(10)  NOT NULL DEFAULT '',
      job       VARCHAR(10)  NOT NULL DEFAULT '',
      free1     VARCHAR(20)  NOT NULL DEFAULT '',
      free2     VARCHAR(20)  NOT NULL DEFAULT '',
      free3     VARCHAR(20)  NOT NULL DEFAULT '',
      free4     VARCHAR(20)  NOT NULL DEFAULT '',
      free5     VARCHAR(20)  NOT NULL DEFAULT '',
      free6     VARCHAR(20)  NOT NULL DEFAULT '',
      free7     VARCHAR(20)  NOT NULL DEFAULT '',
      free8     VARCHAR(20)  NOT NULL DEFAULT '',
      free9     VARCHAR(20)  NOT NULL DEFAULT '',
      free10    TEXT         NOT NULL DEFAULT '',
      free11    TEXT         NOT NULL DEFAULT '',
      free12    TEXT         NOT NULL DEFAULT '',
      comment   VARCHAR(100) NOT NULL DEFAULT '',
      url       TEXT         NOT NULL DEFAULT '',
      detail    TEXT         NOT NULL DEFAULT '',

      userid INTEGER NOT NULL,
      username VARCHAR(20) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertCharacters($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO characters (
      userid,
      username,
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
      :userid,
      :username,
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function updateCharacters($dbh, $characterid, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE characters
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql ."
    WHERE
      id = :characterid
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':characterid', $characterid);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteEqualCharacters($dbh, $userid, $username, $params = array()) {
  $sql = '
    DELETE FROM characters
    WHERE
      userid = :userid
    AND
      username = :username
  ';
  $sql = setAndEqualArryParam($sql, $params);

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectCharactersId($dbh, $characterid) {
  $sql = '
    SELECT
      *
    FROM characters
    WHERE
      id = :characterid
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':characterid', $characterid);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectLikeCharactersList($dbh, $params = array()) {
  // TEXT は重くなるため一覧には表示しない
  $sql = '
    SELECT
      id,
      fullname,
      color,
      bgcolor,
      gender,
      species,
      team,
      job,
      free1,
      free2,
      free3,
      free4,
      free5,
      free6,
      free7,
      free8,
      free9,
      comment,
      url,
      userid,
      username,
      created,
      modified
    FROM characters
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndLikeArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectCharactersMy($dbh, $userid, $username) {
  $sql = '
    SELECT
      id,
      fullname,
      color,
      bgcolor,
      comment,
      userid,
      username,
      created,
      modified
    FROM characters
    WHERE
      userid = :userid
    AND
      username = :username
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function getAllCharacterIds($dbh, $params = array()) {
  $sql = '
    SELECT
      id
    FROM characters
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id ASC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * チャットルーム管理
 * ****************************************************************************
 */
function creatRooms($dbh) {
  $sql = "
    CREATE TABLE rooms (
      id        INTEGER        PRIMARY KEY AUTOINCREMENT,
      roomdir   VARCHAR(20)    NOT NULL UNIQUE,
      roomtitle VARCHAR(100)   NOT NULL,
      published INTEGER        NOT NULL DEFAULT 0,
      displayno INTEGER        NOT NULL DEFAULT 0,

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertRooms($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO rooms (
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteRooms($dbh, $id) {
  $sql = '
    DELETE FROM rooms
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function updateRooms($dbh, $id, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE rooms
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql ."
    WHERE
      id = :id
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':id', $id);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectRoomsId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM rooms
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualRoomsList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      roomdir,
      roomtitle,
      published,
      displayno,
      created,
      modified
    FROM rooms
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY displayno
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * 入退室履歴
 * ****************************************************************************
 */
function createRoominouthistories($dbh) {
  $sql = "
    CREATE TABLE roominouthistories (
      id             INTEGER        PRIMARY KEY AUTOINCREMENT,
      roomtitle      VARCHAR(100)   NOT NULL,
      message        TEXT           NOT NULL,

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertRoominouthistories($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO roominouthistories (
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectRoominouthistories($dbh, $limit, $params = array()) {
  $sql = '
    SELECT
      *
    FROM roominouthistories
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function deleteRoominouthistoriesLimit1000($dbh) {
  $sql = "
    DELETE FROM roominouthistories
    WHERE
      id NOT IN
      (
        SELECT id
        FROM roominouthistories
        ORDER BY id DESC
        LIMIT 1000
      )
  ";

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  return $results;
}

function deleteRoominouthistories($dbh, $id) {
  $sql = '
    DELETE FROM roominouthistories
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * チャットルーム設定
 * ****************************************************************************
 */
function createChatrooms($dbh) {
  $sql = "
    CREATE TABLE chatrooms (
      id            INTEGER        PRIMARY KEY AUTOINCREMENT,
      title         VARCHAR(100)   NOT NULL,
      guide         TEXT           NOT NULL,
      toptemplate   VARCHAR(20)    NOT NULL DEFAULT 'default',
      logtemplate   VARCHAR(20)    NOT NULL DEFAULT 'default',
      isfree        INTEGER        NOT NULL DEFAULT 0,
      issecret      INTEGER        NOT NULL DEFAULT 0,
      color         VARCHAR(7)     NOT NULL DEFAULT '#696969',
      bgcolor       VARCHAR(7)     NOT NULL DEFAULT '#f5f5f5',
      bgimage       TEXT           NOT NULL DEFAULT '',
      omi1flg       INTEGER        NOT NULL DEFAULT 0,
      omi1type      INTEGER        NOT NULL DEFAULT 0,
      omi1name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi1text      TEXT           NOT NULL DEFAULT '',
      omi2flg       INTEGER        NOT NULL DEFAULT 0,
      omi2type      INTEGER        NOT NULL DEFAULT 0,
      omi2name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi2text      TEXT           NOT NULL DEFAULT '',
      omi3flg       INTEGER        NOT NULL DEFAULT 0,
      omi3type      INTEGER        NOT NULL DEFAULT 0,
      omi3name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi3text      TEXT           NOT NULL DEFAULT '',
      deck1flg      INTEGER        NOT NULL DEFAULT 0,
      deck1type     INTEGER        NOT NULL DEFAULT 0,
      deck1name     VARCHAR(10)    NOT NULL DEFAULT '',
      deck1text     TEXT           NOT NULL DEFAULT '',
      roomcss       TEXT           NOT NULL DEFAULT '',

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertInitChatrooms($dbh) {
  $sql = "
    INSERT INTO chatrooms (
      title,
      guide
    ) VALUES (
      'チャットルームタイトル',
      'チャットルーム説明'
    )
  ";

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  return $results;
}

function selectChatroomsConfig($dbh) {
  $sql = '
    SELECT
      *
    FROM chatrooms
    WHERE
      id = 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function updateChatroomsConfig($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE chatrooms
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql. "
    WHERE
      id = 1
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * 入室状態
 * ****************************************************************************
 */
function createChatentries($dbh) {
  $sql = "
    CREATE TABLE chatentries (
      id          INTEGER        PRIMARY KEY AUTOINCREMENT,
      entrykey    VARCHAR(40)    NOT NULL,
      deleteflg   INTEGER        NOT NULL DEFAULT 0,

      characterid INTEGER        NOT NULL,
      fullname    VARCHAR(20)    NOT NULL,
      color       VARCHAR(7)     NOT NULL DEFAULT '#696969',
      bgcolor     VARCHAR(7)     NOT NULL DEFAULT '#f5f5f5',

      userid INTEGER NOT NULL,
      username VARCHAR(20) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_chatentries_entrykey_id ON chatentries(entrykey, id);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertChatentries($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO chatentries (
      userid,
      username,
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
      :userid,
      :username,
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function updateChatentries($dbh, $characterid, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE chatentries
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql ."
    WHERE
      characterid = :characterid
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':characterid', $characterid);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectEqualChatentries($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatentries
    WHERE
      deleteflg = 0
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualLogChatentries($dbh, $entrykey, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatentries
    WHERE
      deleteflg = 1
    AND
      entrykey = :entrykey
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':entrykey', $entrykey);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function deleteChatentriesExit($dbh) {
  $sql = "
    DELETE FROM chatentries
    WHERE
      deleteflg = 1
  ";

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * ログ
 * ****************************************************************************
 */
function createChatlogs($dbh) {
  $sql = "
    CREATE TABLE chatlogs (
      id             INTEGER        PRIMARY KEY AUTOINCREMENT,
      logtype        INTEGER        NOT NULL DEFAULT 0,

      entrykey       VARCHAR(40)    NOT NULL,
      characterid    INTEGER        NOT NULL,
      fullname       VARCHAR(20)    NOT NULL,
      color          VARCHAR(7)     NOT NULL DEFAULT '#000000',
      bgcolor        VARCHAR(7)     NOT NULL DEFAULT '#ffffff',
      memo           VARCHAR(200)   NOT NULL DEFAULT '',
      message        TEXT           NOT NULL,

      whisperflg     INTEGER        NOT NULL DEFAULT 0,
      wtocharacterid INTEGER        NOT NULL DEFAULT -1,
      wtofullname    VARCHAR(20)    NOT NULL DEFAULT '',

      userid INTEGER NOT NULL,
      username VARCHAR(20) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_chatroom_entrykey_id ON chatlogs(entrykey, id);
    CREATE INDEX IF NOT EXISTS idx_chatlogs_entrykey_modified ON chatlogs(entrykey, modified);
    CREATE INDEX IF NOT EXISTS idx_chatlogs_entrykey_modified_id ON chatlogs(entrykey, modified, id);
    CREATE INDEX IF NOT EXISTS idx_chatlogs_modified ON chatlogs(modified);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertChatlogs($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO chatlogs (
      userid,
      username,
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
      :userid,
      :username,
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectEqualAppendChatlogs($dbh, $limit, $dommaxid = 0,
    $isinroom = 0, $characterid = null,
    $params = array()
  ) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      id IS NOT NULL
    AND
    (
      whisperflg = 0
  ';
  // 入室時はささやきを表示する（送信者本人 or 宛先本人）
  if ($isinroom) {
    $sql = $sql .'
      OR
      (
        whisperflg = 1
        AND
        (
          characterid = :characterid
          OR
          wtocharacterid = :wtocharacterid
        )
      )
    ';
  }
  $sql = $sql . '
    )
  ';
  if ($dommaxid != 0) {
    $sql = $sql .'
      AND
        id > :dommaxid
    ';
  }
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  if ($isinroom) {
    $stmt->bindValue(':characterid', $characterid);
    $stmt->bindValue(':wtocharacterid', $characterid);
  }
  if ($dommaxid != 0) {
    $stmt->bindValue(':dommaxid', $dommaxid);
  }
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualUpdateChatlogs($dbh, $limit, $dommaxid = 0,
    $domminid = 0, $syncmodifiedts = 0,
    $isinroom = 0, $characterid = null,
    $params = array()
  ) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      id IS NOT NULL
    AND
    (
      whisperflg = 0
  ';
  // 入室時はささやきを表示する（送信者本人 or 宛先本人）
  if ($isinroom) {
    $sql = $sql .'
      OR
      (
        whisperflg = 1
        AND
        (
          characterid = :characterid
          OR
          wtocharacterid = :wtocharacterid
        )
      )
    ';
  }
  $sql = $sql . '
    )
  ';
  $sql = $sql .'
    AND
      (
        :domminid <= id AND id <= :dommaxid
      )
  ';
  if (usedStr($syncmodifiedts) && $syncmodifiedts != 0) {
    $sql = $sql .'
      AND
        modified > :syncmodifiedts
    ';
  }
  $sql = $sql . '
    AND
      created != modified
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  if ($isinroom) {
    $stmt->bindValue(':characterid', $characterid);
    $stmt->bindValue(':wtocharacterid', $characterid);
  }
  $stmt->bindValue(':domminid', $domminid);
  $stmt->bindValue(':dommaxid', $dommaxid);
  if (usedStr($syncmodifiedts) && $syncmodifiedts != 0) {
    $stmt->bindValue(':syncmodifiedts', $syncmodifiedts);
  }
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualChatlogsEdit($dbh, $limit, $characterid, $isAdmin, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
    (
      (
        whisperflg = 0
  ';
  if ($isAdmin != 1) {
    $sql = $sql .'
        AND
          characterid = :characterid
    ';
  }
  $sql = $sql .'
        )
      OR
      (
        whisperflg = 1
        AND
        (
          characterid = :characterid
          OR
          wtocharacterid = :wtocharacterid
        )
      )
    )
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $stmt->bindValue(':characterid', $characterid);
  $stmt->bindValue(':wtocharacterid', $characterid);
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualChatlogsChunk($dbh, $limit, $entrykey = '', $beforeid = 0, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      id IS NOT NULL
  ';
  if ($entrykey != '') {
    $sql = $sql .'
      AND
        entrykey = :entrykey
    ';
  }
  if ($beforeid != 0) {
    $sql = $sql .'
      AND
        id < :beforeid
    ';
  }
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  if ($entrykey != '') {
    $stmt->bindValue(':entrykey', $entrykey, SQLITE3_TEXT);
  }
  if ($beforeid != 0) {
    $stmt->bindValue(':beforeid', $beforeid, SQLITE3_INTEGER);
  }
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $results = $stmt->execute();
  // ログ出力負荷軽減のため fetchArraytoArray は噛ませない
  return $results;
}

function updateChatlogs($dbh, $id, $params = array()) {
  $sql = "
    UPDATE chatlogs
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql ."
    WHERE
      id = :id
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':id', $id);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteChatlogsLimit10000($dbh) {
  $sql = "
    DELETE FROM chatlogs
    WHERE
      id NOT IN
      (
        SELECT id
        FROM chatlogs
        ORDER BY id DESC
        LIMIT 10000
      )
  ";

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  return $results;
}

function deleteChatlogs($dbh) {
  $sql = "
    DELETE FROM chatlogs
  ";

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * 秘匿ルーム用
 * ****************************************************************************
 */
function createChatsecrets($dbh) {
  $sql = "
    CREATE TABLE chatsecrets (
      id          INTEGER        PRIMARY KEY AUTOINCREMENT,
      keyword     VARCHAR(20)    NOT NULL,

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertChatsecrets($dbh, $keyword) {
  $sql = '
    INSERT INTO chatsecrets (
      keyword
  ';
  $sql = $sql .'
    ) VALUES (
      :keyword
  ';
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':keyword', $keyword);
  $results = $stmt->execute();
  return $results;
}

function updateChatsecrets($dbh, $keyword) {
  $sql = "
    UPDATE chatsecrets
    SET
      modified = (DATETIME('now', 'localtime')),
      keyword = :keyword
  ";
  $sql = $sql ."
    WHERE
      id = 1
  ";

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':keyword', $keyword);
  $results = $stmt->execute();
  return $results;
}

function selectChatsecrets($dbh) {
  $sql = '
    SELECT
      *
    FROM chatsecrets
    WHERE
      id = 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * 私書テーブル（受信BOX）
 * ****************************************************************************
 */
function createInboxLetters($dbh) {
  $sql = "
    CREATE TABLE inboxletters (
      id              INTEGER        PRIMARY KEY AUTOINCREMENT,
      touserid        INTEGER        NOT NULL,
      tousername      VARCHAR(20)    NOT NULL,
      tocharacterid   INTEGER        NOT NULL,
      tofullname      VARCHAR(20)    NOT NULL,
      fromcharacterid INTEGER        NOT NULL,
      fromfullname    VARCHAR(20)    NOT NULL,
      title           VARCHAR(100)   NOT NULL,
      message         TEXT           NOT NULL DEFAULT '',

      userid INTEGER NOT NULL,
      username VARCHAR(20) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_inbox_touserid_tousername ON inboxletters(touserid, tousername);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertInboxLetters($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO inboxletters (
      userid,
      username,
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
      :userid,
      :username,
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteInboxLetters($dbh, $id) {
  $sql = '
    DELETE FROM inboxletters
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function selectEqualInboxLettersPublicTitleList($dbh, $params = array()) {
  $sql = '
    SELECT
      box2.id,
      box2.touserid,
      box2.tousername,
      box2.tocharacterid,
      box2.tofullname,
      box2.fromcharacterid,
      box2.fromfullname,
      box2.title,
      box2.modified
    FROM
    (
      select
        MAX(id) AS id
      FROM inboxletters
      GROUP BY
        tocharacterid
    ) AS box1
    LEFT JOIN inboxletters AS box2 ON box1.id = box2.id
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualInboxLettersTitleList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      touserid,
      tousername,
      tocharacterid,
      tofullname,
      fromcharacterid,
      fromfullname,
      title,
      modified
    FROM inboxletters AS box1
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualInboxLettersList($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM inboxletters
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxLettersId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM inboxletters
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxLettersMy($dbh, $userid, $username) {
  $sql = '
    SELECT
      id,
      title,
      tofullname,
      fromfullname,
      modified
    FROM inboxletters AS box1
    WHERE
      touserid = :userid
    AND
      tousername = :username
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxLettersFromMessage($dbh, $fromcharacterid, $fromfullname, $message) {
  $sql = '
    SELECT
      COUNT(*) AS count
    FROM inboxletters AS box1
    WHERE
      fromcharacterid = :fromcharacterid
    AND
      fromfullname = :fromfullname
    AND
      message = :message
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':fromcharacterid', $fromcharacterid);
  $stmt->bindValue(':fromfullname', $fromfullname);
  $stmt->bindValue(':message', $message);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * 私書テーブル（送信BOX）
 * ****************************************************************************
 */
function createOutboxLetters($dbh) {
  $sql = "
    CREATE TABLE outboxletters (
      id              INTEGER        PRIMARY KEY AUTOINCREMENT,
      touserid        INTEGER        NOT NULL,
      tousername      VARCHAR(20)    NOT NULL,
      tocharacterid   INTEGER        NOT NULL,
      tofullname      VARCHAR(20)    NOT NULL,
      fromcharacterid INTEGER        NOT NULL,
      fromfullname    VARCHAR(20)    NOT NULL,
      title           VARCHAR(100)   NOT NULL,
      message         TEXT           NOT NULL DEFAULT '',
      status          VARCHAR(20)    NOT NULL,

      userid INTEGER NOT NULL,
      username VARCHAR(20) NOT NULL,
      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_outbox_touserid ON outboxletters(touserid);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertOutboxLetters($dbh, $userid, $username, $status, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO outboxletters (
      userid,
      username,
      status,
  ';
  $sql = setInsertColumnArryParam($sql, $params);
  $sql = $sql .'
    ) VALUES (
      :userid,
      :username,
      :status,
  ';
  $sql = setInsertVluesArryParam($sql, $params);
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt->bindValue(':status', $status);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteOutboxLetters($dbh, $id) {
  $sql = '
    DELETE FROM outboxletters
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function selectOutboxLettersMy($dbh, $userid, $username) {
  $sql = '
    SELECT
      id,
      touserid,
      tousername,
      tocharacterid,
      tofullname,
      fromcharacterid,
      fromfullname,
      title,
      status,
      modified
    FROM outboxletters
    WHERE
      userid = :userid
    AND
      username = :username
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectOutboxLettersId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM outboxletters
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * ログファイル一覧
 * ****************************************************************************
 */
function createChatlogfiles($dbh) {
  $sql = "
    CREATE TABLE chatlogfiles (
      id             INTEGER        PRIMARY KEY AUTOINCREMENT,
      entrykey       VARCHAR(40)    NOT NULL,
      roomdir        VARCHAR(20)    NOT NULL,

      roomtitle      VARCHAR(100)   NOT NULL,
      filename       VARCHAR(40)    NOT NULL,
      entries        TEXT           NOT NULL DEFAULT '',

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_chatlogfiles_roomdir ON chatlogfiles(roomdir);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function selectEqualChatlogfiles($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      roomdir,
      roomtitle,
      filename,
      entries,
      created
    FROM chatlogfiles
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function insertChatlogfiles($dbh, $entrykey, $roomdir, $roomtitle, $filename, $entries) {
  $sql = '
    INSERT INTO chatlogfiles (
      entrykey,
      roomdir,
      roomtitle,
      filename,
      entries
  ';
  $sql = $sql .'
    ) VALUES (
      :entrykey,
      :roomdir,
      :roomtitle,
      :filename,
      :entries
  ';
  $sql = $sql .'
    )
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':entrykey', $entrykey);
  $stmt->bindValue(':roomdir', $roomdir);
  $stmt->bindValue(':roomtitle', $roomtitle);
  $stmt->bindValue(':filename', $filename);
  $stmt->bindValue(':entries', $entries);
  $results = $stmt->execute();
  return $results;
}

function selectChatlogfilesById($dbh, $id) {
  $sql = '
    SELECT
      id,
      roomdir,
      filename
    FROM chatlogfiles
    WHERE
      id = :id
    LIMIT 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function deleteChatlogfilesById($dbh, $id) {
  $sql = '
    DELETE FROM chatlogfiles
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * チャットルーム用関数
 * ****************************************************************************
 */
function firstAccessChatroom($dbname) {
  $dbh = connectRw($dbname);
  insertInitChatrooms($dbh);
  // この関数内のみでコネクションを完結する
  $dbh->close();
}

function firstAccessChatsecrets($dbname) {
  $dbh = connectRw($dbname);
  insertChatsecrets($dbh, '');
  // この関数内のみでコネクションを完結する
  $dbh->close();
}

