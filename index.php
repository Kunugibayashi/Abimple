<?php
require_once(__DIR__ .'/core/src/config.php');
require_once(__DIR__ .'/core/src/functions.php');
require_once(__DIR__ .'/core/src/session.php');
require_once(__DIR__ .'/core/src/database.php');
require_once(__DIR__ .'/core/src/administrator.php');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h(SITE_TITLE); ?></title>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/<?php echo h(SITE_TEMPLATE); ?>.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>assets/css/user-edit.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body class="index-body">
<div class="index-wrap">

  <header class="index-header">
    <h1 class="index-title"><a href="<?php echo h(SITE_LINK); ?>index-top.php" target="indexTop"><?php echo h(SITE_TITLE); ?></a></h1>
    <?php if (isLogin()) { /* ログイン時 */ ?>
      <div class="index-login">
        <?php echo h(getUserid()); ?>:<?php echo h(getUsername()); ?>でログイン中...
      </div>
    <?php } ?>
    <button type="button" class="menu-button">Menu</button>
  </header>

  <div class="index-menu">
    <nav class="menu menu-site">
      <h2 class="menu-title top-menu-title">World</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>world/src/world.php" target="indexTop">世界観</a></li>
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>world/src/rule.php" target="indexTop">利用規約</a></li>
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>world/src/qa.php" target="indexTop">Q&A</a></li>
      </ul>
    </nav>

    <nav class="menu menu-world">
      <h2 class="menu-title top-menu-title">Site</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>infomation/src/list.php" target="indexTop">お知らせ</a></li>
        <?php if (isLogin()) { /* ログイン時 */ ?>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>users/src/mylist.php" target="indexTop">ユーザー管理</a></li>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>users/src/logout.php" target="indexTop">ログアウト</a></li>
        <?php } ?>
        <?php if (!isLogin()) { /* ログアウト時 */ ?>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>users/src/signup.php" target="indexTop">ユーザー登録</a></li>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>users/src/login.php" target="indexTop">ログイン</a></li>
        <?php } ?>
      </ul>
    </nav>

    <nav class="menu menu-user">
      <h2 class="menu-title top-menu-title">NameList</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>characters/src/list.php" target="indexTop">名簿</a></li>
        <?php if (isLogin()) { /* ログイン時 */ ?>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>characters/src/signup.php" target="indexTop">名簿登録</a></li>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>characters/src/mylist.php" target="indexTop">名簿管理</a></li>
        <?php } ?>
        <?php if (!isLogin()) { /* ログアウト時 */ ?>
          <li class="menu-item menu-nolink">名簿登録</a></li>
          <li class="menu-item menu-nolink">名簿管理</a></li>
        <?php } ?>
      </ul>
    </nav>

    <nav class="menu menu-room">
      <h2 class="menu-title top-menu-title">Room</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>chatrooms/src/entrance.php" target="indexTop">ルーム一覧</a></li>
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>chatrooms/src/inouthistorylist.php" target="indexTop">入退室履歴</a></li>
        <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>chatloghtml/src/list.php" target="indexTop">ログ倉庫</a></li>
      </ul>
    </nav>

<?php if (SITE_LETTER_OPEN == 1 || SITE_LETTER_OPEN == 2) { /* 公開私書、または、個別私書の場合 */ ?>
    <nav class="menu menu-chara">
      <h2 class="menu-title top-menu-title">Letter</h2>
      <ul class="menu-item-group">
        <?php if (SITE_LETTER_OPEN == 1) { /* 公開私書の場合 */ ?>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>letters/src/publiclist.php" target="indexTop">公開私書箱</a></li>
        <?php } ?>
        <?php if (isLogin()) { /* ログイン時 */ ?>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>letters/src/inboxmylist.php" target="indexTop">受信箱</a></li>
          <li class="menu-item"><a href="<?php echo h(SITE_LINK); ?>letters/src/outboxmylist.php" target="indexTop">送信箱</a></li>
        <?php } ?>
        <?php if (!isLogin()) { /* ログアウト時 */ ?>
          <li class="menu-item menu-nolink">受信箱</a></li>
          <li class="menu-item menu-nolink">送信箱</a></li>
        <?php } ?>
      </ul>
    </nav>
<?php } ?>

  </div>

  <div class="index-frame-wrap">
    <iframe id="index-top" name="indexTop" title="ページトップ"
      src="<?php echo h(SITE_LINK); ?>index-top.php">
    </iframe>
  </div>

  <footer class="index-footer">
    <div class="index-footer-menu">
      <a href="<?php echo h(SITE_LINK); ?>admin.php">管理画面</a>
      <a href="<?php echo h(SITE_LINK); ?>users/src/logout.php" target="indexTop">ログアウト</a>
      <?php if (!isLogin()) { /* ログアウト時 */ ?>
        <a href="<?php echo h(SITE_LINK); ?>users/src/login.php" target="indexTop">ログイン</a>
      <?php } ?>
    </div>
    <div class="index-copyright">
      Copyright (c) 2022 Kunugibayashi<br>
      Released under the MIT license<br>
      <a href="https://opensource.org/licenses/mit-license.php">
        https://opensource.org/licenses/mit-license.php
      </a>
    </div>
  </footer>

</div>
</body>
</html>
