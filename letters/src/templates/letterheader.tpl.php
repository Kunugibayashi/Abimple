<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h($title); ?></title>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="./css/base.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/<?php echo h($siteTemplate); ?>.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/user-edit.css?up=<?php echo h($updateDate); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="./css/responsive.css?up=<?php echo h($updateDate); ?>"/>
</head>
<body>

<div class="content-wrap">
  <h3 class="frame-title"><?php echo h($title); ?></h3>
