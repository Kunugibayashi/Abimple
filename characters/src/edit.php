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

$inputParams['id'] = inputParam('id', 20);
$inputParams['fullname'] = inputParam('fullname', 20);
$inputParams['color'] = inputParam('color', 7);
$inputParams['bgcolor'] = inputParam('bgcolor', 7);
$inputParams['gender'] = inputParam('gender', 10);
$inputParams['species'] = inputParam('species', 10);
$inputParams['team'] = inputParam('team', 10);
$inputParams['job'] = inputParam('job', 10);
$inputParams['free1'] = inputParam('free1', 20);
$inputParams['free2'] = inputParam('free2', 20);
$inputParams['free3'] = inputParam('free3', 20);
$inputParams['free4'] = inputParam('free4', 20);
$inputParams['free5'] = inputParam('free5', 20);
$inputParams['free6'] = inputParam('free6', 20);
$inputParams['free7'] = inputParam('free7', 20);
$inputParams['free8'] = inputParam('free8', 20);
$inputParams['free9'] = inputParam('free9', 20);
$inputParams['comment'] = inputParam('comment', 100);
$inputParams['url'] = inputParam('url', 1000);
$inputParams['detail'] = inputParam('detail', 10000);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // 初回はGETでIDを取得
  $inputParams['id'] = getParam('id');

  if (!usedStr($inputParams['id'])) {
    $errors[] = 'キャラクターIDが不正です。';
    goto outputPage;
  }

  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectCharactersId($dbhCharacters, $inputParams['id']);
  if (!usedArr($characters)) {
    $errors[] = '名簿がありません。';
    goto outputPage;
  }
  if (count($characters) !== 1) {
    $errors[] = '名簿データに不備があります。';
    goto outputPage;
  }
  $character = $characters[0];

  // 本人確認
  identityUser($character['userid'], $character['username']);

  // 画面表示のため詰め替え
  $inputParams = $character;

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhCharacters = connectRw(CHARACTERS_DB);

