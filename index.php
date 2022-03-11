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
  <title><?php echo h(SITE_TITLE); ?></title>
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
<div class="index-wrap">

  <header class="index-header">
    <h1 class="index-title"><a href="./index-top.php" target="indexTop"><?php echo h(SITE_TITLE); ?></a></h1>
    <?php if (isLogin()) { /* ログイン時 */ ?>
      <div class="index-login">
        <?php echo h(getUserid()); ?>:<?php echo h(getUsername()); ?>でログイン中...
      </div>
    <?php } ?>
    <button type="button" class="menu-button">Menu</button>
  </header>

  <div class="index-menu">
    <nav class="menu menu-site">
      <h2 class="menu-title">World</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./world/src/world.php" target="indexTop">世界観</a></li>
        <li class="menu-item"><a href="./world/src/rule.php" target="indexTop">利用規約</a></li>
        <li class="menu-item"><a href="./world/src/qa.php" target="indexTop">Q&A</a></li>
      </ul>
    </nav>

    <nav class="menu menu-world">
      <h2 class="menu-title">Site</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./infomation/src/list.php" target="indexTop">お知らせ</a></li>
        <?php if (isLogin()) { /* ログイン時 */ ?>
          <li class="menu-item"><a href="./users/src/mylist.php" target="indexTop">ユーザー管理</a></li>
        <?php } ?>
        <?php if (!isLogin()) { /* ログアウト時 */ ?>
          <li class="menu-item"><a href="./users/src/signup.php" target="indexTop">ユーザー登録</a></li>
        <?php } ?>
        <li class="menu-item"><a href="./chatrooms/src/list.php" target="indexTop">ルーム一覧</a></li>
      </ul>
    </nav>

    <nav class="menu menu-user">
      <h2 class="menu-title">NameList</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./characters/src/list.php" target="indexTop">名簿</a></li>
        <?php if (isLogin()) { /* ログイン時 */ ?>
          <li class="menu-item"><a href="./characters/src/signup.php" target="indexTop">名簿登録</a></li>
          <li class="menu-item"><a href="./characters/src/mylist.php" target="indexTop">名簿管理</a></li>
        <?php } ?>
        <?php if (!isLogin()) { /* ログアウト時 */ ?>
          <li class="menu-item"><a href="./users/src/login.php" target="indexTop">ログイン</a></li>
        <?php } ?>
      </ul>
    </nav>

      <nav class="menu menu-chara">
        <h2 class="menu-title">Tool</h2>
        <ul class="menu-item-group">
            <?php if (SITE_LETTER_OPEN == 1) { /* 公開私書の場合 */ ?>
              <li class="menu-item"><a href="./letters/src/publiclist.php" target="indexTop">公開私書箱</a></li>
            <?php } ?>
            <?php if (isLogin()) { /* ログイン時 */ ?>
              <li class="menu-item"><a href="./letters/src/mylist.php" target="indexTop">私書管理</a></li>
            <?php } ?>
            <li class="menu-item"><a href="./bbs/src/bbslist.php" target="indexTop">掲示板</a></li>
        </ul>
      </nav>
  </div>

  <div class="index-frame-wrap">
    <iframe id="index-top" name="indexTop" title="ページトップ"
      src="./index-top.php">
    </iframe>
  </div>

  <footer class="index-footer">
    <div class="index-footer-menu">
      <a href="./admin.php">管理画面</a>
      <a href="./users/src/logout.php" target="indexTop">ログアウト</a>
      <?php if (!isLogin()) { /* ログアウト時 */ ?>
        <a href="./users/src/login.php" target="indexTop">ログイン</a>
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
