<?php
require_once('../../../../core/src/config.php');
require_once('../../../../core/src/functions.php');
require_once('../../../../core/src/session.php');
require_once('../../../../core/src/database.php');
require_once('../../../../core/src/administrator.php');

require_once('./config.php');
require_once('./functions.php');

$inputParams = array();

$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['color'] = inputParam('color', 7) ? inputParam('color', 7) : '#000000';
$inputParams['bgcolor'] = inputParam('bgcolor', 7) ? inputParam('bgcolor', 7) : '#ffffff';
$inputParams['memo'] = inputParam('memo', 200);
$inputParams['inoutmesflg'] = inputParam('inoutmesflg', 1);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken();

// 既に入室している場合はセッションから取得
if (isNowRoomEntry(getPageRoomdir())) {
  $inputParams = getChatEntry();
}

// DB接続
$dbhChatrooms  = connectRo(CHAT_ROOMS_DB);
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatentries = connectRw(CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(CHAT_LOGS_DB);
$dbhChatsecrets = connectRo(CHAT_SECRETS_DB);
$dbhInouthistory = connectRw(ROOM_INOUT_HISTORIES_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

$characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
if (!usedArr($characters)) {
  // 不正アクセス
  echo '名簿が存在しません。';
  exit;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

// 秘匿ルームの場合
if ($chatroom['issecret'] == 1) {
  $chatsecrets = selectChatsecrets($dbhChatsecrets);
  if (!usedArr($chatsecrets)) {
    firstAccessChatsecrets(CHAT_SECRETS_DB);
    $chatsecrets = selectChatsecrets($dbhChatsecrets);
  }
  $dbKeyword = $chatsecrets[0]['keyword'];
  $sessionKeyword = getSecretKeyword();
  if (!usedStr($dbKeyword) || !usedStr($sessionKeyword)) {
    echo '入室キーワードが設定されていません。';
    exit;
  }
  if ($dbKeyword != $sessionKeyword) {
    echo '入室キーワードが一致しません';
    exit;
  }
}

$chatentries = selectEqualChatentries($dbhChatentries);
// 最初の入室者かどうか
if (!usedArr($chatentries)) {
  // 最初の入室者の場合はエントリーキーを入れる
  $entrykey = sha1(uniqid(mt_rand(), true));
  insertChatentries($dbhChatentries, getUserid(), getUsername(), [
    'entrykey' => $entrykey,
    'characterid' => $character['id'],
    'fullname' => $character['fullname'],
    'color' => $inputParams['color'],
    'bgcolor' => $inputParams['bgcolor'],
  ]);
  $chatentries = selectEqualChatentries($dbhChatentries);

  // 入室ログ
  if ($inputParams['inoutmesflg'] == 1) {
    insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
      'entrykey' => $entrykey,
      'characterid' => $character['id'],
      'fullname' => CHAT_LOG_SYSTEM_NAME,
      'color' => $chatroom['color'],
      'bgcolor' => $chatroom['bgcolor'],
      'message' => '<span class="fullname">' .$character['fullname'] .'</span>' .'が入室しました。',
    ]);

    // 秘匿ルームでない場合のみ履歴に登録
    if ($chatroom['issecret'] != 1) {
      insertRoominouthistories($dbhInouthistory, [
        'roomtitle' => $chatroom['title'],
        'message' => '<span style="font-weight: bold;">' .$character['fullname'] .'</span>' .'が入室しました。',
      ]);
    }
  }
}
$chatentry = $chatentries[0];

// 自分は入室しているかどうか
$myChatentries = selectEqualChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (!usedArr($myChatentries)) {
  insertChatentries($dbhChatentries, getUserid(), getUsername(), [
    'entrykey' => $chatentry['entrykey'],
    'characterid' => $character['id'],
    'fullname' => $character['fullname'],
    'color' => $inputParams['color'],
    'bgcolor' => $inputParams['bgcolor'],
  ]);
  $myChatentries = selectEqualChatentries($dbhChatentries, [
    'characterid' => $character['id'],
  ]);

  // 入室していない場合は入室ログを出す
  if ($inputParams['inoutmesflg'] == 1) {
    insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
      'entrykey' => $chatentry['entrykey'],
      'characterid' => $character['id'],
      'fullname' => CHAT_LOG_SYSTEM_NAME,
      'color' => $chatroom['color'],
      'bgcolor' => $chatroom['bgcolor'],
      'message' => '<span class="fullname">' .$character['fullname'] .'</span>' .'が入室しました。'
    ]);

    // 秘匿ルームでない場合のみ履歴に登録
    if ($chatroom['issecret'] != 1) {
      insertRoominouthistories($dbhInouthistory, [
        'roomtitle' => $chatroom['title'],
        'message' => '<span style="font-weight: bold;">' .$character['fullname'] .'</span>' .'が入室しました。',
      ]);
    }
  }
}
$myChatentry = $myChatentries[0];

