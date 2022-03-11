<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

/* 関数
 */
$sortByLastmod = function($a, $b) {
  return filemtime($b) - filemtime($a);
};

$success = '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  $files = glob(OUTPUT_LOG_DIR .'*.html');
  usort($files, $sortByLastmod);

  $logList = array();
  foreach ($files as $key => $value) {
    // ファイル名のみ抽出
    $filename = str_replace(OUTPUT_LOG_DIR, '', $value);

    // ログの中から参加者を取得
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTMLFile($value);
    libxml_clear_errors();
    $entries = $doc->saveHTML($doc->getElementById('chat-entries'));

    $logFile = array();
    $logFile['filepath'] = $value;
    $logFile['filename'] = $filename;
    $logFile['entries'] = $entries;

    $logList[] = $logFile;
  }

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
  <title>過去ログ一覧</title>
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
  <h3 class="chatroom-title">過去ログ一覧</h3>

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

  <div class="note-wrap">
    <p class="note">
      ダウンロードする場合は「表示」を右クリックし、「リンク先を別名で保存」してください。<br>
    </p>
  </div>

  <?php if (usedArr($logList)) { ?>
    <div class="table-wrap loglist-table-wrap">
      <table>
        <tr>
          <th class="cell-dl">URL</th>
          <th class="cell-filename">ファイル名</th>
          <th class="cell-entry">参加者</th>
        </tr>
        <?php foreach ($logList as $key => $value) { ?>
          <tr>
            <td><a href="<?php echo h($value['filepath']); ?>">表示</a></td>
            <td><?php echo h($value['filename']); ?></td>
            <td><?php echo $value['entries']; /* タグ出力を許可 */ ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <?php if (!usedArr($logList)) { ?>
    <div class="note-wrap">
      <p class="note">
        過去ログファイルはありません。<br>
      </p>
    </div>
  <?php } ?>

  <div class="page-back-wrap">
    <button type="button" class="tochatroom-button">チャットルームに移動する</button>
  </div>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.tochatroom-button').on('click', function(){
    window.location.href = './roomtop.php';
  });
});
</script>
</body>
</html>
