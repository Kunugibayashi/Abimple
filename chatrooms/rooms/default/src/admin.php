<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

adminOnly();

$success = '';
$errors = array();
$inputParams = array();

$inputParams['title'] = inputParam('title', 100);
$inputParams['guide'] = inputParam('guide', 2000);
$inputParams['toptemplate'] = inputParam('toptemplate', 20);
$inputParams['logtemplate'] = inputParam('logtemplate', 20);
$inputParams['isfree'] = inputParam('isfree', 1);
$inputParams['issecret'] = inputParam('issecret', 1);
$inputParams['color'] = inputParam('color', 7);
$inputParams['bgcolor'] = inputParam('bgcolor', 7);
$inputParams['bgimage'] = inputParam('bgimage', 1000);
$inputParams['omi1flg'] = inputParam('omi1flg', 1);
$inputParams['omi1name'] = inputParam('omi1name', 10);
$inputParams['omi1text'] = inputParam('omi1text', 10000);
$inputParams['omi2flg'] = inputParam('omi2flg', 1);
$inputParams['omi2name'] = inputParam('omi2name', 10);
$inputParams['omi2text'] = inputParam('omi2text', 10000);
$inputParams['omi3flg'] = inputParam('omi3flg', 1);
$inputParams['omi3name'] = inputParam('omi3name', 10);
$inputParams['omi3text'] = inputParam('omi3text', 10000);
$inputParams['deck1flg'] = inputParam('deck1flg', 1);
$inputParams['deck1name'] = inputParam('deck1name', 10);
$inputParams['deck1text'] = inputParam('deck1text', 10000);
$inputParams['roomcss'] = inputParam('roomcss', 10000);
$inputParams['created'] = inputParam('created', 20);
$inputParams['modified'] = inputParam('modified', 20);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOMS_DB);

  $chatrooms = selectChatroomsConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOMS_DB);
    $chatrooms = selectChatroomsConfig($dbhChatrooms);
  }
  $inputParams = $chatrooms[0];

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 入力値チェック
if (!usedStr($inputParams['title'])) {
  $errors[] = 'ルームタイトルを入力してください。';
}
if (!usedStr($inputParams['guide'])) {
  $errors[] = 'ルーム説明を入力してください。';
}
if (usedArr($errors)) {
  goto outputPage;
}

// おみくじ1の編集
if (usedStr($inputParams['omi1text'])) {
  $omiTmpArray = explode(',', $inputParams['omi1text']);
  foreach ($omiTmpArray as $key => $value) {
    $omiTmpArray[$key] = trim($value);
  }
  $inputParams['omi1text'] = implode(",\n", $omiTmpArray);
}

// おみくじ2の編集
if (usedStr($inputParams['omi2text'])) {
  $omiTmpArray = explode(',', $inputParams['omi2text']);
  foreach ($omiTmpArray as $key => $value) {
    $omiTmpArray[$key] = trim($value);
  }
  $inputParams['omi2text'] = implode(",\n", $omiTmpArray);
}

// おみくじ3の編集
if (usedStr($inputParams['omi3text'])) {
  $omiTmpArray = explode(',', $inputParams['omi3text']);
  foreach ($omiTmpArray as $key => $value) {
    $omiTmpArray[$key] = trim($value);
  }
  $inputParams['omi3text'] = implode(",\n", $omiTmpArray);
}

// 山札の編集
if (usedStr($inputParams['deck1text'])) {
  $deckTmpArray = explode(',', $inputParams['deck1text']);
  foreach ($deckTmpArray as $key => $value) {
    $value = trim($value);

    $markArray = explode('#', $value);
    if (count($markArray) >= 2) {
      $deckTmpArray[$key] = $markArray[0].'#'.$markArray[1];
    } else {
      $deckTmpArray[$key] = '0#'.$markArray[0];
    }
  }
  $inputParams['deck1text'] = implode(",\n", $deckTmpArray);
}

// DB接続
$dbhChatrooms = connectRw(CHAT_ROOMS_DB);

// チャットルーム更新
$updateRoom = $inputParams;
$result = updateChatroomsConfig($dbhChatrooms, $updateRoom);
if (!$result) {
  $errors[] = '更新に失敗しました。もう一度お試しください。';
  goto outputPage;
}

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$inputParams = $chatrooms[0];

