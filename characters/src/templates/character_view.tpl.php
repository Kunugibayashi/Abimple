<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h($character['fullname'] ?? ''); ?></title>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="./css/base.css?up=<?php echo h($update_date); ?>"/>
  <link rel="stylesheet" href="./css/<?php echo h($SITE_TEMPLATE); ?>.css?up=<?php echo h($update_date); ?>"/>
  <link rel="stylesheet" href="./css/user-edit.css?up=<?php echo h($update_date); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="./css/responsive.css?up=<?php echo h($update_date); ?>"/>


</head>
<body>

<div class="content-wrap">
  <h3 class="frame-title">名簿参照</h3>
</div>

</body>
</html>