$characters = selectCharactersId($dbhCharacters, $inputParams['id']);
if (!usedArr($characters)) {
  $errors[] = '名簿がありません。';
  goto outputPage;
}
if (count($characters) !== 1) {
  $errors[] = '名簿データに不備があります。';
  goto outputPage;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

// キャラクター更新
$updateCharacter = $inputParams;
updateCharacters($dbhCharacters, $character['id'], $updateCharacter);
$success = '更新が完了しました。';

// 情報更新
$characters = selectCharactersId($dbhCharacters, $character['id']);
// 画面表示のため詰め替え
$inputParams = $characters[0];

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
  <title>名簿更新</title>
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
  <h3 class="frame-title">名簿更新</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">すべての名簿の更新が可能</span>です。<br>
        管理ユーザーは非表示にしているすべてのカラムが表示されます。<br>
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

  <?php if (!usedStr($success) && usedArr($inputParams) && usedStr($inputParams['id'])) { /* 処理が成功でない & データがある場合は表示 */ ?>
    <div class="form-wrap">
      <form name="characters-form" class="characters-form" action="./edit.php" method="POST">
        <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
        <input type="hidden" name="id" value="<?php echo h($inputParams['id']); ?>">
        <ul class="form-row">
          <li class="form-col-title"><?php echo h(NAMELIST_ID); ?></li>
          <li class="form-col-item"><?php echo h($inputParams['id']); ?></li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title"><?php echo h(NAMELIST_NAME); ?><div class="mandatory-mark"></div></li>
          <li class="form-col-item"><input type="text" name="fullname" value="<?php echo h($inputParams['fullname']); ?>" maxlength="20"></li>
          <li class="form-col-note">最大 20 文字</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">基本文字色<div class="optional-mark"></div></li>
          <li class="form-col-item">
            <div class="form-col-item-group">
              <input type="text" name="color" value="<?php echo h($inputParams['color']); ?>" maxlength="7">
              <input type="color" class="select-color" value="<?php echo h($inputParams['color']); ?>">
            </div>
          </li>
          <li class="form-col-note">文字色コードを入力。右側アイコンで色選択できます。</li>
        </ul>
        <ul class="form-row">
          <li class="form-col-title">基本背景色<div class="optional-mark"></div></li>
          <li class="form-col-item">
            <div class="form-col-item-group">
              <input type="text" name="bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>" maxlength="7">
              <input type="color" class="select-bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>">
            </div>
          </li>
          <li class="form-col-note">文字色コードを入力。右側アイコンで色選択できます。</li>
        </ul>
        <?php if (NAMELIST_GENDER || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_GENDER_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <?php foreach (NAMELIST_GENDER_LIST as $key => $value) { ?>
                <label><input type="radio" name="gender" <?php echo checkedRadio($inputParams['gender'], $value); ?> value="<?php echo h($value); ?>" ><?php echo h($key); ?></label>
              <?php } ?>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_SPECIES || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_SPECIES_NAME); ?><div class="optional-mark"></div></li>
            <div class="select-wrap">
              <li class="form-col-item">
                <select name="species">
                  <?php foreach (NAMELIST_SPECIES_LIST as $key => $value) { ?>
                    <option <?php echo selectedOption($inputParams['species'], $value); ?> value="<?php echo h($value); ?>"><?php echo h($key); ?></option>
                  <?php } ?>
                </select>
              </li>
            </div>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_TEAM || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_TEAM_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
                <div class="select-wrap">
                <select name="team">
                  <?php foreach (NAMELIST_TEAM_LIST as $key => $value) { ?>
                    <option <?php echo selectedOption($inputParams['team'], $value); ?> value="<?php echo h($value); ?>"><?php echo h($key); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_JOB || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_JOB_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="job" value="<?php echo h($inputParams['job']); ?>" maxlength="10"></li>
            <li class="form-col-note">最大 10 文字</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE1 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE1_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <?php foreach (NAMELIST_FREE1_LIST as $key => $value) { ?>
                <label><input type="radio" name="free1" <?php echo checkedRadio($inputParams['free1'], $value); ?> value="<?php echo h($value); ?>" ><?php echo h($key); ?></label>
              <?php } ?>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE2 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE2_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <?php foreach (NAMELIST_FREE2_LIST as $key => $value) { ?>
                <label><input type="radio" name="free2" <?php echo checkedRadio($inputParams['free2'], $value); ?> value="<?php echo h($value); ?>" ><?php echo h($key); ?></label>
              <?php } ?>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE3 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE3_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <?php foreach (NAMELIST_FREE3_LIST as $key => $value) { ?>
                <label><input type="radio" name="free3" <?php echo checkedRadio($inputParams['free3'], $value); ?> value="<?php echo h($value); ?>" ><?php echo h($key); ?></label>
              <?php } ?>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE4 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE4_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="free4">
                  <?php foreach (NAMELIST_FREE4_LIST as $key => $value) { ?>
                    <option <?php echo selectedOption($inputParams['free4'], $value); ?> value="<?php echo h($value); ?>"><?php echo h($key); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE5 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE5_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="free5">
                  <?php foreach (NAMELIST_FREE5_LIST as $key => $value) { ?>
                    <option <?php echo selectedOption($inputParams['free5'], $value); ?> value="<?php echo h($value); ?>"><?php echo h($key); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE6 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE6_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="free6">
                  <?php foreach (NAMELIST_FREE6_LIST as $key => $value) { ?>
                    <option <?php echo selectedOption($inputParams['free6'], $value); ?> value="<?php echo h($value); ?>"><?php echo h($key); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE7 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE7_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="free7" value="<?php echo h($inputParams['free7']); ?>" maxlength="20"></li>
            <li class="form-col-note">最大 20 文字</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE8 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE8_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="free8" value="<?php echo h($inputParams['free8']); ?>" maxlength="20"></li>
            <li class="form-col-note">最大 20 文字</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE9 || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_FREE9_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="free9" value="<?php echo h($inputParams['free9']); ?>" maxlength="20"></li>
            <li class="form-col-note">最大 20 文字</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_COMMENT || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_COMMENT_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="comment" value="<?php echo h($inputParams['comment']); ?>" maxlength="100"></li>
            <li class="form-col-note">最大 100 文字。サイトトップに表示されます。</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_URL || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_URL_NAME); ?><div class="optional-mark"></div></li>
            <li class="form-col-item"><input type="text" name="url" value="<?php echo h($inputParams['url']); ?>" maxlength="1000"></li>
            <li class="form-col-note">最大 1000 文字</li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_DETAIL || isAdmin()) { ?>
          <ul class="form-row">
            <li class="form-col-title"><?php echo h(NAMELIST_DETAIL_NAME); ?><div class="optional-mark"></div><div class="htmltag-mark"></div></li>
            <li class="form-col-item"><textarea name="detail" maxlength="10000"><?php echo h($inputParams['detail']); ?></textarea></li>
            <li class="form-col-note">最大 10000 文字。<a href="../../manual/src/htmltag.php" target="_blank">使用可能なHTMLタグについてはこちら。</a></li>
          </ul>
        <?php } ?>
        <div class="form-button-wrap">
          <button type="submit">更新</button>
        </div>
      </form>
    </div>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
  </div>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  // 移動ボタン
  jQuery('button.tolist-button').on('click', function(){
    window.location.href = "<?php echo h(getPrev()); ?>";
  });
});
</script>
</body>
</html>
