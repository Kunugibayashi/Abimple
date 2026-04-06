<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>管理説明書</title>
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
<body>
<div class="content-wrap">
  <h3 class="manual-title">管理説明書</h3>

  <div class="manual-menu-wrap">
    <ul class="menu-item-group">
      <li class="menu-item"><a href="#1">はじめに</a></li>
      <li class="menu-item"><a href="#2">管理ユーザーのパスワードを忘れてしまったら</a></li>
      <li class="menu-item"><a href="#3">サイトのテンプレートを変更する</a></li>
      <li class="menu-item"><a href="#4">私書の公開状態を変更する</a></li>
      <li class="menu-item"><a href="#5">複数キャラクター入室可能にする</a></li>
      <li class="menu-item"><a href="#6">ベル音を変更する</a></li>
      <li class="menu-item"><a href="#7">チャットルーム閲覧者数の表示非表示を切り替える</a></li>
      <li class="menu-item"><a href="#8">名簿の項目を変更する</a></li>
      <li class="menu-item"><a href="#9">チャットルームを追加する</a></li>
      <li class="menu-item"><a href="#10">チャットルームの設定を変更する</a></li>
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
/**
 * 管理者ユーザー名。
 * 必ず変更してください。
 * 英数字のみ。
 *
 * このユーザー名で登録したユーザーが管理者画面で編集できます。
 */
define('ADMIN_USERNAME', 'admin');
<pre></code></div>
<div class="code-wrap"><code><pre>
/**
 * サイト名。
 * 必ず変更してください。
 *
 * 一括DL時のファイル名として使用するため、特殊記号は出力時にzipファイル名から削除されます。
 */
define('SITE_TITLE', 'Abimple');
<pre></code></div>
<div class="code-wrap"><code><pre>
/**
 * index.phpまでのPATH。
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
/**
 * テンプレート名。
 * 編集任意。
 *
 * core/css/ 配下のテンプレート名を入力してください。拡張子はいりません。
 * 例）
 * template1 … シンプルな横型テンプレート
 * template2 … シンプルな縦型テンプレート
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
define('SITE_LETTER_OPEN', <span class="point">2または1または0</span>);<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/**
 * 私書を公開するか。
 * 編集任意。
 *
 * 2:私書を個人のみに公開する
 * 1:私書を全体に公開する
 * 0:私書を使用しない
 */
define('SITE_LETTER_OPEN', 1);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="5">複数キャラクター入室可能にする</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
</p>
<p class="note">
define('CHAT_MULTI_ENTRY_MODE', <span class="point">1または0</span>);<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/**
 * 複数キャラクター入室可能にするか。
 * 編集任意。
 *
 * 1:管理者のみ
 * 0:全員可
 */
define('CHAT_MULTI_ENTRY_MODE', 1);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="6">ベル音を変更する</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
配置可能なフォルダは assets/sound/ のみです。<br>
</p>
<p class="note">
define('CHAT_SOUND_FILE', '<span class="point">拡張子を含めた音ファイル名</span>');<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/**
 * ベル音。
 * 編集任意。
 * ベル音を変更したい場合は /assets/sound 内にファイルを置いて、このファイル名を変更してください。
 * ファイル名は拡張子まで含めて記載してください。
 *
 * 例）
 * pipipi.wav … WAV形式通知音
 * pipipi.mp3 … MP3形式通知音
 */
define('CHAT_SOUND_FILE', 'pipipi.mp3');
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="7">チャットルーム閲覧者数の表示非表示を切り替える</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
</p>
<p class="note">
define('CHAT_ROOM_SHOW_ONLINE', <span class="point">1または0</span>);<br>
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/**
 * チャットルームの閲覧者数を表示するか。
 * 編集任意。
 *
 * 1:表示する
 * 0:表示しない
 */
define('CHAT_ROOM_SHOW_ONLINE', 1);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="8">名簿の項目を変更する</h4>
<div class="note-wrap">
<p class="note">
config.phpの以下の箇所を編集してください。<br>
以下は一例として所属をあげています。項目ごとに修正箇所はわかれていますのでご注意ください。<br>
</p>
<p class="note">
/* 自由設定6 （プルダウン） */<br>
define('NAMELIST_FREE6', <span class="point">1または0</span>);<br>
define('NAMELIST_FREE6_ISDICE', <span class="point">1または0。1にした場合、{表示名}という形式でチャットダイスが使用可能です。</span>);<br>
define('NAMELIST_FREE6_NAME', '<span class="point">名簿タイトル表示名</span>');<br>
define('NAMELIST_FREE6_LIST', [<br>
　'<span class="point">選択表示名</span>' => '<span class="point">選択値。ダイスに使用する場合は右側の値を数値にしてください。</span>',<br>
　<span class="point">※3個以上増やすことも可能</span><br>
]);
</p>
<div class="file-wrap">core/src/config.php</div>
<div class="code-wrap"><code><pre>
/* 自由設定6 （プルダウン） */
define('NAMELIST_FREE6', 0); // ONOFF（1:表示する／0:表示しない）
define('NAMELIST_FREE6_ISDICE', 0); // ダイスの判定に使用可能とするか（1:使用可能／0:使用不可）
define('NAMELIST_FREE6_NAME', '自由設定6'); // 表示名
define('NAMELIST_FREE6_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '--------' => '',
  '自由設定6選択A' => '自由設定6選択A',
  '自由設定6選択B' => '自由設定6選択B',
  '自由設定6選択C' => '自由設定6選択C',
]);
<pre></code></div>
</div>
</div>

<div class="manual-wrap">
<h4 id="9">チャットルームを追加する</h4>
<div class="note-wrap">
<p class="note">
管理画面の「ルーム管理」からチャットルームを追加してください。<br>
<span class="point">ルーム追加時に「Permission denied」が表示される場合</span>は権限がありません。<br>
ファイル編集権限が追加可能なレンタルサーバーを使用してください。<br>
</p>
</div>

<div class="manual-wrap">
<h4 id="10">チャットルームの設定を変更する</h4>
<div class="note-wrap">
<p class="note">
管理ユーザーでログインし、チャットルームトップ画面右上の「管理画面」から設定を変更してください。<br>
</p>
</div>
</div>

</div>
</body>
</html>
