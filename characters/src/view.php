<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

$errors = array();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  $inputParams['characterid'] = getParam('id');
  if (!usedStr($inputParams['characterid'])) {
    $errors[] = 'キャラクターIDが不正です。';
    goto outputPage;
  }

  // DB接続
  $dbhCharacters = connectRw(CHARACTERS_DB);

  $characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
  if (!usedArr($characters)) {
    $errors[] = '名簿がありません。';
    goto outputPage;
  }
  if (count($characters) !== 1) {
    $errors[] = '名簿データに不備があります。';
    goto outputPage;
  }
  $character = $characters[0];

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// POSTは処理をしない。
exit;

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
  <title>名簿参照</title>
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
  <h3 class="frame-title">名簿参照</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
      </p>
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

  <?php if (usedArr($character) && usedStr($character['id'])) { /* データがある場合は表示 */ ?>
    <div class="view-wrap view-character-wrap">
      <div class="view-contents">
        <ul class="view-row">
          <li class="view-col-title"><?php echo h(NAMELIST_ID); ?></li>
          <li class="view-col-item"><?php echo h($character['id']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h(NAMELIST_NAME); ?></li>
          <li class="view-col-item"><?php echo h($character['fullname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h(NAMELIST_COLOR); ?></li>
          <li class="view-col-item"><span style="color: <?php echo h($character['color']); ?>; "><?php echo h($character['color']); ?></span></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h(NAMELIST_BGCOLOR); ?></li>
          <li class="view-col-item"><span style="color: <?php echo h($character['bgcolor']); ?>; "><?php echo h($character['bgcolor']); ?></span></li>
        </ul>
        <?php if (NAMELIST_GENDER || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_GENDER_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['gender']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_SPECIES || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_SPECIES_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['species']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_TEAM || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_TEAM_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['team']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_JOB || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_JOB_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['job']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE1 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE1_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free1']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE2 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE2_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free2']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE3 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE3_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free3']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE4 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE4_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free4']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE5 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE5_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free5']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE6 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE6_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free6']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE7 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE7_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free7']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE8 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE8_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free8']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE9 || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_FREE9_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['free9']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_COMMENT || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_COMMENT_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['comment']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_URL || isAdmin()) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h(NAMELIST_URL_NAME); ?></li>
            <li class="view-col-item"><?php echo h($character['url']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE10 || isAdmin()) { ?>
          <ul class="view-row view-free10-row">
            <li class="view-col-title view-free10-title"><?php echo h(NAMELIST_FREE10_NAME); ?></li>
            <li class="view-col-item view-free10-item"><?php echo ht($character['free10']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE11 || isAdmin()) { ?>
          <ul class="view-row view-free11-row">
            <li class="view-col-title view-free11-title"><?php echo h(NAMELIST_FREE11_NAME); ?></li>
            <li class="view-col-item view-free11-item"><?php echo ht($character['free11']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE12 || isAdmin()) { ?>
          <ul class="view-row view-free12-row">
            <li class="view-col-title view-free12-title"><?php echo h(NAMELIST_FREE12_NAME); ?></li>
            <li class="view-col-item view-free12-item"><?php echo ht($character['free12']); ?></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_DETAIL || isAdmin()) { ?>
          <ul class="view-row view-detail-row">
            <li class="view-col-title view-detail-title"><?php echo h(NAMELIST_DETAIL_NAME); ?></li>
            <li class="view-col-item view-detail-item"><?php echo ht($character['detail']); ?></li>
          </ul>
        <?php } ?>
      </div>
    <?php } ?>

    <div class="page-back-wrap">
      <button type="button" class="tolist-button">一覧に戻る</button>
    </div>

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
