<?php
require_once('../../core/src/config.php');
require_once('../../core/src/functions.php');
require_once('../../core/src/session.php');
require_once('../../core/src/database.php');
require_once('../../core/src/administrator.php');

$searchParams = array();

$searchParams['id'] = searchParam('id', SEARCHKEY_LIMIT);
$searchParams['fullname'] = searchParam('fullname', SEARCHKEY_LIMIT);
$searchParams['color'] = searchParam('color', SEARCHKEY_LIMIT);
$searchParams['bgcolor'] = searchParam('bgcolor', SEARCHKEY_LIMIT);
$searchParams['gender'] = searchParam('gender', SEARCHKEY_LIMIT);
$searchParams['species'] = searchParam('species', SEARCHKEY_LIMIT);
$searchParams['team'] = searchParam('team', SEARCHKEY_LIMIT);
$searchParams['job'] = searchParam('job', SEARCHKEY_LIMIT);
$searchParams['free1'] = searchParam('free1', SEARCHKEY_LIMIT);
$searchParams['free2'] = searchParam('free2', SEARCHKEY_LIMIT);
$searchParams['free3'] = searchParam('free3', SEARCHKEY_LIMIT);
$searchParams['free4'] = searchParam('free4', SEARCHKEY_LIMIT);
$searchParams['free5'] = searchParam('free5', SEARCHKEY_LIMIT);
$searchParams['free6'] = searchParam('free6', SEARCHKEY_LIMIT);
$searchParams['free7'] = searchParam('free7', SEARCHKEY_LIMIT);
$searchParams['free8'] = searchParam('free8', SEARCHKEY_LIMIT);
$searchParams['free9'] = searchParam('free9', SEARCHKEY_LIMIT);
$searchParams['comment'] = searchParam('comment', SEARCHKEY_LIMIT);
$searchParams['url'] = searchParam('url', SEARCHKEY_LIMIT);
$searchParams['detail'] = searchParam('detail', SEARCHKEY_LIMIT);
$searchParams['userid'] = searchParam('userid', SEARCHKEY_LIMIT);
$searchParams['username'] = searchParam('username', SEARCHKEY_LIMIT);
$searchParams['created'] = searchParam('created', SEARCHKEY_LIMIT);
$searchParams['modified'] = searchParam('modified', SEARCHKEY_LIMIT);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // CSRF対策
  setToken();

  // ページングでない場合はパラメータリセット
  if (!usedStr(getParam('page'))) {
    foreach ($searchParams as $key => $value) {
      $searchParams[$key] = '';
    }
    setSearchParam($searchParams);
  }

  // DB接続
  $dbhCharacters = connectRo(CHARACTERS_DB);

  $characters = selectLikeCharactersList($dbhCharacters, $searchParams);
  $pages = splitPages($characters, getNowPage());

  goto outputPage;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkToken();

// 検索値更新
$searchParams['id'] = inputParam('id', SEARCHKEY_LIMIT);
$searchParams['fullname'] = inputParam('fullname', SEARCHKEY_LIMIT);
$searchParams['color'] = inputParam('color', SEARCHKEY_LIMIT);
$searchParams['bgcolor'] = inputParam('bgcolor', SEARCHKEY_LIMIT);
$searchParams['gender'] = inputParam('gender', SEARCHKEY_LIMIT);
$searchParams['species'] = inputParam('species', SEARCHKEY_LIMIT);
$searchParams['team'] = inputParam('team', SEARCHKEY_LIMIT);
$searchParams['job'] = inputParam('job', SEARCHKEY_LIMIT);
$searchParams['free1'] = inputParam('free1', SEARCHKEY_LIMIT);
$searchParams['free2'] = inputParam('free2', SEARCHKEY_LIMIT);
$searchParams['free3'] = inputParam('free3', SEARCHKEY_LIMIT);
$searchParams['free4'] = inputParam('free4', SEARCHKEY_LIMIT);
$searchParams['free5'] = inputParam('free5', SEARCHKEY_LIMIT);
$searchParams['free6'] = inputParam('free6', SEARCHKEY_LIMIT);
$searchParams['free7'] = inputParam('free7', SEARCHKEY_LIMIT);
$searchParams['free8'] = inputParam('free8', SEARCHKEY_LIMIT);
$searchParams['free9'] = inputParam('free9', SEARCHKEY_LIMIT);
$searchParams['comment'] = inputParam('comment', SEARCHKEY_LIMIT);
$searchParams['url'] = inputParam('url', SEARCHKEY_LIMIT);
$searchParams['detail'] = inputParam('detail', SEARCHKEY_LIMIT);
$searchParams['userid'] = inputParam('userid', SEARCHKEY_LIMIT);
$searchParams['username'] = inputParam('username', SEARCHKEY_LIMIT);
setSearchParam($searchParams);

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);

