<?php
require_once(__DIR__ .'/logger.php');

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

  // ログ一覧用
  $changeStr = preg_replace('/(&lt;)ul id=(&quot;)id-chat-entries(&quot;) class=(&quot;)entries-item-group/i', '<ul id="id-chat-entries" class="entries-item-group', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/ul(&gt;)/i', '</ul>', $changeStr);
  $changeStr = preg_replace('/(&lt;)li class=(&quot;)entries-item(&quot;) style=(&quot;)/i', '<li class="entries-item" style="', $changeStr);
  $changeStr = preg_replace('/;(&quot;) value=(&quot;)1/i', ';" value="', $changeStr);
  $changeStr = preg_replace('/(&lt;)\/li(&gt;)/i', '</li>', $changeStr);

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

function isUnsafeChars(?string $value): bool
{
  if ($value === null || $value === '') {
    return false;
  }
  // 制御文字チェック
  if (preg_match('/[\x00-\x1F\x7F]/u', $value)) {
    return true;
  }
  // 許可しない記号チェック
  if (preg_match('/[!"#$%&\'()\-\^\\\\@\[\];:\/|*?<>`~+=,]/u', $value)) {
    return true;
  }
  return false;
}

function removeUnsafeChars(?string $value): string
{
  if ($value === null) {
    return '';
  }
  // 制御文字を除去
  $result = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
  // 許可しない記号を除去
  $result = preg_replace('/[!"#$%&\'()\-\^\\\\@\[\];:\/|*?<>`~+=,]/u', '', $result);
  // 前後空白を削除
  return trim($result);
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
  if (SITE_LETTER_OPEN == 0) {
    echo '私書は使用できません。';
    exit;
  }
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
    echo '<li class="paging-to-first">';
    echo '<a href="?page=1">最初へ</a>';
    echo '</li>';
  } else {
    echo '<li class="paging-no-link">';
    echo '最初へ';
    echo '</li>';
  }

  if ($nowPage > 1) {
    echo '<li class="paging-to-pre">';
    echo '<a href="?page=' . $prevPage . '">前へ</a>';
    echo '</li>';
  } else {
    echo '<li class="paging-no-link">';
    echo '前へ';
    echo '</li>';
  }

  if ($nowPage == 1) {
    echo '<li class="paging-current">';
    echo '1';
    echo '</li>';
  } else {
    echo '<li class="paging-to-page">';
    echo '<a href="?page=1">1</a>';
    echo '</li>';
  }

  if (1 < ($nowPage - PAGING_PVNT_COUNT) && 2 != ($nowPage - PAGING_PVNT_COUNT)) {
    echo '<li class="paging-no-link">';
    echo '...';
    echo '</li>';
  }

  foreach ($pages as $num) {
    if ($num == $nowPage) {
      echo '<li class="paging-current">';
      echo $num;
      echo '</li>';
    } else {
      echo '<li class="paging-to-page">';
      echo '<a href="?page='. $num .'">' . $num . '</a>';
      echo '</li>';
    }
  }

  if (($totalPage - 1) > $endPage) {
    echo '<li class="paging-no-link">';
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
    echo '<li class="paging-to">';
    echo '<a href="?page='. $totalPage .'">' . $totalPage . '</a>';
    echo '</li>';
  }  else {
    echo '<li class="paging-no-link">';
    echo $totalPage;
    echo '</li>';
  }

  if ($nowPage < $totalPage){
    echo '<li class="paging-to-next">';
    echo '<a href="?page='.$nextPage.'">次へ</a>';
    echo '</li>';
  } else {
    echo '<li class="paging-no-link">';
    echo '次へ';
    echo '</li>';
  }

  if ($nowPage < $totalPage){
    echo '<li class="paging-to-last">';
    echo '<a href="?page=' . $totalPage . '">最後へ</a>';
    echo '</li>';
  } else {
    echo '<li class="paging-no-link">';
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

function nowYmdhi(): string {
  return date('YmdHi');
}

/* ------------------------------------------------------------------------------------------------- */
/* チャットルーム管理用                                                                              */
/* ------------------------------------------------------------------------------------------------- */
// 作成と削除のPathはあわせること
function copyRoomTemplate(string $src, string $dst): array
{
  $errors = [];

  $src = rtrim($src, DIRECTORY_SEPARATOR);
  $dst = rtrim($dst, DIRECTORY_SEPARATOR);

  if (!is_dir($src)) {
    $errors[] = 'コピー元が存在しません: ' . $src;
    return $errors;
  }
  if (!is_dir($dst) && !mkdir($dst, 0755, true)) {
    $errors[] = 'コピー先ルートの作成に失敗しました: ' . $dst;
    return $errors;
  }

  try {
    $directoryIterator = new RecursiveDirectoryIterator(
      $src,
      FilesystemIterator::SKIP_DOTS
    );
    $iterator = new RecursiveIteratorIterator(
      $directoryIterator,
      RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
      $srcPath = $item->getPathname();

      if (strpos($srcPath, $src) !== 0) {
        $errors[] = 'パス生成失敗: ' . $srcPath;
        continue;
      }

      $relativePath = ltrim(substr($srcPath, strlen($src)), DIRECTORY_SEPARATOR);
      $dstPath = $dst . DIRECTORY_SEPARATOR . $relativePath;
      if ($item->isDir()) {
        if (!is_dir($dstPath) && !mkdir($dstPath, 0755, true)) {
          $errors[] = 'ディレクトリ作成失敗: ' . $dstPath;
        }
        continue;
      }

      if (strtolower($item->getExtension()) !== 'php'
        && $item->getFilename() !== '.htaccess'
      ) {
        continue;
      }

      $dstDir = dirname($dstPath);
      if (!is_dir($dstDir) && !mkdir($dstDir, 0755, true)) {
        $errors[] = 'コピー先ディレクトリ作成失敗: ' . $dstDir;
        continue;
      }
      if (!copy($srcPath, $dstPath)) {
        $errors[] = 'コピー失敗: ' . $srcPath . ' -> ' . $dstPath;
      }
    }
  } catch (Throwable $e) {
    $errors[] = 'エラーが発生しました。error.log を参照してください。';
    logError('copyRoomTemplate: ' . $e->getMessage());
  }

  return $errors;
}

// 作成と削除のPathはあわせること
function deleteRoomdir($roomPath): array
{
  $errors = [];

  if (!file_exists($roomPath)) {
    return [];
  }

  try {
    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($roomPath, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
      $path = $item->getPathname();

      if ($item->isDir()) {
        if (!rmdir($path)) {
          $errors[] = 'ディレクトリ削除失敗: ' . $path;
        }
      } else {
        if (!unlink($path)) {
          $errors[] = 'ファイル削除失敗: ' . $path;
        }
      }
    }

    if (file_exists($roomPath) && !rmdir($roomPath)) {
      $errors[] = 'ルームディレクトリ削除失敗: ' . $roomPath;
    }
  } catch (Throwable $e) {
    $errors[] = 'エラーが発生しました。error.log を参照してください。';
    logError('copyRoomTemplate: ' . $e->getMessage());
  }

  if (file_exists($roomPath)) {
    $errors[] = 'ルームの削除に失敗しました。「Permission denied」が表示されている場合は、ディレクトリを直接削除後に再度お試しください。';
  }

  return $errors;
}

/* ------------------------------------------------------------------------------------------------- */
/* チャットルーム用                                                                                  */
/* ------------------------------------------------------------------------------------------------- */
function getPageRoomdir() {
  $path = $_SERVER['SCRIPT_NAME'];
  $path = preg_replace('/^.*\/rooms\//', '', $path);
  $path = preg_replace('/\/src\/.*.php/', '', $path);
  return $path;
}
