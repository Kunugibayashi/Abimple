<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhAllLogLists = connectRo(ALL_LOG_LISTS_DB);
  $logLists = selectEqualAllloglistsList($dbhAllLogLists);

  $pages = splitPages($logLists, getNowPage());

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// DB接続
$dbhAllLogLists = connectRo(ALL_LOG_LISTS_DB);
$logLists = selectEqualAllloglistsList($dbhAllLogLists);

$pages = splitPages($logLists, getNowPage());


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
  <title>ログ倉庫</title>
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
  <h3 class="frame-title">ログ倉庫</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">ログの「削除」のみが可能</span>です。<br>
        出力されたログファイルの内容を修正したい場合は chatrooms/rooms/《roomdir》/logs 配下のファイルを編集してください。<br>
      </p>
    </div>
  <?php } ?>

  <?php if (!usedArr($pages)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

  <div class="paging-wrap">
    <?php outputPaging($logLists, getNowPage()); ?>
  </div>
  <div class="sumpaging-wrap">
    <?php outputSumPaging($logLists, getNowPage()); ?>
  </div>
  <?php if (usedArr($pages)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap logstorage-table-wrap">
      <table>
        <tr>
          <?php if (isAdmin()) { ?>
            <th class="cell-action">操作</th>
            <th class="cell-id">id</th>
          <?php } ?>
          <th class="cell-created">作成日</th>
          <th class="cell-roomtitle">ルーム</th>
          <th class="cell-logentries">参加者</th>
          <th class="cell-logfilename">ログファイル名</th>
          <th class="cell-action1button">DL</th>
        </tr>
        <?php foreach ($pages as $key => $value) { ?>
          <tr>
            <?php if (isAdmin()) { ?>
              <td>
                <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
              </td>
              <td><?php echo h($value['id']); ?></td>
            <?php } ?>
            <td><?php echo h($value['created']); ?></td>
            <td><?php echo h($value['roomtitle']); ?></td>
            <td class="scroll"><?php echo ht($value['entries']); ?></td>
            <?php
              $filePath = (INDEX_ROOT. '/chatrooms/rooms/' .$value['roomdir'] .'/logs/' .$value['filename']);
              // リンクURLをチェック用ファイルパスと同じにするとローカルで正しく取得できないため、絶対パスで指定
              $filePathLink = (SITE_ROOT. '/chatrooms/rooms/' .$value['roomdir'] .'/logs/' .$value['filename']);
              if (file_exists($filePath)) {
            ?>
              <td><a href="<?php echo h($filePathLink); ?>"><?php echo h($value['filename']); ?></a></td>
              <td><a href="<?php echo h($filePathLink); ?>" download>DL</a></td>
            <?php } else { ?>
              <td><?php echo h($value['filename']); ?></td>
              <td>ファイル無し</td>
            <?php } ?>
          </tr>
        <?php } ?>
      </table>
    </div>
    <div class="paging-wrap">
      <?php outputPaging($logLists, getNowPage()); ?>
    </div>
  <?php } ?>

  <form id="delete-form" class="hidden-form" action="./delete.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.delete-button').on('click', function(){
    var userid = jQuery(this).val();
    var deleteForm = jQuery('form#delete-form');
    deleteForm.find('input[name="id"]').val(userid);
    deleteForm.submit();
  });
});
</script>
</body>
</html>