$characters = selectLikeCharactersList($dbhCharacters, $searchParams);
$pages = splitPages($characters, getNowPage());

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
  <title>名簿</title>
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
  <h3 class="frame-title">名簿</h3>

  <?php if (isAdmin()) { /* 管理ユーザーは常に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        管理ユーザーでログインしています。<br>
        「操作」「ユーザーID」「ユーザー名」のカラムは管理ユーザーのみ表示されます。<br>
        管理ユーザーは<span class="point">すべての名簿の「編集」「削除」が可能</span>です。<br>
        管理ユーザーは非表示にしているすべてのカラムが表示されます。<br>
      </p>
    </div>
  <?php } ?>

  <div class="search-wrap">
    <form name="search-form" class="search-form" action="?page=1<?php // 戻る処理のため検索は明示的に1を指定 ?>" method="POST">
      <input type="hidden" name="token" value="<?php echo h(getToken()); ?>">
      <div class="search-key-wrap">
        <ul class="search-row">
          <li class="search-col-title"><?php echo h(NAMELIST_ID); ?></li>
          <li class="search-col-item"><input type="text" name="id" value="<?php echo h($searchParams['id']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
        <ul class="search-row">
          <li class="search-col-title"><?php echo h(NAMELIST_NAME); ?></li>
          <li class="search-col-item"><input type="text" name="fullname" value="<?php echo h($searchParams['fullname']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
        <ul class="search-row">
          <li class="search-col-title"><?php echo h(NAMELIST_COLOR); ?></li>
          <li class="search-col-item"><input type="text" name="color" value="<?php echo h($searchParams['color']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
        <ul class="search-row">
          <li class="search-col-title"><?php echo h(NAMELIST_BGCOLOR); ?></li>
          <li class="search-col-item"><input type="text" name="bgcolor" value="<?php echo h($searchParams['bgcolor']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
        </ul>
        <?php if (NAMELIST_GENDER || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_GENDER_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="gender" value="<?php echo h($searchParams['gender']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_SPECIES || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_SPECIES_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="species" value="<?php echo h($searchParams['species']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_TEAM || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_TEAM_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="team" value="<?php echo h($searchParams['team']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_JOB || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_JOB_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="job" value="<?php echo h($searchParams['job']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE1 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE1_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free1" value="<?php echo h($searchParams['free1']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE2 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE2_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free2" value="<?php echo h($searchParams['free2']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE3 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE3_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free3" value="<?php echo h($searchParams['free3']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE4 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE4_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free4" value="<?php echo h($searchParams['free4']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE5 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE5_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free5" value="<?php echo h($searchParams['free5']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE6 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE6_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free6" value="<?php echo h($searchParams['free6']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE7 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE7_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free7" value="<?php echo h($searchParams['free7']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE8 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE8_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free8" value="<?php echo h($searchParams['free8']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_FREE9 || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_FREE9_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="free9" value="<?php echo h($searchParams['free9']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_COMMENT || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_COMMENT_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="comment" value="<?php echo h($searchParams['comment']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_URL || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_URL_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="url" value="<?php echo h($searchParams['url']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (NAMELIST_DETAIL || isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title"><?php echo h(NAMELIST_DETAIL_NAME); ?></li>
            <li class="search-col-item"><input type="text" name="detail" value="<?php echo h($searchParams['detail']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
        <?php if (isAdmin()) { ?>
          <ul class="search-row">
            <li class="search-col-title">ユーザーID</li>
            <li class="search-col-item"><input type="text" name="userid" value="<?php echo h($searchParams['userid']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
          <ul class="search-row">
            <li class="search-col-title">ユーザー名</li>
            <li class="search-col-item"><input type="text" name="username" value="<?php echo h($searchParams['username']); ?>" maxlength="<?php echo h(SEARCHKEY_LIMIT); ?>"></li>
          </ul>
        <?php } ?>
      </div>
      <div class="search-button-wrap">
        <button type="submit" class="search-and-button">AND検索</button>
      </div>
    </form>
  </div>

  <?php if (!usedArr($pages)) { /* 登録がない場合に表示 */ ?>
    <div class="note-wrap">
      <p class="note">
        データがありません。<br>
      </p>
    </div>
  <?php } ?>

    <div class="paging-wrap">
      <?php outputPaging($characters, getNowPage()); ?>
    </div>
    <div class="sumpaging-wrap">
      <?php outputSumPaging($characters, getNowPage()); ?>
    </div>
    <?php if (usedArr($pages)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap namelist-table-wrap">
      <table>
        <tr>
          <?php if (isAdmin()) { ?>
            <th class="cell-action">操作</th>
          <?php } ?>
          <?php if (isLogin()) { /* ログイン時のみ表示 */ ?>
            <th class="cell-letter">私書</th>
          <?php } ?>
          <th class="cell-id"><?php echo h(NAMELIST_ID); ?></th>
          <th class="cell-fullname"><?php echo h(NAMELIST_NAME); ?></th>
          <th class="cell-color"><?php echo h(NAMELIST_COLOR); ?></th>
          <th class="cell-bgcolor"><?php echo h(NAMELIST_BGCOLOR); ?></th>
          <?php if (NAMELIST_GENDER || isAdmin()) { ?>
            <th class="cell-gender"><?php echo h(NAMELIST_GENDER_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_SPECIES || isAdmin()) { ?>
            <th class="cell-species"><?php echo h(NAMELIST_SPECIES_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_TEAM || isAdmin()) { ?>
            <th class="cell-team"><?php echo h(NAMELIST_TEAM_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_JOB || isAdmin()) { ?>
            <th class="cell-job"><?php echo h(NAMELIST_JOB_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE1 || isAdmin()) { ?>
            <th class="cell-free1"><?php echo h(NAMELIST_FREE1_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE2 || isAdmin()) { ?>
            <th class="cell-free2"><?php echo h(NAMELIST_FREE2_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE3 || isAdmin()) { ?>
            <th class="cell-free3"><?php echo h(NAMELIST_FREE3_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE4 || isAdmin()) { ?>
            <th class="cell-free4"><?php echo h(NAMELIST_FREE4_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE5 || isAdmin()) { ?>
            <th class="cell-free5"><?php echo h(NAMELIST_FREE5_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE6 || isAdmin()) { ?>
            <th class="cell-free6"><?php echo h(NAMELIST_FREE6_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE7 || isAdmin()) { ?>
            <th class="cell-free7"><?php echo h(NAMELIST_FREE7_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE8 || isAdmin()) { ?>
            <th class="cell-free8"><?php echo h(NAMELIST_FREE8_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_FREE9 || isAdmin()) { ?>
            <th class="cell-free9"><?php echo h(NAMELIST_FREE9_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_COMMENT || isAdmin()) { ?>
            <th class="cell-comment"><?php echo h(NAMELIST_COMMENT_NAME); ?></th>
          <?php } ?>
          <?php if (NAMELIST_URL || isAdmin()) { ?>
            <th class="cell-url"><?php echo h(NAMELIST_URL_NAME); ?></th>
          <?php } ?>
          <?php if (isAdmin()) { ?>
            <th class="cell-userid">ユーザーID</th>
            <th class="cell-username">ユーザー名</th>
          <?php } ?>
          <th class="cell-created">作成日</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <?php foreach ($pages as $key => $value) { ?>
          <tr>
            <?php if (isAdmin()) { ?>
              <td>
                <button type="button" class="edit-button" value="<?php echo h($value['id']); ?>">編集</button>
                <button type="button" class="warning delete-button" value="<?php echo h($value['id']); ?>">削除</button>
              </td>
            <?php } ?>
            <?php if (isLogin()) { /* ログイン時のみ表示 */ ?>
              <td>
                <button type="button" class="letter-button transparent-button" value="<?php echo h($value['id']); ?>"><div class="letter-icon"></div></button>
              </td>
            <?php } ?>
            <td><?php echo h($value['id']); ?></td>
            <td><a class="character-view-link" href="./view.php?id=<?php echo h($value['id']); ?>"><?php echo h($value['fullname']); ?><a></td>
            <td><span style="color: <?php echo h($value['color']); ?>; "><?php echo h($value['color']); ?></span></td>
            <td><span style="color: <?php echo h($value['bgcolor']); ?>; "><?php echo h($value['bgcolor']); ?></span></td>
            <?php if (NAMELIST_GENDER || isAdmin()) { ?>
              <td><?php echo h($value['gender']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_SPECIES || isAdmin()) { ?>
              <td><?php echo h($value['species']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_TEAM || isAdmin()) { ?>
              <td><?php echo h($value['team']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_JOB || isAdmin()) { ?>
              <td><?php echo h($value['job']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE1 || isAdmin()) { ?>
              <td><?php echo h($value['free1']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE2 || isAdmin()) { ?>
              <td><?php echo h($value['free2']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE3 || isAdmin()) { ?>
              <td><?php echo h($value['free3']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE4 || isAdmin()) { ?>
              <td><?php echo h($value['free4']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE5 || isAdmin()) { ?>
              <td><?php echo h($value['free5']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE6 || isAdmin()) { ?>
              <td><?php echo h($value['free6']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE7 || isAdmin()) { ?>
              <td><?php echo h($value['free7']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE8 || isAdmin()) { ?>
              <td><?php echo h($value['free8']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_FREE9 || isAdmin()) { ?>
              <td><?php echo h($value['free9']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_COMMENT || isAdmin()) { ?>
              <td><?php echo h($value['comment']); ?></td>
            <?php } ?>
            <?php if (NAMELIST_URL || isAdmin()) { ?>
              <td>
                <?php
                  // 値がある場合のみリンクを表示
                  if (usedStr($value['url'])) {
                    echo '<a href="';
                    echo h($value['url']);
                    echo '" target="_blank">';
                    echo 'Link';
                    echo '</a>';
                  }
                ?>
              </td>
            <?php } ?>
            <?php if (isAdmin()) { ?>
              <td><?php echo h($value['userid']); ?></td>
              <td><?php echo h($value['username']); ?></td>
            <?php } ?>
            <td><?php echo h($value['created']); ?></td>
            <td><?php echo h($value['modified']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
    <div class="paging-wrap">
      <?php outputPaging($characters, getNowPage()); ?>
    </div>
  <?php } ?>

  <form id="edit-form" class="hidden-form" action="./edit.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

  <form id="delete-form" class="hidden-form" action="./delete.php" method="GET">
    <input type="hidden" name="id" value="jQueryで入力">
  </form>

  <form id="letter-form" class="hidden-form" action="../../letters/src/send.php" method="GET">
    <input type="hidden" name="tocharacterid" value="jQueryで入力">
  </form>

</div>
<script> <!-- 各ボタン制御 -->
jQuery(function(){
  jQuery('button.edit-button').on('click', function(){
    var characterid = jQuery(this).val();
    var editForm = jQuery('form#edit-form');
    editForm.find('input[name="id"]').val(characterid);
    editForm.submit();
  });
  jQuery('button.delete-button').on('click', function(){
    var characterid = jQuery(this).val();
    var deleteForm = jQuery('form#delete-form');
    deleteForm.find('input[name="id"]').val(characterid);
    deleteForm.submit();
  });
  jQuery('button.letter-button').on('click', function(){
    var characterid = jQuery(this).val();
    var letterForm = jQuery('form#letter-form');
    letterForm.find('input[name="tocharacterid"]').val(characterid);
    letterForm.submit();
  });
});
</script>
</body>
</html>
