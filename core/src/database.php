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
  } else if (ADMIN_ROOMS_DB === $dbname) {
    creatAdminrooms($dbh);
  } else if (CHAT_ROOMS_DB === $dbname) {
    createChatrooms($dbh);
  } else if (CHAT_ENTRIES_DB === $dbname) {
    createChatentries($dbh);
  } else if (CHAT_LOGS_DB === $dbname) {
    createChatlogs($dbh);
  } else if (INBOX_LETTERS_DB === $dbname) {
    createInboxLetters($dbh);
  } else if (OUTBOX_LETTERS_DB === $dbname) {
    createOutboxLetters($dbh);
  } else if (BBS_PARENTS_DB === $dbname) {
    createBbsParents($dbh);
  } else if (BBS_DB === $dbname) {
    createBbs($dbh);
  } else if (INFOMATIONS_DB === $dbname) {
    createInfomations($dbh);
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
 * チャットルーム管理
 * ****************************************************************************
 */
function creatAdminrooms($dbh) {
  $sql = "
    CREATE TABLE adminrooms (
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

function insertAdminrooms($dbh, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO adminrooms (
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

function deleteAdminrooms($dbh, $id) {
  $sql = '
    DELETE FROM adminrooms
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  return $results;
}

function updateAdminrooms($dbh, $id, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = "
    UPDATE adminrooms
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

function selectAdminroomsId($dbh, $id) {
  $sql = '
    SELECT
      *
    FROM adminrooms
    WHERE
      id = :id
  ';

  $stmt = myPrepare($dbh, $sql);
  $stmt->bindValue(':id', $id);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
}

function selectEqualAdminroomsList($dbh, $params = array()) {
  $sql = '
    SELECT
      id,
      roomdir,
      roomtitle,
      published,
      displayno,
      created,
      modified
    FROM adminrooms
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
      isframe       INTEGER        NOT NULL DEFAULT 0,
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
      deck1flg      INTEGER        NOT NULL DEFAULT 0,
      deck1name     VARCHAR(10)    NOT NULL DEFAULT '',
      deck1text     TEXT           NOT NULL DEFAULT '',

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
 * 私書テーブル（受信BOX）
 * ****************************************************************************
 */
function createInboxLetters($dbh) {
 $sql = "
   CREATE TABLE inboxletters (
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
      *
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
    CREATE INDEX IF NOT EXISTS idx_outbox_touserid ON outboxletters(touserid);
  ";

  $results = $dbh->query($sql);
  if (!$results) {
    echo $dbh->lastErrorMsg();
  }
}

function insertOutboxLetters($dbh, $userid, $username, $params = array()) {
  unset($params['id']);
  unset($params['created']);
  unset($params['modified']);

  $sql = '
    INSERT INTO outboxletters (
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

function selectOutboxMessageId($dbh, $id) {
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

function selectEqualLogChatentries($dbh, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatentries
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
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectEqualChatlogs($dbh, $limit, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
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

function selectEqualChatlogsEntrykey($dbh, $limit, $entrykey, $params = array()) {
  $sql = '
    SELECT
      *
    FROM chatlogs
    WHERE
      entrykey = :entrykey
  ';
  $sql = setAndEqualArryParam($sql, $params);
  $sql = $sql .'
    ORDER BY id DESC
    LIMIT :limit
  ';

  $stmt = myPrepare($dbh, $sql, $params);
  $stmt = setEqualArryBindValue($stmt, $params);
  $stmt->bindValue(':entrykey', $entrykey);
  $stmt->bindValue(':limit', $limit);
  $results = $stmt->execute();
  $data = fetchArraytoArray($results);
  return $data;
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

/* ****************************************************************************
 * BBS親記事ID管理
 * ****************************************************************************
 */
function createBbsParents($dbh) {
  $sql = "
    CREATE TABLE bbsparents (
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

function insertBbsParents($dbh, $userid, $username) {
  $sql = '
    INSERT INTO bbsparents (
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

function selectBbsParents($dbh) {
  $sql = '
    SELECT
      id
    FROM bbsparents
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
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function selectBbsParentsTitleTop($dbh) {
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

function selectBbsListParentid($dbh, $parentid) {
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

function selectBbsListChildParentid($dbh, $parentid) {
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

function updateBbs($dbh, $id, $params = array()) {
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
  $stmt = setEqualArryBindValue($stmt, $params);
  $results = $stmt->execute();
  return $results;
}

function deleteBbs($dbh, $id) {
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

function selectEqualBbsTitleList($dbh, $params = array()) {
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
