<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$success = '';
$errors = array();
$inputParams = array();

// 入室前提のためセッションから値を取得
$sessionChatEntry = getChatEntry();

$inputParams['characterid'] = $sessionChatEntry['characterid'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);
  $dbhChatentries = connectRw(CHAT_ENTRIES_DB);
  $dbhChatlogs = connectRw(CHAT_LOGS_DB);

  if (isAdmin()) {
    // アドミンの場合は、秘匿を覗く全ての発言を取得

    // 最大10000行
    $chatlogs = selectEqualChatlogs($dbhChatlogs, 10000);
  } else {
    // ユーザーの場合は入室情報から取得

    $characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
    if (!usedArr($characters)) {
      // 不正アクセス
      $jsonArray['code'] = 1;
      $jsonArray['errorMessage'] = '名簿が存在しません。';
      goto outputPage;
    }
    $character = $characters[0];

    // 本人確認
    identityUser($character['userid'], $character['username']);

    $myChatentries = selectEqualChatentries($dbhChatentries, [
      'characterid' => $character['id'],
    ]);
    if (!usedArr($myChatentries)) {
      // 不正アクセス
      $jsonArray['code'] = 1;
      $jsonArray['errorMessage'] = '入室していません。';
      goto outputPage;
    }
    $myChatentry = $myChatentries[0];

    // 最大10000行
    $chatlogs = selectEqualChatlogsEntrykey($dbhChatlogs, 10000, $myChatentry['entrykey'], [
      'characterid' => $character['id'],
      'fullname' => $character['fullname'],
    ]);

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
  <title>発言編集</title>
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
  <h3 class="chatroom-title">発言編集</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        管理ユーザーは<span class="point">全てのユーザーの発言「編集」が可能</span>です。<br>
      </p>
    </div>
  <?php } ?>

  <div class="note-wrap">
    <p class="note">
      変更する発言を変更し、編集ボタンを押下してください。<br>
      出力されたログは編集できません。<br>
    </p>
  </div>

  <div class="mes-wrap">
    <div id="result-mes"><!-- エラーメッセージ表示箇所 --></div>
  </div>

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

  <?php if (usedArr($chatlogs)) { /* 発言がある場合に表示 */ ?>
    <div class="table-wrap logedit-table-wrap">
      <table>
        <tr>
          <th class="cell-action">操作</th>
          <th class="cell-id"><?php echo h(NAMELIST_ID); ?></th>
          <th class="cell-fullname"><?php echo h(NAMELIST_NAME); ?></th>
          <th class="cell-message">発言</th>
          <?php if (isAdmin()) { ?>
            <th class="cell-userid">userid</th>
            <th class="cell-username">username</th>
          <?php } ?>
        </tr>
        <?php foreach ($chatlogs as $key => $value) { ?>
          <tr>
            <td>
              <button type="button" class="edit-button" value="<?php echo h($value['id']); ?>">編集</button>
            </td>
            <td><?php echo h($value['id']); ?></td>
            <td><?php echo h($value['fullname']); ?></td>
            <td><textarea name="message" maxlength="3000" id="id_message_<?php echo h($value['id']); ?>"><?php echo h($value['message']); ?></textarea></td>
            <?php if (isAdmin()) { ?>
              <td><?php echo h($value['userid']); ?></td>
              <td><?php echo h($value['username']); ?></td>
            <?php } ?>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <form name="edit-form" class="hidden-form" action="./edit.php" method="POST">
    <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
    <input type="hidden" name="characterid" value="<?php echo h($character['id']); ?>">
    <input type="hidden" name="id" value="jQueryで入力">
    <input type="hidden" name="message" value="jQueryで入力">
  </form>

</div>
<script>
jQuery(function(){
  var resultElm = jQuery('div#result-mes');

  jQuery('button.edit-button').on('click', function(){
    var id = jQuery(this).val();
    var message = jQuery('textarea#id_message_' + id).val();

    var editForm = jQuery('form[name="edit-form"]');
    editForm.find('input[name="id"]').val(id);
    editForm.find('input[name="message"]').val(message);

    var sendData = editForm.serialize();
    jQuery.ajax({
      url: './edit.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html(id + 'の更新処理が完了しました。');
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });
});
</script>
</body>
</html>
