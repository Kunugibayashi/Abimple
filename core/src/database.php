<?php
function myPrepare($dbh, $sql, $params = array()) {
  // SQLを出力する場合は以下のコメントを外す。開発時のデバッグ用。
  // --- ここから ---
  // echo $sql;
  // echo '<br>';
  // print_r($params);
  // echo '<br>';
  // --- ここまで ---

  return $dbh->prepare($sql);
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
function setAndArryParam($sql, $params) {
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
function setArryBindValue($stmt, $params) {
  if (!isset($params)) {
    return $stmt;
  }
  if (!is_array($params)) {
    return $stmt;
  }
  foreach ($params as $key => $value) {
    if (usedStr($value)) {
      $stmt->bindValue(":$key", $value);
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
  $dbh = connect($dbname);
  // DB作成
  if (USERS_DB === $dbname) {
    createUsers($dbh);
  } else if (CHARACTERS_DB === $dbname) {
    createCharacters($dbh);
  } else if (CHAT_ADMIN_ROOMS === $dbname) {
    creatAdminrooms($dbh);
  } else if (CHAT_ROOM_DB === $dbname) {
    createChatroom($dbh);
  } else if (CHAT_ENTRIES_DB === $dbname) {
    createChatentries($dbh);
  } else if (CHAT_LOGS_DB === $dbname) {
    createChatlogs($dbh);
  } else if (INBOX_DB === $dbname) {
    createInbox($dbh);
  } else if (OUTBOX_DB === $dbname) {
    createOutbox($dbh);
  } else if (BBSID_DB === $dbname) {
    createBbsParentId($dbh);
  } else if (BBS_DB === $dbname) {
    createBbs($dbh);
  } else if (INFOMATION_DB === $dbname) {
    createInfomation($dbh);
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

  $users = selectUserId($dbh, $userid);
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

function selectUserId($dbh, $id) {
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

function selectUsername($dbh, $username) {
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

function selectUserList($dbh, $params = array()) {
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
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectUserMyList($dbh, $userid, $username) {
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
function createInfomation($dbh) {
  $sql = "
    CREATE TABLE infomation (
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

function selectInfomationList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      title,
      created,
      modified
    FROM infomation
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInfomationMessage($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM infomation
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function insertInfomation($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO infomation (
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function updateInfomation($dbh, $id, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE infomation
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteInfomation($dbh, $id) {
  $sql = '
    DELETE FROM infomation
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * チャットルーム管理
 * ****************************************************************************
 */
function creatAdminrooms($dbh) {
  $sql = "
    CREATE TABLE adminrooms (
      id        INTEGER        PRIMARY KEY AUTOINCREMENT,
      roomdir   VARCHAR(20)    NOT NULL UNIQUE,
      roomname  VARCHAR(20)    NOT NULL,
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
  $stmt = setArryBindValue($stmt, $params);
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteCharacters($dbh, $userid, $username, $params = array()) {
  $sql = '
    DELETE FROM characters
    WHERE
      userid = :userid
    AND
      username = :username
  ';
  $sql = setAndArryParam($sql, $params);

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectCharacterId($dbh, $characterid) {
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

function selectCharacterList($dbh, $params = array()) {
  // detail は重くなるため一覧には表示しない
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
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectCharacterMyList($dbh, $userid, $username) {
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

function selectCharacterView($dbh, $characterid) {
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

/* ****************************************************************************
 * チャットルーム設定
 * ****************************************************************************
 */
function createChatroom($dbh) {
  $sql = "
    CREATE TABLE chatroom (
      id            INTEGER        PRIMARY KEY AUTOINCREMENT,
      title         VARCHAR(100)   NOT NULL,
      guide         TEXT           NOT NULL,
      toptemplate   VARCHAR(20)    NOT NULL DEFAULT 'default',
      logtemplate   VARCHAR(20)    NOT NULL DEFAULT 'default',
      isfree        INTEGER        NOT NULL DEFAULT 0,
      color         VARCHAR(7)     NOT NULL DEFAULT '#696969',
      bgcolor       VARCHAR(7)     NOT NULL DEFAULT '#f5f5f5',
      bgimage       TEXT           NOT NULL DEFAULT '',
      omi1flg       INTEGER        NOT NULL DEFAULT 0,
      omi1name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi1text      TEXT           NOT NULL DEFAULT '',
      omi2flg       INTEGER        NOT NULL DEFAULT 0,
      omi2name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi2text      TEXT           NOT NULL DEFAULT '',
      omi3flg       INTEGER        NOT NULL DEFAULT 0,
      omi3name      VARCHAR(10)    NOT NULL DEFAULT '',
      omi3text      TEXT           NOT NULL DEFAULT '',

      created DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime')),
      modified DATETIME NOT NULL DEFAULT (DATETIME('now', 'localtime'))
    )
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertInitChatroom($dbh) {
  $sql = "
    INSERT INTO chatroom (
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

function selectChatroomConfig($dbh) {
  $sql = '
    SELECT
      *
    FROM chatroom
    WHERE
      id = 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function updateChatroomConfig($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE chatroom
    SET
      modified = (DATETIME('now', 'localtime')),
  ";
  $sql = setUpdateArryParam($sql, $params);
  $sql = $sql. "
    WHERE
      id = 1
  ";

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * 私書テーブル（受信BOX）
 * ****************************************************************************
 */
function createInbox($dbh) {
 $sql = "
   CREATE TABLE inbox (
      id            INTEGER        PRIMARY KEY AUTOINCREMENT,
      touserid        INTEGER        NOT NULL,
      tousername      VARCHAR(20)    NOT NULL,
      tocharacterid   INTEGER        NOT NULL,
      tofullname      INTEGER        NOT NULL,
      fromcharacterid VARCHAR(20)    NOT NULL,
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
   CREATE INDEX IF NOT EXISTS idx_inbox_touserid_tousername ON inbox(touserid, tousername);
 ";

 $results = $dbh->query($sql);
 if (!$results) {
   echo $dbh->lastErrorMsg();
 }
}

function insertInbox($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO inbox (
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteInboxId($dbh, $id) {
  $sql = '
    DELETE FROM inbox
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function selectInboxPublicTitleList($dbh, $params = array()) {
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
      FROM inbox
      GROUP BY
        tocharacterid
    ) AS box1
    LEFT JOIN inbox AS box2 ON box1.id = box2.id
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxTitleList($dbh, $params = array()) {
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
    FROM inbox AS box1
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxMessageList($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM inbox
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectInboxMyList($dbh, $userid, $username) {
  // SQLiteの場合、IN句は1000件以上の場合だとエラーとなる。1000件以上になる場合は修正すること。
  $sql = '
    SELECT
      *
    FROM inbox AS box1
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

/* ****************************************************************************
 * 私書テーブル（送信BOX）
 * ****************************************************************************
 */
function createOutbox($dbh) {
  $sql = "
    CREATE TABLE outbox (
      id              INTEGER        PRIMARY KEY AUTOINCREMENT,
      touserid        INTEGER        NOT NULL,
      tousername      VARCHAR(20)    NOT NULL,
      tocharacterid   INTEGER        NOT NULL,
      tofullname      INTEGER        NOT NULL,
      fromcharacterid VARCHAR(20)    NOT NULL,
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
    CREATE INDEX IF NOT EXISTS idx_outbox_touserid ON outbox(touserid);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertOutbox($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO outbox (
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteOutboxId($dbh, $id) {
  $sql = '
    DELETE FROM outbox
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function selectOutboxMyList($dbh, $userid, $username) {
  // SQLiteの場合、IN句は1000件以上の場合だとエラーとなる。1000件以上になる場合は修正すること。
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
    FROM outbox
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

function selectOutboxMessageList($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM outbox
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY modified DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setLikeArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
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
  $stmt = setArryBindValue($stmt, $params);
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectChatentries($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatentries
    WHERE
      deleteflg = 0
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectLogChatentries($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatentries
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * ログ
 * ****************************************************************************
 */
function createChatlogs($dbh) {
  $sql = "
    CREATE TABLE chatlogs (
      id          INTEGER        PRIMARY KEY AUTOINCREMENT,
      entrykey    VARCHAR(40)    NOT NULL,
      characterid INTEGER        NOT NULL,
      fullname    VARCHAR(20)    NOT NULL,
      color       VARCHAR(7)     NOT NULL DEFAULT '#000000',
      bgcolor     VARCHAR(7)     NOT NULL DEFAULT '#ffffff',
      memo        VARCHAR(200)   NOT NULL DEFAULT '',
      message     TEXT           NOT NULL,

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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectChatlogs($dbh, $limit, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectChatlogsMylist($dbh, $limit, $entrykey, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      entrykey = :entrykey
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $stmt->bindValue(':entrykey', $entrykey);
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function updateChatlogsId($dbh, $id, $params = array()) {
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

/* ****************************************************************************
 * BBS親記事ID管理
 * ****************************************************************************
 */
function createBbsParentId($dbh) {
  $sql = "
    CREATE TABLE bbsparentid (
      id          INTEGER        PRIMARY KEY AUTOINCREMENT,

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

function insertBbsParentId($dbh, $userid, $username) {
  $sql = '
    INSERT INTO bbsparentid (
      userid,
      username
    ) VALUES (
      :userid,
      :username
    )
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':userid', $userid);
  $stmt->bindValue(':username', $username);
  $results = $stmt->execute();
  return $results;
}

function selectBbsParentId($dbh) {
  $sql = '
    SELECT
      id
    FROM bbsparentid
    ORDER BY id DESC
    Limit 1
  ';

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

/* ****************************************************************************
 * BBS記事
 * ****************************************************************************
 */
function createBbs($dbh) {
  $sql = "
    CREATE TABLE bbs (
      id          INTEGER        PRIMARY KEY AUTOINCREMENT,

      parentid    INTEGER        NOT NULL,
      depth       INTEGER        NOT NULL DEFAULT 0,

      toid        INTEGER,
      touserid    INTEGER,
      tousername  VARCHAR(20),
      toname      VARCHAR(20),
      totitle     VARCHAR(100),

      fromname    VARCHAR(20)    NOT NULL DEFAULT '',
      title       VARCHAR(100)   NOT NULL DEFAULT '',
      message     TEXT           NOT NULL DEFAULT '',
      image       VARCHAR(60)    NOT NULL DEFAULT '',

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
    CREATE INDEX IF NOT EXISTS idx_bbs_parentid ON bbs(parentid);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }

  $sql = "
    CREATE INDEX IF NOT EXISTS idx_bbs_touserid_tousername ON bbs(touserid, tousername);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertBbs($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO bbs (
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectBbsParentTitleList($dbh) {
  $sql = '
    SELECT
      bbs2.parentid,
      bbs2.modified,
      bbs2.articlenum,
      bbs3.title,
      bbs3.fromname
    FROM
    (
      SELECT
        MIN(id) AS id,
        parentid,
        MAX(modified) AS modified,
        COUNT(parentid) AS articlenum
      FROM bbs AS bbs1
      GROUP BY
        parentid
      ORDER BY modified DESC
    ) AS bbs2
    LEFT JOIN bbs AS bbs3 ON bbs2.id = bbs3.id;
  ';

  $stmt = myPrepare($dbh, $sql);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectBbsListParent($dbh, $parentid) {
  $sql = '
    SELECT
      *
    FROM bbs
    WHERE
      depth = 0
    AND
      parentid = :parentid
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':parentid', $parentid);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectBbsListChild($dbh, $parentid) {
  $sql = '
    SELECT
      *
    FROM bbs
    WHERE
      depth <> 0
    AND
      parentid = :parentid
    ORDER BY id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':parentid', $parentid);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectBbsId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM bbs
    WHERE
      id = :id
    ORDER BY id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function updateBbsId($dbh, $id, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE bbs
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
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteBbsId($dbh, $id) {
  $sql = '
    DELETE FROM bbs
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function selectBbsTitle($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      parentid,
      toname,
      fromname,
      title,
      modified
    FROM bbs
    WHERE
      id IS NOT NULL
  ';
  $sql = setAndArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setArryBindValue($stmt, $params);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}
