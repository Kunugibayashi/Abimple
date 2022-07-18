<?php
function setJsonHeader() {
  header("Content-Type: application/json; charset=UTF-8");
}

function h($str) {
  $changeStr = htmlspecialchars($str, ENT_QUOTES, "UTF-8");

  return $changeStr;
}

function hb($str) {
  $changeStr = htmlspecialchars($str, ENT_QUOTES, "UTF-8");
  $changeStr = nl2br($changeStr);

  return $changeStr;
}

function ht($str) {
  $changeStr = h($str);
  $changeStr = nl2br($changeStr);

  // 許可するタグを戻す
  // /i修飾子は大文字小文字を区別しない
  $changeStr = preg_replace('/(&lt;)ruby(&gt;)/i', '<ruby>', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/ruby(&gt;)/i', '</ruby>', $changeStr);
  $changeStr = preg_replace('/(&lt;)rt(&gt;)/i', '<rt>', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/rt(&gt;)/i', '</rt>', $changeStr);
  $changeStr = preg_replace('/(&lt;)rp(&gt;)/i', '<rp>', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/rp(&gt;)/i', '</rp>', $changeStr);

  $changeStr = preg_replace('/(&lt;)span style=(&quot;)/i', '<span style="', $changeStr);
  $changeStr = preg_replace('/(&lt;)div class=(&quot;)/i', '<div class="', $changeStr);
  $changeStr = preg_replace('/(&lt;)span class=(&quot;)/i', '<span class="', $changeStr);

  $changeStr = preg_replace('/(&quot;)(&gt;)/i', '">', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/span(&gt;)/i', '</span>', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/div(&gt;)/i', '</div>', $changeStr);

  return $changeStr;
}

function checkedRadio($param, $inputValue) {
  if ($param == $inputValue) {
    return 'checked';
  }
  return '';
}

function selectedOption($param, $inputValue) {
  if ($param == $inputValue) {
    return 'selected';
  }
  return '';
}


function usedArr($array) {
  if (!isset($array)) {
    return false;
  }
  if (is_null($array)) {
    return false;
  }
  if (!is_array($array)) {
    return false;
  }
  if (count($array) > 0) {
    return true;
  }
  return false;
}

function usedStr($str) {
  if (!isset($str)) {
    return false;
  }
  if (is_null($str)) {
    return false;
  }
  if (mb_strlen($str) > 0) {
    return true;
  }
  return false;
}

function paramTrim($param) {
  $param = rtrim($param);
  $param = trim($param);
  return $param;
}

function getParam($key) {
  $param = (isset($_GET) && isset($_GET[$key])) ? $_GET[$key] : '';
  $param = paramTrim($param);
  return $param;
}

function postParam($key) {
  $param = (isset($_POST) && isset($_POST[$key])) ? $_POST[$key] : '';
  $param = paramTrim($param);
  return $param;
}

function inputParam($key, $num) {
  $param = mb_substr(postParam($key), 0, $num);
  $param = paramTrim($param);
  if (!usedStr($param)) {
    $param = mb_substr(getParam($key), 0, $num);
    $param = paramTrim($param);
  }
  return $param;
}

function getNowPage() {
  $nowPage = (int) getParam('page');
  $nowPage = $nowPage ? $nowPage : 1;
  return $nowPage;
}

function getPaging($data, $nowPage) {
  $count = count($data);
  $totalPage = ceil($count / PAGING_LIMIT);

  $prevPage = max($nowPage - 1, 1);
  $nextPage = min($nowPage + 1, $totalPage);

  $startPage = max($nowPage - PAGING_PVNT_COUNT, 2);
  $endPage = min($nowPage + PAGING_PVNT_COUNT, $totalPage - 1);

  $pages = [];
  for ($i = $startPage; $i <= $endPage; $i++) {
    $pages[] = $i;
  }

  return array(
    $totalPage,
    $prevPage,
    $nextPage,
    $startPage,
    $endPage,
    $pages
  );
}

function letterPublicOnly() {
  if (SITE_LETTER_OPEN != 1) {
    echo '公開私書は設定されていません。私書は個別のみ許可されています。';
    exit;
  }
}

function outputPaging($data, $nowPage){
  if (!usedArr($data)) {
    return '';
  }
  list(
    $totalPage,
    $prevPage,
    $nextPage,
    $startPage,
    $endPage,
    $pages
  ) = getPaging($data, $nowPage);

  echo '<ul class="paging-group">';

  if ($nowPage > 1 && $nowPage != 1){
    echo '<li>';
    echo '<a href="?page=1">最初へ</a>';
    echo '</li>';
  } else {
    echo '<li>';
    echo '最初へ';
    echo '</li>';
  }

  if ($nowPage > 1) {
    echo '<li>';
    echo '<a href="?page=' . $prevPage . '">前へ</a>';
    echo '</li>';
  } else {
    echo '<li>';
    echo '前へ';
    echo '</li>';
  }

  if ($nowPage == 1) {
    echo '<li class="paging-current">';
    echo '1';
    echo '</li>';
  } else {
    echo '<li>';
    echo '<a href="?page=1">1</a>';
    echo '</li>';
  }

  if (1 < ($nowPage - PAGING_PVNT_COUNT) && 2 != ($nowPage - PAGING_PVNT_COUNT)) {
    echo '<li>';
    echo '...';
    echo '</li>';
  }

  foreach ($pages as $num) {
    if ($num == $nowPage) {
      echo '<li class="paging-current">';
      echo $num;
      echo '</li>';
    } else {
      echo '<li>';
      echo '<a href="?page='. $num .'">' . $num . '</a>';
      echo '</li>';
    }
  }

  if (($totalPage - 1) > $endPage) {
    echo '<li>';
    echo '...';
    echo '</li>';
  }

  if ($totalPage == 1) {
    // なにもしない
  } else if ($nowPage == $totalPage) { /* 厳密比較はしない */
    echo '<li class="paging-current">';
    echo $totalPage;
    echo '</li>';
  } else if ($nowPage < $totalPage) {
    echo '<li>';
    echo '<a href="?page='. $totalPage .'">' . $totalPage . '</a>';
    echo '</li>';
  }  else {
    echo '<li>';
    echo $totalPage;
    echo '</li>';
  }

  if ($nowPage < $totalPage){
    echo '<li>';
    echo '<a href="?page='.$nextPage.'">次へ</a>';
    echo '</li>';
  } else {
    echo '<li>';
    echo '次へ';
    echo '</li>';
  }

  if ($nowPage < $totalPage){
    echo '<li>';
    echo '<a href="?page=' . $totalPage . '">最後へ</a>';
    echo '</li>';
  } else {
    echo '<li>';
    echo '最後へ';
    echo '</li>';
  }

  echo '</ul>';
}

function outputSumPaging($data, $nowPage){
  if (!usedArr($data)) {
    return '';
  }
  list(
    $totalPage,
    $prevPage,
    $nextPage,
    $startPage,
    $endPage,
    $pages
  ) = getPaging($data, $nowPage);

  echo '<ul class="sumpaging-group">';
  echo "    <li>合計</li>";
  echo "    <li>" .count($data) ."</li>";
  echo "    <li>件</li>";
  echo "    <li>" .$nowPage ."</li>";
  echo "    <li>/</li>";
  echo "    <li>" .$totalPage ."</li>";
  echo "    <li>ページを表示</li>";
  echo '</ul>';
}

function splitPages($data, $nowPage) {
  $min = (PAGING_LIMIT * $nowPage) - PAGING_LIMIT;
  return array_slice($data, $min, PAGING_LIMIT);
}

function fileUpload() {
  $errors = array();

  // エラーチェック
  $ferror = $_FILES['image']['error'];
  if ($ferror !== UPLOAD_ERR_OK) {
    $errors[] = '画像アップロードに失敗しました。';
  }
  if ($ferror === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'ファイルが選択されていません。';
  }
  if ($ferror === UPLOAD_ERR_INI_SIZE) {
    $errors[] = 'ファイルサイズが大きすぎます。';
  }
  if ($ferror === UPLOAD_ERR_FORM_SIZE) {
    $errors[] = 'ファイルが大きすぎます。';
  }
  if ($_FILES['image']['size'] > 10485760) {
    $errors[] = '画像ファイルサイズは 10MB 以内にしてください。';
  }
  if (!$ext = array_search(
                mime_content_type($_FILES['image']['tmp_name']),
                array(
                  'gif' => 'image/gif',
                  'jpg' => 'image/jpeg',
                  'png' => 'image/png',
                ),
                true
              )
  ) {
    $errors[] = 'ファイル形式が不正です。';
  }
  if (count($errors) > 0) {
    return [$errors, ''];
  }

  $tmpImageFile = date('Ymd') .'_' .sha1(uniqid(mt_rand(), true));
  $tmpImageFile = $tmpImageFile .'.' .$ext;

  if (!move_uploaded_file($_FILES['image']['tmp_name'], IMAGE_SAVE_PATH.$tmpImageFile)) {
    $errors[] = 'ファイル保存時にエラーが発生しました。';
    return [$errors, ''];
  }

  // ファイルのパーミッションを確実に0644に設定する
  chmod(IMAGE_SAVE_PATH.$tmpImageFile, 0644);
  return [$errors, $tmpImageFile];
}

// 作成と削除のPathはあわせること
function createRoomdir($roomdir) {

  if (!usedStr($roomdir)){
    // DB削除失敗による二回目以降である可能性があるためエラーにはしない
    return [];
  }

  $defaultPathSrc = './../rooms/default/src';

  $copyPath = './../rooms/' .$roomdir;
  $copyPathLogs = './../rooms/' .$roomdir .'/logs';
  $copyPathSrc = './../rooms/' .$roomdir .'/src';
  $copyPathDb = './../rooms/' .$roomdir .'/src/db';

  if (!file_exists($defaultPathSrc)) {
    $errors[] = 'default ルームが存在しません。';
    return $errors;
  }

  mkdir($copyPath, 0705);
  mkdir($copyPathLogs, 0705);
  mkdir($copyPathSrc, 0705);
  mkdir($copyPathDb, 0705);

  $phpFilePath = glob($defaultPathSrc .'/*.php');
  foreach ($phpFilePath as $key => $phpPath) {
    $file = str_replace("$defaultPathSrc/", '', $phpPath);
    copy("$defaultPathSrc/$file", "$copyPathSrc/$file");
  }

  if (!file_exists($copyPathDb)) {
    $errors[] = 'ルームのコピーに失敗しました。';
    return $errors;
  }

  return [];
}

// 作成と削除のPathはあわせること
function deleteRoomdir($roomdir) {

  if (!usedStr($roomdir)){
    // DB削除失敗による二回目以降である可能性があるためエラーにはしない
    return [];
  }

  $copyPath = './../rooms/' .$roomdir;
  $copyPathLogs = './../rooms/' .$roomdir .'/logs';
  $copyPathSrc = './../rooms/' .$roomdir .'/src';
  $copyPathDb = './../rooms/' .$roomdir .'/src/db';

  $files = glob($copyPathDb .'/*.*');
  foreach ($files as $key => $file) {
    unlink($file);
  }

  $files = glob($copyPathSrc .'/*.*');
  foreach ($files as $key => $file) {
    unlink($file);
  }

  $files = glob($copyPathLogs .'/*.*');
  foreach ($files as $key => $file) {
    unlink($file);
  }

  rmdir($copyPathDb);
  rmdir($copyPathSrc);
  rmdir($copyPathLogs);
  rmdir($copyPath);

  if (file_exists($copyPath)) {
    $errors[] = 'ルームの削除に失敗しました。';
    return $errors;
  }

  return [];
}