$success = '更新が完了しました。';

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
  <title>チャットルーム管理画面</title>
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
  <h3 class="chatroom-title">チャットルーム管理画面</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        部屋の文字色や背景色は管理画面には反映されません。<br>
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

  <div class="form-wrap">
    <form name="characters-form" class="characters-form" action="./admin.php" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <ul class="form-row">
        <li class="form-col-title">ルームDIR</li>
        <li class="form-col-item"><?php echo h(getPageRoomdir()); ?></li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">ルームタイトル<div class="mandatory-mark"></div></li>
        <li class="form-col-item"><input type="text" name="title" value="<?php echo h($inputParams['title']); ?>" maxlength="100"></li>
        <li class="form-col-note">最大 100 文字</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">ルーム説明<div class="mandatory-mark"></div></li>
        <li class="form-col-item"><textarea name="guide" maxlength="2000"><?php echo h($inputParams['guide']); ?></textarea></li>
        <li class="form-col-note">最大 2000 文字</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">トップテンプレート<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="toptemplate">
              <option <?php echo selectedOption($inputParams['toptemplate'], CHAT_TOP_DEFAULT); ?> value="<?php echo h(CHAT_TOP_DEFAULT); ?>">デフォルト</option>
              <option <?php echo selectedOption($inputParams['toptemplate'], CHAT_TOP_TEMPLATE1); ?> value="<?php echo h(CHAT_TOP_TEMPLATE1); ?>">シンプル四角</option>
              <option <?php echo selectedOption($inputParams['toptemplate'], CHAT_TOP_TEMPLATE2); ?> value="<?php echo h(CHAT_TOP_TEMPLATE2); ?>">シンプル背景あり</option>
              <option <?php echo selectedOption($inputParams['toptemplate'], CHAT_TOP_TEMPLATE3); ?> value="<?php echo h(CHAT_TOP_TEMPLATE3); ?>">シンプル四角背景あり</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">ログテンプレート<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="logtemplate">
              <option <?php echo selectedOption($inputParams['logtemplate'], CHAT_LOG_DEFAULT); ?> value="<?php echo h(CHAT_LOG_DEFAULT); ?>">デフォルト</option>
              <option <?php echo selectedOption($inputParams['logtemplate'], CHAT_LOG_TEMPLATE1); ?> value="<?php echo h(CHAT_LOG_TEMPLATE1); ?>">背景色なし情報量重視</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">自由設定ルームにするか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="isfree">
              <option <?php echo selectedOption($inputParams['isfree'], '0'); ?> value="0">自由設定ルームにしない</option>
              <option <?php echo selectedOption($inputParams['isfree'], '1'); ?> value="1">自由設定ルームにする</option>
            </select>
          </div>
        </li>
        <li class="form-col-note">自由設定にした場合、ユーザーがタイトルと説明を変更できます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">秘匿ルームにするか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="issecret">
              <option <?php echo selectedOption($inputParams['issecret'], '0'); ?> value="0">秘匿ルームにしない</option>
              <option <?php echo selectedOption($inputParams['issecret'], '1'); ?> value="1">秘匿ルームにする</option>
            </select>
          </div>
        </li>
        <li class="form-col-note">秘匿設定にした場合、入室時にキーワードを設定し、キーワードを入力したユーザーのみが入室可能になります。</li>
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
      <ul class="form-row">
        <li class="form-col-title">ルームイメージ画像URL<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="text" name="bgimage" value="<?php echo h($inputParams['bgimage']); ?>" maxlength="1000"></li>
        <li class="form-col-note">最大 1000 文字。絶対パス指定を推奨。 例）/Abimple/assets/img/sample.jpg</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ1を表示するか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="omi1flg">
              <option <?php echo selectedOption($inputParams['omi1flg'], '0'); ?> value="0">表示しない</option>
              <option <?php echo selectedOption($inputParams['omi1flg'], '1'); ?> value="1">表示する</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ1の名前<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="text" name="omi1name" value="<?php echo h($inputParams['omi1name']); ?>" maxlength="10"></li>
        <li class="form-col-note">最大 10 文字。ボタン名に使用されます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ1<div class="optional-mark"></div></li>
        <li class="form-col-item">
          <textarea name="omi1text" maxlength="10000"><?php echo h($inputParams['omi1text']); ?></textarea>
          <li class="form-col-note">最大 10000 文字。カンマ(,)で区切って記載してください。</li>
          <div class="form-col-item-group">
            <button type="button" class="preview-button" value="omi1text">プレビュー</button>
            <div class="preview">ここにプレビュー結果が表示されます。</div>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ2を表示するか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="omi2flg">
              <option <?php echo selectedOption($inputParams['omi2flg'], '0'); ?> value="0">表示しない</option>
              <option <?php echo selectedOption($inputParams['omi2flg'], '1'); ?> value="1">表示する</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ2の名前<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="text" name="omi2name" value="<?php echo h($inputParams['omi2name']); ?>" maxlength="10"></li>
        <li class="form-col-note">最大 10 文字。ボタン名に使用されます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ2<div class="optional-mark"></div></li>
        <li class="form-col-item">
          <textarea name="omi2text" maxlength="10000"><?php echo h($inputParams['omi2text']); ?></textarea>
          <li class="form-col-note">最大 10000 文字。カンマ(,)で区切って記載してください。</li>
          <div class="form-col-item-group">
            <button type="button" class="preview-button" value="omi2text">プレビュー</button>
            <div class="preview">ここにプレビュー結果が表示されます。</div>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ3を表示するか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="omi3flg">
              <option <?php echo selectedOption($inputParams['omi3flg'], '0'); ?> value="0">表示しない</option>
              <option <?php echo selectedOption($inputParams['omi3flg'], '1'); ?> value="1">表示する</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ3の名前<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="text" name="omi3name" value="<?php echo h($inputParams['omi3name']); ?>" maxlength="10"></li>
        <li class="form-col-note">最大 10 文字。ボタン名に使用されます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">おみくじ3<div class="optional-mark"></div></li>
        <li class="form-col-item">
          <textarea name="omi3text" maxlength="10000"><?php echo h($inputParams['omi3text']); ?></textarea>
          <li class="form-col-note">最大 10000 文字。カンマ(,)で区切って記載してください。</li>
          <div class="form-col-item-group">
            <button type="button" class="preview-button" value="omi3text">プレビュー</button>
            <div class="preview">ここにプレビュー結果が表示されます。</div>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">山札を表示するか<div class="mandatory-mark"></div></li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="deck1flg">
              <option <?php echo selectedOption($inputParams['deck1flg'], '0'); ?> value="0">表示しない</option>
              <option <?php echo selectedOption($inputParams['deck1flg'], '1'); ?> value="1">表示する</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">山札の名前<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="text" name="deck1name" value="<?php echo h($inputParams['deck1name']); ?>" maxlength="10"></li>
        <li class="form-col-note">最大 10 文字。ボタン名に使用されます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">山札<div class="optional-mark"></div></li>
        <li class="form-col-item">
          <textarea name="deck1text" maxlength="10000"><?php echo h($inputParams['deck1text']); ?></textarea>
          <li class="form-col-note">最大 10000 文字。カンマ(,)で区切って記載してください。本文に # は使用できません。頭に 0# とつけると未開示、1# とつけると開示済みになります。つけない場合は 0# がつきます。</li>
          <div class="form-col-item-group">
            <button type="button" class="preview-deck-button" value="deck1text">プレビュー</button>
            <div class="preview">ここにプレビュー結果が表示されます。</div>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">ルームCSS追加<div class="optional-mark"></div></li>
        <li class="form-col-item"><textarea name="roomcss" maxlength="10000"><?php echo h($inputParams['roomcss']); ?></textarea></li>
        <li class="form-col-note">最大 10000 文字。チャットルームすべてのページの最下部 <style></style> 内に記載されます。</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">作成日</li>
        <li class="form-col-item"><?php echo h($inputParams['created']); ?></li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">更新日</li>
        <li class="form-col-item"><?php echo h($inputParams['modified']); ?></li>
      </ul>
      <div class="form-button-wrap">
        <button type="submit">更新</button>
      </div>
    </form>
  </div>

  <div class="page-back-wrap">
    <button type="button" class="tochatroom-button">チャットルームに移動する</button>
  </div>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.tochatroom-button').on('click', function(){
    window.location.href = './roomtop.php';
  });
  // プレビュー機能
  jQuery('button.preview-button').on('click', function(){
    var preview = jQuery(this).parent().find('.preview');
    var omiTextName = jQuery(this).val();
    var omiText = jQuery('textarea[name="' + omiTextName + '"]').val();
    var textArray = omiText.replace(/\r?\n/g, '').split(',');

    var result = [];
    var num = 1;
    for(const value of textArray){
      result.push('[' + num + ']：' + value.trim());
      num = num + 1;
    }
    preview.html(result.join('<br>'));
  });
  jQuery('button.preview-deck-button').on('click', function(){
    var preview = jQuery(this).parent().find('.preview');
    var deckTextName = jQuery(this).val();
    var deckText = jQuery('textarea[name="' + deckTextName + '"]').val();
    var textArray = deckText.replace(/\r?\n/g, '').split(',');

    var result = [];
    for(const value of textArray){
      valueArray = value.split('#');
      if(valueArray.length >= 2){
        openNum = valueArray[0];
        deckDetail = valueArray[1];
      } else {
        openNum = '0';
        deckDetail = valueArray[0];
      }
      if(openNum === '0'){
        openText = '未';
      } else {
        openText = '済';
      }
      result.push('[' + openText + ']：' + deckDetail.trim());
    }
    preview.html(result.join('<br>'));
  });

});
</script>
<style>
input[name="color"],
input[name="bgcolor"] {
  width: 8em;
}
</style>
</body>
</html>
