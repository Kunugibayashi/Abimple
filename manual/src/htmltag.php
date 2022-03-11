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
  <title>HTMLタグについて</title>
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
  <h3 class="taghtml-title">HTMLタグについて</h3>

  <div class="note-wrap">
    <p class="note">
      HTMLタグは「HTML可」と記載されている項目のみに使用可能です。<br>
      記載されているタグ以外は使用できません。<br>
      使用例以外の書き方については、Web上のCSSリファレンスを参照してください。<br>
    </p>
  </div>

  <h4 class="taghtml-title">使用可能タグ一覧</h3>
  <div class="table-wrap">
    <table>
      <tbody>
        <tr>
          <th class="cell-tagrole">役割</th>
          <th class="cell-taghtml">タグ名</th>
          <th class="cell-taglook">見え方</th>
          <th class="cell-tagexample">使用例</th>
        </tr>
        <tr>
          <td>ルビ用</td>
          <td>ruby, rp, rt</td>
          <td><ruby>Abimple<rp>（</rp><rt>あびぷる</rt><rp>）</rp></ruby></td>
          <td>&lt;ruby&gt;Abimple&lt;rp&gt;（&lt;/rp&gt;&lt;rt&gt;あびぷる&lt;/rt&gt;&lt;rp&gt;）&lt;/rp&gt;&lt;/ruby&gt;</td>
        </tr>
        <tr>
          <td>装飾用</td>
          <td>span</td>
          <td><span style="">装飾</span></td>
          <td>&lt;span style=&quot;&quot;&gt;装飾&lt;/span&gt;</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h4 class="taghtml-title">タグ使用例</h3>
  <div class="table-wrap">
    <table>
      <tbody>
        <tr>
          <th class="cell-taglook">見え方</th>
          <th class="cell-tagexample">使用例</th>
        </tr>
        <tr>
          <td><span style="font-weight:bold;">サンプル</span></td>
          <td>&lt;span style=&quot;font-weight:bold;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="font-style:oblique;">サンプル</span></td>
          <td>&lt;span style=&quot;font-style:oblique;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="text-decoration:line-through;">サンプル</span></td>
          <td>&lt;span style=&quot;text-decoration:line-through;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="font-size:50%;">サンプル</span></td>
          <td>&lt;span style=&quot;font-size:50%;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="font-size:150%;">サンプル</span></td>
          <td>&lt;span style=&quot;font-size:150%;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="font-size:200%;">サンプル</span></td>
          <td>&lt;span style=&quot;font-size:200%;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="color:teal;">サンプル</span></td>
          <td>&lt;span style=&quot;color:teal;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
        <tr>
          <td><span style="color:#ff0000;">サンプル</span></td>
          <td>&lt;span style=&quot;color:#ff0000;&quot;&gt;サンプル&lt;/span&gt;</td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