// 入室情報の保存
$save = [
  'roomdir' => getPageRoomdir(),
  'entrykey' => $chatentry['entrykey'],
  'characterid' => $character['id'],
  'color' => $inputParams['color'],
  'bgcolor' => $inputParams['bgcolor'],
  'memo' => $inputParams['memo'],
];
setChatEntry($save);


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
  <!-- 直接記載があるため下部に移動 -->
  <link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_ROOT); ?>/core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div id="id-roomchat-content-wrap" class="content-wrap">

  <header class="header">
    <nav class="header-menu">
      <ul class="header-item-group">
        <li class="header-item">
          <a href="./editlist.php" target="log">発言編集</a>
        </li>
        <li class="header-item">
          <span class="link new-window-submit">ログ別窓表示</span>
          <form name="new-window-form" class="hidden-form" action="./log.php" target="_blank" method="GET">
            <input type="hidden" name="lognum" value="">
            <input type="hidden" name="logsec" value="">
          </form>
        </li>
        <li class="header-item">
          <span class="link form-submit">退室メッセージを表示させずに退室</span>
          <form name="exit-form" class="hidden-form" action="./roomexit.php" method="POST">
            <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
            <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
            <input type="hidden" name="inoutmesflg" value="0">
          </form>
        </li>
        <li class="header-item">
          <span class="link form-submit">退室</span>
          <form name="exit-form" class="hidden-form" action="./roomexit.php" method="POST">
            <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
            <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
            <input type="hidden" name="inoutmesflg" value="1">
          </form>
        </li>
      </ul>
    </nav>
  </header>

  <div class="chat-form-wrap">
    <div class="form-wrap">
      <input type="hidden" id="backup-mes" value="">
      <form name="chat-form" id="chat-form" action="" target="log" method="POST">
        <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
        <input type="hidden" name="entrykey" value="<?php echo h($myChatentry['entrykey']); ?>">
        <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
        <ul class="form-row name-setting-wrap">
          <li class="form-col-title">名前</li>
          <li class="form-col-item">
            <?php echo h($character['fullname']); ?>
          </li>
          <li class="form-col-title">ささやき宛先</li>
          <li class="form-col-item">
            <div class="form-row-item-group">
              <div class="select-wrap">
                <select name="whisperid">
                  <option value="">なし</option>
                </select>
              </div>
            </div>
          </li>
        </ul>
        <ul class="form-row color-setting-wrap">
          <li class="form-col-title">文字色</li>
          <li class="form-col-item">
            <div class="form-row-item-group">
              <input type="text" name="color" value="<?php echo h($inputParams['color']); ?>" maxlength="7">
              <input type="color" class="select-color" value="<?php echo h($inputParams['color']); ?>">
            </div>
          </li>
          <li class="form-col-title">背景色</li>
          <li class="form-col-item">
            <div class="form-row-item-group">
              <input type="text" name="bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>" maxlength="7">
              <input type="color" class="select-bgcolor" value="<?php echo h($inputParams['bgcolor']); ?>">
            </div>
          </li>
        </ul>
        <ul class="form-row message-wrap">
          <li class="form-col-title">発言</li>
          <li class="form-col-item">
            <div class="form-col-item-group">
              <div class="htmltag-mark"></div>
              <textarea name="message" maxlength="3000" placeholder="Ctrl+Enterで発言可能"></textarea>
              <div class="form-col-note form-col-note-message">最大 3000 文字。<a href="../../../../manual/src/htmltag.php" target="_blank">使用可能なHTMLタグについてはこちら。</a></div>
            </div>
          </li>
        </ul>
        <ul class="form-row memo-wrap">
          <li class="form-col-title">備考</li>
          <li class="form-col-item">
            <input type="text" name="memo" value="<?php echo h($inputParams['memo']); ?>" maxlength="200">
            <div class="form-col-note">最大 200 文字</div>
          </li>
        </ul>
      </form>
    </div>
    <div class="form-wrap">
      <form name="reload-form" id="reload-form" action="./log.php" target="log" method="GET">
        <ul class="form-row log-setting-wrap">
          <li class="form-col-title">ログ行数</li>
          <li class="form-col-item">
            <div class="select-wrap">
              <select name="lognum">
                <option value="25">25行</option>
                <option value="50">50行</option>
                <option value="100">100行</option>
              </select>
            </div>
          </li>
          <li class="form-col-title">リロード時間</li>
          <li class="form-col-item">
            <div class="select-wrap">
              <select name="logsec">
                <option value="25">25秒</option>
                <option value="60">60秒</option>
                <option value="0">0秒（手動）</option>
              </select>
            </div>
          </li>
        </ul>
      </form>
      <div class="form-button-wrap chat-button-wrap">
        <button type="button" class="chat-button">発言</button>
        <button type="button" class="restore-button">発言復元</button>
        <button type="button" class="color-set-button">設定色変更</button>
        <button type="button" class="whisperid-set-button">ささやき宛先更新</button>
        <button type="button" class="reload-button">リロード</button>
        <button type="button" class="change-display-button">表示切替</button>
      </div>
      <div class="mes-wrap">
        <div id="result-mes"><!-- エラーメッセージ表示箇所 --></div>
      </div>
    </div>
  </div>

  <div class="random-wrap">
    <div class="random-border-wrap">
      <h3 class="dice-title">ダイス</h3>
      <div class="form-wrap dice-form-wrap">
        <form name="dice-form" class="dice-form" action="" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
          <div class="dice-wrap">
            <input type="text" name="dice" value="" maxlength="6" placeholder="1d6 など">
            <div class="form-col-note">最大 10d100 （100面ダイス10個）</div>
          </div>
          <div class="form-button-wrap dice-button-wrap">
            <button type="button" class="dice-button">ダイスを振る</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($chatroom['omi1flg'] || $chatroom['omi2flg'] || $chatroom['omi3flg']) { /* おみくじがひとつでも設定されていれば表示 */ ?>
      <div class="random-border-wrap">
        <h3 class="omi-title">おみくじ</h3>
          <form name="omi-form" id="omi-form" action="" method="POST">
            <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
            <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
            <input type="hidden" name="omikujiid" value="">
          </form>
        <?php if ($chatroom['omi1flg']) { ?>
          <div class="form-wrap omi1-form-wrap">
            <div class="form-button-wrap omi1-button-wrap">
              <button type="button" class="omi-button" value="<?php echo h(OMIKUJI1_ID);?>"><?php echo h($chatroom['omi1name']); ?></button>
            </div>
          </div>
        <?php } ?>
        <?php if ($chatroom['omi2flg']) { ?>
          <div class="form-wrap omi2-form-wrap">
            <div class="form-button-wrap omi2-button-wrap">
              <button type="button" class="omi-button" value="<?php echo h(OMIKUJI2_ID);?>"><?php echo h($chatroom['omi2name']); ?></button>
            </div>
          </div>
        <?php } ?>
        <?php if ($chatroom['omi3flg']) { ?>
          <div class="form-wrap omi3-form-wrap">
            <div class="form-button-wrap omi3-button-wrap">
              <button type="button" class="omi-button" value="<?php echo h(OMIKUJI3_ID);?>"><?php echo h($chatroom['omi3name']); ?></button>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>

    <?php if ($chatroom['deck1flg']) { /* 山札が設定されていれば表示 */ ?>
      <div class="random-border-wrap">
        <h3 class="deck-title">山札</h3>
        <form name="deck-form" id="deck-form" action="" method="POST">
          <input type="hidden" name="token" value="<?php echo h(getChatToken()); ?>">
          <input type="hidden" name="characterid" value="<?php echo h($myChatentry['characterid']); ?>">
        </form>
        <div class="form-button-wrap deck1-button-wrap">
          <button type="button" class="deck-button"><?php echo h($chatroom['deck1name']); ?></button>
        </div>
        <div class="form-button-wrap deck1-button-wrap">
          <button type="button" class="deck-reset-button">山札リセット</button>
        </div>
      </div>
    <?php } ?>

  </div>

  <div class="chatroom-frame-wrap">
    <iframe id="log-top" name="log" title="ルームログ"
      src="./log.php">
    </iframe>
  </div>

</div>
<script>
jQuery(function(){
  var resultElm = jQuery('div#result-mes');
  var backupElm = jQuery('input#backup-mes');
  var textMesElm = jQuery('textarea[name="message"]');
  var reloadBtElm = jQuery('button.reload-button');
  var chatBtElm = jQuery('button.chat-button');
  var whisperidElm = jQuery('select[name="whisperid"]');

  // 復元
  jQuery('button.restore-button').on('click', function(){
    var backup = textMesElm.val();

    if (backup) {
      resultElm.html('復元する場合は発言欄を空にしてください。');
      return;
    }

    var message = backupElm.val();
    textMesElm.val(message);
  });

  // 発言
  chatBtElm.on('click', function(){
    var sendData = jQuery('form#chat-form').serialize();
    var message = textMesElm.val();

    if (!message) {
      resultElm.html('発言する場合は発言欄に文字を入力してください。');
      return;
    }
    message = message.trim();
    if (message.length <= 0) {
      resultElm.html('発言する場合は発言欄に文字を入力してください。');
      return;
    }

    backupElm.val(message);
    textMesElm.val('');

    jQuery.ajax({
      url: './chat.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
        reloadBtElm.trigger('click');
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // Ctrl + Enter 発言
  textMesElm.on('keydown', function(event){
    if(event.ctrlKey === true && event.which === 13){
      chatBtElm.trigger('click');
    }
  });

  // 表示切替
  jQuery('button.change-display-button').on('click', function(){
    if (jQuery('div.random-wrap').css('display') == 'block') {
      // 隠す
      jQuery('div.content-wrap').css('grid-template-columns', '1fr 0em');
      jQuery('textarea[name="message"]').css('width', '85vw');
      jQuery('#id-roomchat-content-wrap').css('grid-template-rows', '2em 16em 1fr');
    } else {
      // 表示
      jQuery('div.content-wrap').css('grid-template-columns', '1fr 18em');
      jQuery('textarea[name="message"]').css('width', '30em');
      jQuery('#id-roomchat-content-wrap').css('grid-template-rows', '2em 28em 1fr');
    }

    jQuery('button.color-set-button').toggle();
    jQuery('ul.color-setting-wrap').toggle();
    jQuery('ul.memo-wrap').toggle();
    jQuery('ul.log-setting-wrap').toggle();
    jQuery('div.random-wrap').toggle();
  });

  // リロード
  reloadBtElm.on('click', function(){
    var reloadForm = jQuery('form#reload-form');
    reloadForm.submit();
  });

  // ささやきリスト更新
  jQuery('button.whisperid-set-button').on('click', function(){
    var sendData = jQuery('form#chat-form').serialize();
    jQuery.ajax({
      url: './whisperlist.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      var whisperlist = data['whisperlist'];
      if (code === 0) {
        resultElm.html('');
        whisperidElm.html(whisperlist);
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
      whisperidElm.trigger('change');
    });
  });

  whisperidElm.on('change', function(){
    var selectCharacterid = whisperidElm.val();
    if (selectCharacterid) {
      chatBtElm.text('ささやく');
    } else {
      chatBtElm.text('発言');
    }
  });

  // 設定色変更
  jQuery('button.color-set-button').on('click', function(){
    var sendData = jQuery('form#chat-form').serialize();
    jQuery.ajax({
      url: './setcolor.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
        reloadBtElm.trigger('click');
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // ダイス
  jQuery('button.dice-button').on('click', function(){
    var sendData = jQuery('form[name="dice-form"]').serialize();
    jQuery.ajax({
      url: './dice.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
        reloadBtElm.trigger('click');
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // おみくじ
  jQuery('button.omi-button').on('click', function(){
    var id = jQuery(this).val();
    var omiForm = jQuery('form[name="omi-form"]');
    omiForm.find('input[name="omikujiid"]').val(id);

    var sendData = omiForm.serialize();
    jQuery.ajax({
      url: './omikuji.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
        reloadBtElm.trigger('click');
      } else {
        resultElm.html(errMes);
      }
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // 山札
  jQuery('button.deck-button').on('click', function(){
    var id = jQuery(this).val();
    var omiForm = jQuery('form[name="deck-form"]');

    var sendData = omiForm.serialize();
    jQuery.ajax({
      url: './deck.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
      } else {
        resultElm.html(errMes);
      }
      reloadBtElm.trigger('click');
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // 山札リセット
  jQuery('button.deck-reset-button').on('click', function(){
    var id = jQuery(this).val();
    var omiForm = jQuery('form[name="deck-form"]');

    var sendData = omiForm.serialize();
    jQuery.ajax({
      url: './deckreset.php',
      type: 'POST',
      data: sendData,
      dataType: 'json',
    }).done((data, textStatus, jqXHR) => {
      var code = data['code'];
      var errMes = data['errorMessage'];
      if (code === 0) {
        resultElm.html('');
      } else {
        resultElm.html(errMes);
      }
      reloadBtElm.trigger('click');
    }).fail((jqXHR, textStatus, errorThrown) => {
      console.log(jqXHR);
      resultElm.html(errorThrown);
    }).always((data) => {
    });
  });

  // 別窓リンク
  jQuery('span.new-window-submit').on('click', function(){
    var lognum = jQuery('select[name="lognum"]').val();
    var logsec = jQuery('select[name="logsec"]').val();

    var nwForm = jQuery('form[name="new-window-form"]');
    nwForm.find('input[name="lognum"]').val(lognum);
    nwForm.find('input[name="logsec"]').val(logsec);
    nwForm.submit();
  });

});
</script>
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
</style>
<style>
/* レイアウト */
div.content-wrap {
  display: grid;
  grid-template-columns: 1fr 18em;
  grid-template-rows: 2em 28em 1fr;
}
header.header {
  grid-column: 1 / 3;
  grid-row: 1 / 2;
}
div.chat-form-wrap {
  grid-column: 1 / 2;
  grid-row: 2 / 3;
  overflow: auto;
}
div.random-wrap {
  grid-column: 2 / 3;
  grid-row: 2 / 3;
  overflow: auto;
}
div.chatroom-frame-wrap {
  grid-column: 1 / 3;
  grid-row: 3 / 4;
}
</style>
<style>
/* 入力 */
input[name="color"],
input[name="bgcolor"] {
  width: 8em;
}
input[name="memo"] {
  width: 30em;
}
input[name="dice"] {
  width: 7em;
}
select[name="whisperid"],
select[name="lognum"],
select[name="logsec"] {
  width: 8em;
}
textarea[name="message"] {
  resize: auto;
  width: 30em;
  height: 4em;
}
/* 通信メッセージ */
div.mes-wrap {
  display: flex;
  justify-content: center;
}
/* ボタン */
div.chat-button-wrap {
  display: flex;
  justify-content: center;
}
div.chat-button-wrap>button {
  margin: 0.5em;
}
/* チャットフォーム */
div.chat-form-wrap {
  margin-bottom: 0;
  padding: 1em;

  display: flex;
  flex-direction: column;
}
ul.form-row {
  border-bottom: dotted 1px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}
li.form-col-title:not(:first-child) {
  margin-left: 2em;
}
li.form-col-title {
  width: 7em;
  min-width: 7em;
  margin: 0.5em 0;
}
li.form-col-item {
  margin: 0.5em 0;
  width: 10em;
}
li.form-col-item-name {
  width: 25em;
}
div.form-row-item-group {
  display: flex;
  align-content: center;
  width: 13em;
}
div.form-col-item-group {
  display: flex;
  flex-direction: column;
}
div.form-col-note {
  font-size: 0.8em;
  opacity: 0.6;
  width: 20em;
}
div.form-col-note-message {
  width: 30em;
}
/* ダイス おみくじ 山札 */
div.random-border-wrap {
  margin: 1em 0 0 0;

  display: flex;
  flex-direction: column;
  align-items: center;
}
form.dice-form {
  margin:  0 0 0 1.2em;
}
h3.deck-title,
h3.omi-title,
h3.dice-title {
  font-size: 1.2em;
  font-weight: bold;
  border-bottom: dotted 1px;
  width: 12em;
  margin-bottom: 0.2em;
}
button.deck-reset-button,
button.deck-button,
button.omi-button,
input[name="dice"] {
  width: 14em;
}
</style>
<?php if (usedStr($chatroom['roomcss'])) { ?>
  <style>
    /* DB登録のCSS記載 */
    <?php echo h($chatroom['roomcss']) ?>
  </style>
<?php } ?>
<!-- レスポンシブ用 -->
<link rel="stylesheet" href="<?php echo h(SITE_ROOT); ?>/core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
</body>
</html>
