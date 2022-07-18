<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>管理説明書</title>
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
  <h3 class="manual-title">管理説明書</h3>

  <div class="manual-menu-wrap">
    <ul class="menu-item-group">
      <li class="menu-item"><a href="#1">はじめに</a></li>
      <li class="menu-item"><a href="#2">管理ユーザーのパスワードを忘れてしまったら</a></li>
      <li class="menu-item"><a href="#3">サイトのテンプレートを変更する</a></li>
      <li class="menu-item"><a href="#4">私書の公開状態を変更する</a></li>
      <li class="menu-item"><a href="#5">名簿の項目を変更する</a></li>
      <li class="menu-item"><a href="#6">チャットルームを追加する</a></li>
      <li class="menu-item"><a href="#7">チャットルームの設定を変更する</a></li>
    </ul>
  </div>

<div class="manual-wrap">
<h4 id="1">はじめに</h4>
<div class="note-wrap">
<p class="note">
はじめに、config.phpを編集し「管理者ユーザー名」の変更と「サイト名」の変更と「index.phpまでのPATH」の変更をおこなってください。<br>
推測しやすいため、デフォルトの管理者ユーザーを使用するのはおすすめしません。<br>
</p>
<p class="note">
define('ADMIN_USERNAME', '<span class="point">管理者ユーザー名</span>');<br>
define('SITE_TITLE', '<span class="point">サイトタイトル</span>');<br>
define('SITE_ROOT', '<span class="point">index.phpまでのPATH</span>');<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* 管理者ユーザー名。
 * 必ず変更してください。
 * 英数字のみ。
 *
 * このユーザー名で登録したユーザーが管理者画面で編集できます。
 */
define('ADMIN_USERNAME', 'admin');
<pre></code></div>
<div class="code-wrap"><code><pre>
/* サイト名。
 * 必ず変更してください。
 */
define('SITE_TITLE', 'Abimple');
<pre></code></div>
<div class="code-wrap"><code><pre>
/* index.phpまでのPATH。
 * 必ず変更してください。
 *
 * 例）https://abitopia.com/Abimple/index.php であれば '/Abimple'
 * 例）https://abitopia.com/ab/Abitopia/index.php であれば '/ab/Abitopia'
 */
define('SITE_ROOT', '/Abimple');
<pre></code></div>
<p class="note">
次に、「管理者ユーザー名」に記載したアカウントでユーザー登録をしてください。<br>
このユーザーが管理者として全ユーザーの「編集」「削除」をおこなえるようになります。<br>
</p>
</div>
</div>

<div class="manual-wrap">
<h4 id="2">管理ユーザーのパスワードを忘れてしまったら</h4>
<div class="note-wrap">
<p class="note">
config.phpのユーザー名を新しいものに変更し、新しいアカウントをユーザー登録してください。<br>
私書の公開状態を個別のみにしている場合、サイトから<span class="point">古い管理ユーザー名での私書は確認できません。</span><br>
</p>
<p class="note">
ログイン後に古い管理ユーザーは削除することをおすすめします。<br>
</p>
</div>
</div>

<div class="manual-wrap">
<h4 id="3">サイトのテンプレートを変更する</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
</p>
<p class="note">
define('SITE_TEMPLATE', '<span class="point">template1</span>');<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* テンプレート名。
 * 編集任意。
 *
 * core/css/ 配下のテンプレート名を入力してください。拡張子はいりません。
 */
define('SITE_TEMPLATE', 'template1');
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="4">私書の公開状態を変更する</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
</p>
<p class="note">
define('SITE_LETTER_OPEN', <span class="point">1または0</span>);<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* 私書を公開するか。
 * 編集任意。
 * 私書機能を使わない場合は手動でメニューから削除してください。
 *
 * 1:私書を全体に公開する
 * 0:私書を個人のみに公開する
 */
define('SITE_LETTER_OPEN', 1);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="5">名簿の項目を変更する</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
以下は一例として所属をあげています。項目ごとに修正箇所はわかれていますのでご注意ください。<br>
</p>
<p class="note">
/* 所属 （プルダウン） */<br>
define('NAMELIST_TEAM', <span class="point">1または0</span>);<br>
define('NAMELIST_TEAM_NAME', '<span class="point">名簿タイトル表示名</span>');<br>
define('NAMELIST_TEAM_LIST', [<br>
　'<span class="point">選択表示名</span>' => '<span class="point">選択値。検索に使用されるため表示名と同じものをおすすめします。</span>',<br>
　<span class="point">※3個以上増やすことも可能</span><br>
]);
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* 所属 （プルダウン） */
define('NAMELIST_TEAM', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_TEAM_NAME', '所属'); // 表示名
define('NAMELIST_TEAM_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 10 文字。
  '--------' => '',
  '所属選択A' => '所属選択A',
  '所属選択B' => '所属選択B',
  '所属選択C' => '所属選択C',
]);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="6">チャットルームを追加する</h4>
<div class="note-wrap">
<p class="note">
はじめに、chatrooms/roomsにあるdefaultをコピーして、同フォルダ内にペーストし、フォルダにルーム名をつけてください。<span class="point">使用可能なのは英数字のみ</span>です。<br>
以下は一例として「test1」というチャットルームを作っています。<br>
</p>
<div class="code-wrap"><code><pre>
chatrooms/rooms/
  |--default
  |
  |--test1
<pre></code></div>
<p class="note">
もしも、defaultルームを動かしていた場合、前ルームの設定が残っていますので以下のファイルを削除してください。<br>
ない場合は問題ありません。<br>
</p>
<div class="code-wrap"><code><pre>
chatrooms/rooms/
  |--default
  |
  |--test1
     |
     |--logs
     |  |--_save_log.txt
     |  |--※他の.htmlファイルすべてを削除
     |
     |--src
        |--db
           |--_save_db.txt
           |--※他の.dbファイルすべてを削除
<pre></code></div>
<p class="note">
次に、config.phpに新しいチャットルームを追加してください。<br>
この変更を保存した時点で、トップページやルーム一覧にチャットルームが表示されるようになります。<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* 表示チャットルーム。
 * チャットルームを使用する場合は必ず追加してください。
 *
 * 保存ワード「'表示名' => 'ルームDIR',」形式。
 * ルームDIR は、チャットルームトップ画面から遷移する「チャットルーム管理画面」で確認できます。
 * 例） 'デフォルトルーム' => 'default',
 */
define('SITE_CHATROOM', [
  'デフォルトルーム' => 'default',  // 削除可能
  'テストルーム' => 'test1',
]);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="7">チャットルームの設定を変更する</h4>
<div class="note-wrap">
<p class="note">
管理ユーザーでログインし、チャットルームトップ画面右上の「管理画面」から設定を変更してください。<br>
</p>
</div>
</div>

</div>
</body>
</html>
