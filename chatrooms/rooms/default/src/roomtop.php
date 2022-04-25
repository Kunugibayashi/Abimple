<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$errors = array();
$inputParams = array();

$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['fullname'] = inputParam('fullname', 20);
$inputParams['isfree'] = inputParam('isfree', 1);
$inputParams['isframe'] = inputParam('isframe', 1);
$inputParams['memo'] = inputParam('memo', 200);
$inputParams['color'] = inputParam('color', 7) ? inputParam('color', 7) : '#000000';
$inputParams['bgcolor'] = inputParam('bgcolor', 7) ? inputParam('bgcolor', 7) : '#ffffff';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策はフォーム表示時にセット

  // DB接続
  $dbhChatrooms = connectRo(CHAT_ROOM_DB);
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $chatrooms = selectChatroomConfig($dbhChatrooms);
  if (!usedArr($chatrooms)) {
    firstAccessChatroom(CHAT_ROOM_DB);
    $chatrooms = selectChatroomConfig($dbhChatrooms);
  }
  $chatroom = $chatrooms[0];

  $characters = selectCharacterMyList($dbhCharacters, getUserid(), getUsername());

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
  <title><?php echo h($chatroom['title']); ?></title>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_ROOT); ?>/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">

  <header class="header">
    <nav class="header-menu">
      <ul class="header-item-group">
        <?php if ($chatroom['isfree']) { ?>
          <li class="header-item"><a href="./roomseting.php">ルーム設定変更</a></li>
        <?php } ?>
        <li class="header-item"><a href="./log.php?lognum=100&logsec=25" target="_blank">ログ別窓表示</a></li>
        <li class="header-item"><a href="./loglist.php">過去ログ一覧</a></li>
        <li class="header-item"><a href="./admin.php">管理画面</a></li>
      </ul>
    </nav>
  </header>

  <?php if (usedArr($errors)) { /* エラーメッセージ */ ?>
    <div class="mes-wrap">
      <ul class="err-mes-wrap">
        <?php foreach ($errors as $key => $value) { ?>
          <li class="err-mes">エラー：<?php echo h($value); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <div class="chatconfig-wrap">
    <div class="chatconfig-title-wrap">
      <h3 class="chatconfig-title"><?php echo h($chatroom['title']); ?></h3>
    </div>

    <div class="chatconfig-guide"><?php echo h($chatroom['guide']); ?></div>

    <div class="form-wrap roomchat-form-wrap">
      <?php if (!isNowRoomEntry(getPageRoomdir()) && isChatEntry()) { /* 他のルームに入室している場合はメッセージのみ */ ?>
        <div class="note-wrap">
          <p class="note">
            他のルーム（<?php echo h(getNowRoomEntry()); ?>）に入室しています。<br>
          </p>
        </div>
      <?php } else if (isChatEntry()) { /* 入室している場合は入室ボタンのみ */ ?>
        <div class="note-wrap">
          <p class="note">
            すでに入室しています。<br>
          </p>
        </div>
        <form name="roomchat-form" class="roomchat-form" action="./roomchat.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <div class="form-button-wrap submit-wrap">
            <button type="submit">再入室</button>
          </div>
        </form>
      <?php } else if (usedArr($characters)) { /* 入室していない & キャラクター登録をしている場合のみに入室を表示 */ ?>
        <?php setChatToken(); /* フォーム表示時にトークンをセット */ ?>
        <form name="roomchat-form" class="roomchat-form" action="./roomchat.php" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <ul class="form-row fullname-wrap">
            <li class="form-col-title">キャラクター</li>
            <li class="form-col-item">
              <div class="select-wrap">
                <select name="characterid">
                  <?php foreach ($characters as $character) { ?>
                    <option value="<?php echo h($character['id']); ?>"><?php echo h($character['fullname']); ?></option>
                  <?php } ?>
                </select>
              </div>
            </li>
          </ul>
          <ul class="form-row color-wrap">
            <li class="form-col-title">文字色</li>
            <li class="form-col-item">
              <div class="form-col-item-group">
                <input type="text" name="color" value="<?php echo h($inputParams['color']); ?>" maxlength="7">
                <input type="color" class="select-color" value="<?php echo h($inputParams['color']); ?>">
              </div>
            </li>
          </ul>
          <ul class="form-row bgcolor-wrap">
            <li class="form-col-title">背景色</li>
            <li class="form-col-item">
              <div class="form-col-item-group">
                <input type="text" name="bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>" maxlength="7">
                <input type="color" class="select-bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>">
              </div>
            </li>
          </ul>
          <ul class="form-row memo-wrap">
            <li class="form-col-title">備考</li>
            <li class="form-col-item"><input type="text" name="memo" value="<?php echo h($inputParams['memo']); ?>" maxlength="200"></li>
          </ul>
          <div class="form-button-wrap submit-wrap">
            <button type="submit">入室</button>
          </div>
        </form>
      <?php } else { /* キャラクター登録がない場合は案内を表示 */ ?>
        <div class="note-wrap">
          <p class="note">
            入室する場合はログインし、名簿登録をしてください。<br>
          </p>
        </div>
      <?php } ?>
    </div>

  </div>

  <div class="chatroom-frame-wrap">
    <?php if ($chatroom['isframe']) {  /* フレームあり */ ?>
      <iframe id="log-top" name="log" title="ルームログ"
        src="./log.php">
      </iframe>
    <?php } ?>
  </div>
  <?php if (!$chatroom['isframe']) { /* フレームなし */ ?>
    <script>
      // 自動画面更新
      var lognum = 100;
      var logsec = 25;
      var logReload = function() {
        var resultContents = jQuery('.chatroom-frame-wrap');
        // log側のデフォルトと異なる場合に、画面表示と整合性を取るため、GETパラメータに指定する
        jQuery.ajax({
          url: './log.php?lognum=' + lognum + '&logsec=' + logsec,
          dataType: 'HTML',
        }).done((data, textStatus, jqXHR) => {
          resultContents.html(data);
        }).fail((jqXHR, textStatus, errorThrown) => {
          console.log(jqXHR);
          resultContents.html(errorThrown);
        }).always((data) => {
        });
      }
      logReload();
      setInterval(logReload, logsec * 1000);
    </script>
  <?php } ?>

</div>
<?php if (usedArr($characters)) { /* キャラクター登録をしている場合のみに表示 */ ?>
  <script>
  jQuery(function(){
    var characterColors = {};
    <?php foreach ($characters as $key => $value) { ?>
      characterColors['<?php echo h($value['id']); ?>'] = {'color' : '<?php echo h($value['color']); ?>', 'bgcolor' : '<?php echo h($value['bgcolor']); ?>' };
    <?php } ?>

    // キャラクター選択によって文字色を変更
    jQuery('select[name="characterid"]').on('change', function(){
      var characterid = jQuery(this).val();

      jQuery('input[name="color"]').val(characterColors[characterid].color);
      jQuery('input.select-color').val(characterColors[characterid].color);

      jQuery('input[name="bgcolor"]').val(characterColors[characterid].bgcolor);
      jQuery('input.select-bgcolor').val(characterColors[characterid].bgcolor);
    }).trigger('change');

  });
  </script>
<?php } ?>
<style>
/* 共通 */
a {
  color: <?php echo h($chatroom['color']); ?>;
}
body {
  color: <?php echo h($chatroom['color']); ?>;
  background-color: <?php echo h($chatroom['bgcolor']); ?>;
}
div.content-wrap {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 99vh;
}
ul, li {
  list-style-type: none;
}
/* レイアウト */
div.content-wrap {
  display: grid;
  grid-template-rows: 2em 25em 1fr;
}
header.header {
  grid-row: 1 / 2;
}
div.chatconfig-wrap {
  grid-row: 2 / 3;
}
div.chatroom-frame-wrap {
  grid-row: 3 / 4;
}
/* ヘッダー */
header.header {
  display: flex;
  justify-content: flex-end;
  font-size: 0.8em;
  color: <?php echo h($chatroom['bgcolor']); ?>;
  background-color: <?php echo h($chatroom['color']); ?>;
}
ul.header-item-group {
  display: flex;
  margin: 0.5em;
}
li.header-item {
  padding: 0 1em;
  list-style-type: none;
}
li.header-item>a {
  color: <?php echo h($chatroom['bgcolor']); ?>;
}
/* インラインフレーム */
div.chatroom-frame-wrap {
  border-top: solid 4px;
}
/* チャット画面フォーム */
div.roomchat-form-wrap {
  overflow: auto;
}
div.form-wrap {
  margin: 1em 0;
}
ul.form-row {
  display: flex;
}
li.form-col-title {
  width: 8em;
  padding: 0.2em;
  margin-top: 0.6em;
}
li.form-col-item {
  margin-top: 0.5em;
}
div.form-button-wrap {
  margin-top: 2em;
  margin-right: 2em;
  display: flex;
  justify-content: flex-end;
}
div.form-col-item-group {
  display: flex;
  align-items: flex-start;
}
select[name="characterid"] {
  width: 20em;
}
input[name="color"],
input[name="bgcolor"] {
  width: 8em;
}
input[name="memo"] {
  width: 20em;
}

<?php if ($chatroom['toptemplate'] === CHAT_TOP_DEFAULT
       || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2
) { ?>
  div.chatconfig-wrap {
    margin: 3em 3em 1em 3em;
  }
  /* チャット画面レイアウト */
  div.chatconfig-wrap {
    display: grid;
    grid-template-rows: 4em 1fr; /* 横 */
    grid-template-columns: 1fr 30em; /* 縦 */
  }
  div.chatconfig-title-wrap {
    grid-column: 1 / 3; /* 横 */
    grid-row: 1 / 2; /* 縦 */
  }
  div.chatconfig-guide {
    grid-column: 1 / 2; /* 横 */
    grid-row: 2 / 3; /* 縦 */
  }
  div.form-wrap {
    grid-column: 2 / 3; /* 横 */
    grid-row: 2 / 3; /* 縦 */
  }
  /* チャットルームタイトル */
  h3.chatconfig-title {
    border-bottom: solid 2px;
    font-size: 3em;
    text-align: center;
  }
  /* チャットルーム説明 */
  div.chatconfig-guide {
    margin: 1em 0;
    height: 14em;
    overflow: auto;
  }
<?php } ?>
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE1
       || $chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3
) { ?>
  div.chatconfig-wrap {
    margin: 2em;
  }
  /* チャット画面レイアウト */
  div.chatconfig-wrap {
    display: grid;
    grid-template-columns: 24em 1fr 30em; /* 横 */
  }
  h3.chatconfig-title {
    grid-column: 1 / 2; /* 横 */
  }
  div.chatconfig-guide {
    grid-column: 2 / 3; /* 横 */
  }
  div.form-wrap {
    grid-column: 3 / 4; /* 横 */
  }
  /* チャットルームタイトル */
  div.chatconfig-title-wrap {
    border: double 14px;
    width: 20em;
    height: 20em;
    padding: 2em;
    color: <?php echo h($chatroom['bgcolor']); ?>;
    background-color: <?php echo h($chatroom['color']); ?>;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  h3.chatconfig-title {
    letter-spacing: 0.2em;
  }
  /* チャットルーム説明 */
  div.chatconfig-guide {
    margin: 1em 0;
    height: 14em;
    overflow: auto;
  }
<?php } ?>
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE2) { ?>
  body {
    background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    background-repeat: repeat;
  }
<?php } ?>
<?php if ($chatroom['toptemplate'] === CHAT_TOP_TEMPLATE3) { ?>
  div.chatconfig-title-wrap {
    background-image: url("<?php echo h($chatroom['bgimage']); ?>");
    background-repeat: repeat;
  }
<?php } ?>
</style>
</body>
</html>
