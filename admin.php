<?php
require_once('./core/src/config.php');
require_once('./core/src/functions.php');
require_once('./core/src/session.php');
require_once('./core/src/database.php');
require_once('./core/src/administrator.php');

adminOnly();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h(SITE_TITLE); ?>管理画面</title>
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
    <h1 class="index-title"><a href="./index-top.php" target="indexTop">管理画面</a></h1>
    <?php if (isLogin()) { /* ログイン時 */ ?>
      <div class="index-login">
        <?php echo h(getUserid()); ?>:<?php echo h(getUsername()); ?>でログイン中...
      </div>
    <?php } ?>
    <button type="button" class="menu-button">Menu</button>
  </header>

  <div class="index-menu">
    <nav class="menu menu-site">
      <h2 class="menu-title">Manual</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./manual/src/manual.php" target="indexTop">管理説明書</a></li>
        <li class="menu-item"><a href="./manual/src/design.php" target="indexTop">デザイン確認</a></li>
        <li class="menu-item"><a href="./manual/src/htmltag.php" target="indexTop">使用可能タグ</a></li>
      </ul>
    </nav>
    <nav class="menu menu-site">
      <h2 class="menu-title">Info</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./infomation/src/list.php" target="indexTop">お知らせ管理</a></li>
        <li class="menu-item"><a href="./infomation/src/signup.php" target="indexTop">お知らせ登録</a></li>
      </ul>
    </nav>
    <nav class="menu menu-site">
      <h2 class="menu-title">User</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./users/src/list.php" target="indexTop">ユーザー管理</a></li>
        <li class="menu-item"><a href="./users/src/signup.php" target="indexTop">ユーザー登録</a></li>
      </ul>
    </nav>
    <nav class="menu menu-site">
      <h2 class="menu-title">Chat</h2>
      <ul class="menu-item-group">
        <li class="menu-item"><a href="./chatrooms/src/list.php" target="indexTop">ルーム管理</a></li>
        <li class="menu-item"><a href="./chatrooms/src/entrance.php" target="indexTop">ルーム一覧</a></li>
      </ul>
    </nav>

    <div class="index-warning-wrap">
      <h3 class="index-warning-title">注意事項</h3>
      <div class="note-wrap">
        <p class="note">
          以下、ご注意ください。<br>
        </p>
        <p class="note">
          管理者ユーザーは、管理者ユーザー以外の<span class="point">すべてのユーザーの削除が可能</span>です。<br>
          また、非表示項目も全て表示されます。<br>
        </p>
        <p class="note">
          サイト管理は「管理者」、遊ぶ場合は「その他のユーザー」を想定しています。<br>
        </p>
      </div>
    </div>
  </div>

  <div class="index-frame-wrap">
    <iframe id="index-top" name="indexTop" title="ページトップ"
      src="./index-top.php">
    </iframe>
  </div>

  <footer class="index-footer">
    <div class="index-footer-menu">
      <a href="./index.php">サイトトップ</a>
      <a href="./users/src/logout.php" target="indexTop">ログアウト</a>
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
