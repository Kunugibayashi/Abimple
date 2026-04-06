<?php

// 指定ディレクトリ配下のファイルを再帰的に zip へ追加する
// $dirPath  : 実際に走査する物理ディレクトリ
// $basePath :  zip 内の相対パス算出の基準ディレクトリ
// 通常は同じ値を渡すが、 zip 内の階層構造を変えたい時のために分離している
function addDirToZip(ZipArchive $zip, string $dirPath, string $basePath): void
{
  $basePath = rtrim(realpath($basePath), DIRECTORY_SEPARATOR);

  $files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
  );

  foreach ($files as $file) {
    if (!$file->isFile()) {
      continue;
    }

    // ダミー保持ファイルは zip に出力しない
    $name = $file->getFilename();
    if (preg_match('/^_save_.*\.txt$/', $name)) {
      continue;
    }
    // アクセス制御ファイルは zip に出力しない
    if ($name === '.htaccess') {
      continue;
    }

    $fullPath = realpath($file->getRealPath());

    // basePath を削って zip 内相対パスを作る
    $localPath = ltrim(
      str_replace($basePath, '', $fullPath),
      DIRECTORY_SEPARATOR
    );

    $zip->addFile($fullPath, $localPath);
  }
}

// define → 配列化 関数
// config の 名簿の表示カラム設定 とあわせること
function buildNameListColumns(): array
{
  $columns = [
    'id' => [
      'enabled' => true,
      'label' => defined('NAMELIST_ID') ? NAMELIST_ID : 'ID',
    ],
    'name' => [
      'enabled' => true,
      'label' => defined('NAMELIST_NAME') ? NAMELIST_NAME : '名前',
    ],
    'color' => [
      'enabled' => true,
      'label' => defined('NAMELIST_COLOR') ? NAMELIST_COLOR : '文字色',
    ],
    'bgcolor' => [
      'enabled' => true,
      'label' => defined('NAMELIST_BGCOLOR') ? NAMELIST_BGCOLOR : '背景色',
    ],

    'imgfile' => [
      'enabled' => (defined('NAMELIST_UPLOAD_IMAGE') ? (bool)NAMELIST_UPLOAD_IMAGE : false),
    ],

    'gender' => [
      'enabled' => (defined('NAMELIST_GENDER') ? (bool)NAMELIST_GENDER : false),
      'label' => defined('NAMELIST_GENDER_NAME') ? NAMELIST_GENDER_NAME : '',
    ],
    'species' => [
      'enabled' => (defined('NAMELIST_SPECIES') ? (bool)NAMELIST_SPECIES : false),
      'label' => defined('NAMELIST_SPECIES_NAME') ? NAMELIST_SPECIES_NAME : '',
    ],
    'team' => [
      'enabled' => (defined('NAMELIST_TEAM') ? (bool)NAMELIST_TEAM : false),
      'label' => defined('NAMELIST_TEAM_NAME') ? NAMELIST_TEAM_NAME : '',
    ],
    'job' => [
      'enabled' => (defined('NAMELIST_JOB') ? (bool)NAMELIST_JOB : false),
      'label' => defined('NAMELIST_JOB_NAME') ? NAMELIST_JOB_NAME : '',
    ],

    'free1' => [
      'enabled' => (defined('NAMELIST_FREE1') ? (bool)NAMELIST_FREE1 : false),
      'label' => defined('NAMELIST_FREE1_NAME') ? NAMELIST_FREE1_NAME : '',
    ],
    'free2' => [
      'enabled' => (defined('NAMELIST_FREE2') ? (bool)NAMELIST_FREE2 : false),
      'label' => defined('NAMELIST_FREE2_NAME') ? NAMELIST_FREE2_NAME : '',
    ],
    'free3' => [
      'enabled' => (defined('NAMELIST_FREE3') ? (bool)NAMELIST_FREE3 : false),
      'label' => defined('NAMELIST_FREE3_NAME') ? NAMELIST_FREE3_NAME : '',
    ],
    'free4' => [
      'enabled' => (defined('NAMELIST_FREE4') ? (bool)NAMELIST_FREE4 : false),
      'label' => defined('NAMELIST_FREE4_NAME') ? NAMELIST_FREE4_NAME : '',
    ],
    'free5' => [
      'enabled' => (defined('NAMELIST_FREE5') ? (bool)NAMELIST_FREE5 : false),
      'label' => defined('NAMELIST_FREE5_NAME') ? NAMELIST_FREE5_NAME : '',
    ],
    'free6' => [
      'enabled' => (defined('NAMELIST_FREE6') ? (bool)NAMELIST_FREE6 : false),
      'label' => defined('NAMELIST_FREE6_NAME') ? NAMELIST_FREE6_NAME : '',
    ],
    'free7' => [
      'enabled' => (defined('NAMELIST_FREE7') ? (bool)NAMELIST_FREE7 : false),
      'label' => defined('NAMELIST_FREE7_NAME') ? NAMELIST_FREE7_NAME : '',
    ],
    'free8' => [
      'enabled' => (defined('NAMELIST_FREE8') ? (bool)NAMELIST_FREE8 : false),
      'label' => defined('NAMELIST_FREE8_NAME') ? NAMELIST_FREE8_NAME : '',
    ],
    'free9' => [
      'enabled' => (defined('NAMELIST_FREE9') ? (bool)NAMELIST_FREE9 : false),
      'label' => defined('NAMELIST_FREE9_NAME') ? NAMELIST_FREE9_NAME : '',
    ],

    'comment' => [
      'enabled' => (defined('NAMELIST_COMMENT') ? (bool)NAMELIST_COMMENT : false),
      'label' => defined('NAMELIST_COMMENT_NAME') ? NAMELIST_COMMENT_NAME : '',
    ],
    'url' => [
      'enabled' => (defined('NAMELIST_URL') ? (bool)NAMELIST_URL : false),
      'label' => defined('NAMELIST_URL_NAME') ? NAMELIST_URL_NAME : '',
    ],

    'free10' => [
      'enabled' => (defined('NAMELIST_FREE10') ? (bool)NAMELIST_FREE10 : false),
      'label' => defined('NAMELIST_FREE10_NAME') ? NAMELIST_FREE10_NAME : '',
    ],
    'free11' => [
      'enabled' => (defined('NAMELIST_FREE11') ? (bool)NAMELIST_FREE11 : false),
      'label' => defined('NAMELIST_FREE11_NAME') ? NAMELIST_FREE11_NAME : '',
    ],
    'free12' => [
      'enabled' => (defined('NAMELIST_FREE12') ? (bool)NAMELIST_FREE12 : false),
      'label' => defined('NAMELIST_FREE12_NAME') ? NAMELIST_FREE12_NAME : '',
    ],
    'detail' => [
      'enabled' => (defined('NAMELIST_DETAIL') ? (bool)NAMELIST_DETAIL : false),
      'label' => defined('NAMELIST_DETAIL_NAME') ? NAMELIST_DETAIL_NAME : '',
    ],
  ];

  return $columns;
}
