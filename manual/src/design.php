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
  <title>デザイン確認ページ</title>
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
  <h3 class="frame-title">デザイン確認ページ</h3>

  <div class="note-wrap">
    <p class="note">
      おおまかなデザインの確認が可能です。<br>
      <span class="point">テンプレート選択の目安</span>にしてください。<br>
    </p>
  </div>

  <div class="err-mes">エラーメッセージの表示色</div>
  <div class="success-mes">情報メッセージの表示色</div>

  <div class="view-wrap">
    <div class="view-contents">
      <ul class="view-row">
        <li class="view-col-title">項目名</li>
        <li class="view-col-item">項目内容の表示</li>
      </ul>
      <ul class="view-row">
        <li class="view-col-title">項目名</li>
        <li class="view-col-item">項目内容の表示</li>
      </ul>
      <ul class="view-row view-detail-row">
        <li class="view-col-title view-detail-title">テキストエリア項目</li>
        <li class="view-col-item view-detail-item">
          詳細文。
        </li>
      </ul>
    </div>
  </div>

  <div class="view-wrap infomation-view-wrap">
  <div class="view-contents">
    <ul class="view-row">
      <li class="view-col-title-only">お知らせタイトル</li>
    </ul>
    <ul class="view-row">
      <li class="view-col-title">ID</li>
      <li class="view-col-item">X</li>
    </ul>
    <ul class="view-row">
      <li class="view-col-title">件名</li>
      <li class="view-col-item">お知らせタイトル</li>
    </ul>
    <ul class="view-row view-detail-row">
      <li class="view-col-item view-detail-item">詳細文。</li>
    </ul>
  </div>

  <div class="view-wrap letters-view-wrap">
    <div class="view-contents" id="id-11">
      <ul class="view-row">
        <li class="view-col-title">宛先</li>
        <li class="view-col-item">管理人</li>
      </ul>
      <ul class="view-row">
        <li class="view-col-title">差出人</li>
        <li class="view-col-item">管理人</li>
      </ul>
      <ul class="view-row">
        <li class="view-col-title">日付</li>
        <li class="view-col-item">2022-03-06 16:54:44</li>
      </ul>
      <ul class="view-row view-message-row">
        <li class="view-col-title view-message-title">管理人から管理人へ</li>
        <li class="view-col-item  view-message-item">てすとです。</li>
      </ul>
    </div>
  </div>

  <div class="page-back-wrap">
    <button type="button" class="tolist-button">一覧に戻る</button>
    <button type="button" class="sitetop-button">サイトトップへ</button><!-- jQuery -->
  </div>

  <div class="search-wrap">
    <form name="search-form" class="search-form" action="" method="POST">
      <div class="search-key-wrap">
        <ul class="search-row">
          <li class="search-col-title">XXXXX</li>
          <li class="search-col-item"><input type="text" name="" value="" maxlength=""></li>
        </ul>
        <ul class="search-row">
          <li class="search-col-title">テスト項目</li>
          <li class="search-col-item"><input type="text" name="" value="" maxlength=""></li>
        </ul>
      </div>
      <div class="search-button-wrap">
        <button type="submit" class="search-and-button">AND検索</button>
      </div>
    </form>
  </div>

  <div class="paging-wrap">
    <ul class="paging-group">
      <li>最初へ</li>
      <li>前へ</li>
      <li class="paging-current"><a href="">1</a></li>
      <li><a href="">2</a></li>
      <li><a href="">次へ</a></li>
      <li><a href="">最後へ</a></li>
    </ul>
  </div>
  <div class="table-wrap">
    <table>
      <tr>
        <th>操作</th>
        <th>id</th>
        <th>ユーザー名</th>
        <th>作成日</th>
        <th>更新日</th>
      </tr>
      <tr>
        <td class="cell-action">
          <button type="button" class="edit-button" value="">編集</button>
          <button type="button" class="warning delete-button" value="">削除</button>
        </td>
        <td class="cell-id">99999</td>
        <td class="cell-username">username</td>
        <td class="cell-created">YYYY-MM-DD hh:mm:ss</td>
        <td class="cell-modified">YYYY-MM-DD hh:mm:ss</td>
      </tr>
      <tr>
        <td class="cell-action">
          <button type="button" class="edit-button" value="">編集</button>
          <button type="button" class="warning delete-button" value="">削除</button>
        </td>
        <td class="cell-id">99999</td>
        <td class="cell-username">username</td>
        <td class="cell-created">YYYY-MM-DD hh:mm:ss</td>
        <td class="cell-modified">YYYY-MM-DD hh:mm:ss</td>
      </tr>
      <tr>
        <td class="cell-action">
          <button type="button" class="edit-button" value="">編集</button>
          <button type="button" class="warning delete-button" value="">削除</button>
        </td>
        <td class="cell-id">99999</td>
        <td class="cell-username">username</td>
        <td class="cell-created">YYYY-MM-DD hh:mm:ss</td>
        <td class="cell-modified">YYYY-MM-DD hh:mm:ss</td>
      </tr>
    </table>
  </div>

  <div class="form-wrap">
    <form name="" class="" action="" method="POST">
      <ul class="form-row">
        <li class="form-col-title">項目名<div class="mandatory-mark"></div></li>
        <li class="form-col-item"><input type="text" name="" value=""></li>
        <li class="form-col-note">注意書き</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">項目名<div class="optional-mark"></div></li>
        <li class="form-col-item"><input type="password" name="" value=""></li>
        <li class="form-col-note">注意書き</li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">プルダウン</li>
        <li class="form-col-item">
          <div class="select-wrap">
            <select name="species">
              <option value="">プルダウン１</option>
              <option value="">プルダウン２</option>
              <option value="">プルダウン３</option>
            </select>
          </div>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title">ラジオボタン</li>
        <li class="form-col-item">
          <label><input type="radio" name="rrr" value="">１</label>
          <label><input type="radio" name="rrr" value="">２</label>
          <label><input type="radio" name="rrr" value="">３</label>
        </li>
      </ul>
      <ul class="form-row">
        <li class="form-col-title"><?php echo h(NAMELIST_DETAIL_NAME); ?></li>
        <li class="form-col-item"><textarea name="detail"></textarea></li>
      </ul>
      <div class="form-button-wrap">
        <button type="submit">通常ボタン</button>
        <button type="submit" class="warning">強調ボタン</button>
      </div>
    </form>
  </div>

  <h1>h1タイトル</h1>
  <pre>

  </pre>
  <h2>h2タイトル</h2>
  <pre>

  </pre>
  <h3>h3タイトル</h3>
  <pre>

  </pre>
  <h4 class="chatroom-title">チャットルームタイトル</h3>
  <pre>

  </pre>



</div>
</body>
</html>
