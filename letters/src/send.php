<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

loginOnly();

$success = '';
$errors = array();
$inputParams = array();

$inputParams['touserid'] = inputParam('touserid', 20);
$inputParams['tousername'] = inputParam('tousername', 20);
$inputParams['tocharacterid'] = inputParam('tocharacterid', 20);
$inputParams['tofullname'] = inputParam('tofullname', 20);
$inputParams['fromcharacterid'] = inputParam('fromcharacterid', 20);
$inputParams['fromfullname'] = inputParam('fromfullname', 20);
$inputParams['title'] = inputParam('title', 100);
$inputParams['message'] = inputParam('message', 10000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectCharactersId($dbhCharacters, $inputParams['tocharacterid']);
  if (!usedArr($characters)) {
    $errors[] = '宛先の名簿がありません。';
    goto outputPage;
  }
  if (count($characters) !== 1) {
    $errors[] = '宛先の名簿データに不備があります。';
    goto outputPage;
  }
  $character = $characters[0];

  // データ更新
  $inputParams['touserid'] = $character['userid'];
  $inputParams['tousername'] = $character['username'];
  $inputParams['tocharacterid'] = $character['id'];
  $inputParams['tofullname'] = $character['fullname'];

  $mycharacters = selectCharactersMy($dbhCharacters, getUserid(), getUsername());
  if (count($mycharacters) < 1) {
    $errors[] = '私書を使用する場合は、キャラクター登録を行ってください。';
    goto outputPage;
  }

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhInbox = connectRw(INBOX_LETTERS_DB);
$dbhOutbox = connectRw(OUTBOX_LETTERS_DB);

$mycharacters = selectCharactersMy($dbhCharacters, getUserid(), getUsername());

// 入力値チェック
if (!usedStr($inputParams['fromcharacterid'])) {
  $errors[] = '差出人を選択してください。';
}
if (!usedStr($inputParams['title'])) {
  $errors[] = 'タイトルを入力してください。';
}
if (!usedStr($inputParams['message'])) {
  $errors[] = 'メッセージを入力してください。';
}
if (usedStr($inputParams['message']) && mb_strlen($inputParams['message']) > 10000) {
  $errors[] = 'メッセージは最大 10000 文字です。';
}
if (usedArr($errors)) {
  goto outputPage;
}

$characters = selectCharactersId($dbhCharacters, $inputParams['tocharacterid']);
if (!usedArr($characters)) {
  $errors[] = '宛先の名簿がありません。';
  goto outputPage;
}
if (count($characters) !== 1) {
  $errors[] = '宛先の名簿データに不備があります。';
  goto outputPage;
}
$character = $characters[0];

// データ更新
$inputParams['touserid'] = $character['userid'];
$inputParams['tousername'] = $character['username'];
$inputParams['tocharacterid'] = $character['id'];
$inputParams['tofullname'] = $character['fullname'];

$characters = selectCharactersId($dbhCharacters, $inputParams['fromcharacterid']);
if (!usedArr($characters)) {
  $errors[] = '差出人の名簿がありません。';
  goto outputPage;
}
if (count($characters) !== 1) {
  $errors[] = '差出人の名簿データに不備があります。';
  goto outputPage;
}
$mycharacter = $characters[0];

// データ更新
$inputParams['fromcharacterid'] = $mycharacter['id'];
$inputParams['fromfullname'] = $mycharacter['fullname'];

// 不正防止のため、登録するユーザーは必ずセッションから取得
$userid = getUserid();
$username = getUsername();

// 更新
$status = '送信済';
try {
  insertInboxLetters($dbhInbox, $userid, $username, $inputParams);
  $counts = selectInboxLettersFromMessage($dbhInbox, $inputParams['fromcharacterid'], $inputParams['fromfullname'], $inputParams['message']);
  $count = $counts[0]['count'];
  if ($count <= 0) {
    $status = '送信エラー';
  }
} catch (Exception $e) {
  $status = '送信エラー';
}

insertOutboxLetters($dbhOutbox, $userid, $username, $status, $inputParams);

$success = '送信しました。';

/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>私書送信</title>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/<?php echo h(SITE_TEMPLATE); ?>.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/assets/css/user-edit.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">
  <h3 class="frame-title">私書送信</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (usedStr($success)) { /* 成功メッセージ */ ?>
    <div class="mes-wrap">
      <ul class="success-mes-wrap">
        <li class="success-mes"><?php echo h($success); ?></li>
      </ul>
    </div>
  <?php } ?>

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php if (!usedStr($success)) { /* 成功以外にフォームを表示 */ ?>
    <div class="form-wrap">
      <form name="letter-form" class="letter-form" action="./send.php" method="POST">
        <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
        <input type="hidden" name="tocharacterid" value="<?php echo h($inputParams['tocharacterid']); ?>">
        <input type="hidden" name="tofullname" value="<?php echo h($inputParams['tofullname']); ?>">
        <ul class="form-row">
          <li class="form-col-title">宛先</li>
          <li class="form-col-item"><?php echo h($inputParams['tocharacterid']); ?>：<?php echo h($inputParams['tofullname']); ?></li>
        </ul>
        <ul class="form-row fullname-wrap">
          <li class="form-col-title">差出人</li>
          <li class="form-col-item">
            <div class="select-wrap">
              <select name="fromcharacterid">
                <?php foreach ($mycharacters as $character) { ?>
                  <option <?php echo selectedOption($inputParams['fromcharacterid'], $character['id']); ?>  value="<?php echo h($character['id']); ?>"><?php echo h($character['fullname']); ?></option>
                <?php } ?>
              </select>
            </div>
          </li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">タイトル<div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="title" value="<?php echo h($inputParams['title']); ?>" maxlength="100"></li>
          <li class="form-col-note">最大 100 文字</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">メッセージ<div class="mandatory-mark"></li>
          <li class="form-col-item"><textarea name="message" maxlength="10000"><?php echo h($inputParams['message']); ?></textarea></li>
          <li class="form-col-note">最大 10000 文字</li>
        </ul>
        <div class="form-button-wrap">
          <button type="submit">送信</button>
        </div>
      </form>
    </div>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
  </div>

</body>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getPrev()); ?>";
  });
});
</script>
</html>
