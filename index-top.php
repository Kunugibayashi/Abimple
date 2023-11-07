<?php
require_once('./core/src/config.php');
require_once('./core/src/functions.php');
require_once('./core/src/session.php');
require_once('./core/src/database.php');
require_once('./core/src/administrator.php');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>ユーザー一覧</title>
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

<div class="top-news-contents">
  <h3>お知らせ</h3>
  <div id="id-infomation-contents" class="infomation-wrap"></div>
  <script>
    var infomationReload = function() {
      var resultContents = jQuery('#id-infomation-contents');
      jQuery.ajax({
        url: './infomation/src/news.php',
        dataType: 'HTML',
      }).done((data, textStatus, jqXHR) => {
        resultContents.html(data);
      }).fail((jqXHR, textStatus, errorThrown) => {
        console.log(jqXHR);
        resultContents.html(errorThrown);
      }).always((data) => {
      });
    }
    infomationReload();
  </script>
</div>

<div class="top-news-contents">
  <h3>名簿</h3>
  <div id="id-characters-contents" class="characters-wrap"></div>
  <script>
    var charactersReload = function() {
      var resultContents = jQuery('#id-characters-contents');
      jQuery.ajax({
        url: './characters/src/news.php',
        dataType: 'HTML',
      }).done((data, textStatus, jqXHR) => {
        resultContents.html(data);
      }).fail((jqXHR, textStatus, errorThrown) => {
        console.log(jqXHR);
        resultContents.html(errorThrown);
      }).always((data) => {
      });
    }
    charactersReload();
  </script>
</div>

<?php if (SITE_LETTER_OPEN === 1) { /* 私書が公開されている場合 */ ?>
  <div class="top-news-contents">
    <h3>公開私書</h3>
    <div id="id-letters-contents" class="letters-wrap"></div>
    <script>
      var lettersReload = function() {
        var resultContents = jQuery('#id-letters-contents');
        jQuery.ajax({
          url: './letters/src/publicnews.php',
          dataType: 'HTML',
        }).done((data, textStatus, jqXHR) => {
          resultContents.html(data);
        }).fail((jqXHR, textStatus, errorThrown) => {
          console.log(jqXHR);
          resultContents.html(errorThrown);
        }).always((data) => {
        });
      }
      lettersReload();
    </script>
  </div>
<?php } ?>

<?php if (isLogin()) { /* ログイン時 */ ?>
  <div class="top-news-contents">
    <h3>あなた宛の私書</h3>
    <div id="id-myletters-contents" class="myletters-wrap"></div>
    <script>
      var mylettersReload = function() {
        var resultContents = jQuery('#id-myletters-contents');
        jQuery.ajax({
          url: './letters/src/mynews.php',
          dataType: 'HTML',
        }).done((data, textStatus, jqXHR) => {
          resultContents.html(data);
        }).fail((jqXHR, textStatus, errorThrown) => {
          console.log(jqXHR);
          resultContents.html(errorThrown);
        }).always((data) => {
        });
      }
      mylettersReload();
    </script>
  </div>
<?php } ?>

<div class="top-news-contents">
  <h3>ルーム一覧</h3>
  <div id="id-chatroom-contents" class="chatroom-wrap"></div>
  <script>
    var roomReload = function() {
      var resultContents = jQuery('#id-chatroom-contents');
      jQuery.ajax({
        url: './chatrooms/src/news.php',
        dataType: 'HTML',
      }).done((data, textStatus, jqXHR) => {
        resultContents.html(data);
      }).fail((jqXHR, textStatus, errorThrown) => {
        console.log(jqXHR);
        resultContents.html(errorThrown);
      }).always((data) => {
      });
    }
    roomReload();
  </script>
</div>

</body>
</html>
