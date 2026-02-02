<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h($character['fullname'] ?? ''); ?></title>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="./css/base.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/<?php echo h($siteTemplate); ?>.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/user-edit.css?up=<?php echo h($updateDate); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="./css/responsive.css?up=<?php echo h($updateDate); ?>"/>
</head>
<body>

<div class="content-wrap">
  <h3 class="frame-title">名簿参照</h3>

    <div class="view-wrap view-character-wrap">
      <div class="view-contents">
        <ul class="view-row">
          <li class="view-col-title"><?php echo h($columns['id']['label']); ?></li>
          <li class="view-col-item"><?php echo h($character['id']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h($columns['name']['label']); ?></li>
          <li class="view-col-item"><?php echo h($character['fullname']); ?></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h($columns['color']['label']); ?></li>
          <li class="view-col-item"><span style="color: <?php echo h($character['color']); ?>; "><?php echo h($character['color']); ?></span></li>
        </ul>
        <ul class="view-row">
          <li class="view-col-title"><?php echo h($columns['bgcolor']['label']); ?></li>
          <li class="view-col-item"><span style="color: <?php echo h($character['bgcolor']); ?>; "><?php echo h($character['bgcolor']); ?></span></li>
        </ul>
        <?php if ($columns['gender']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['gender']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['gender']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['species']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['species']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['species']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['team']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['team']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['team']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['job']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['job']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['job']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free1']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free1']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free1']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free2']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free2']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free2']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free3']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free3']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free3']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free4']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free4']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free4']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free5']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free5']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free5']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free6']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free6']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free6']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free7']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free7']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free7']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free8']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free8']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free8']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free9']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['free9']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['free9']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['comment']['enabled']) { ?>
          <ul class="view-row">
            <li class="view-col-title"><?php echo h($columns['comment']['label']); ?></li>
            <li class="view-col-item"><?php echo h($character['comment']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free10']['enabled']) { ?>
          <ul class="view-row view-free10-row">
            <li class="view-col-title view-free10-title"><?php echo h($columns['free10']['label']); ?></li>
            <li class="view-col-item view-free10-item"><?php echo ht($character['free10']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free11']['enabled']) { ?>
          <ul class="view-row view-free11-row">
            <li class="view-col-title view-free11-title"><?php echo h($columns['free11']['label']); ?></li>
            <li class="view-col-item view-free11-item"><?php echo ht($character['free11']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['free12']['enabled']) { ?>
          <ul class="view-row view-free12-row">
            <li class="view-col-title view-free12-title"><?php echo h($columns['free12']['label']); ?></li>
            <li class="view-col-item view-free12-item"><?php echo ht($character['free12']); ?></li>
          </ul>
        <?php } ?>
        <?php if ($columns['detail']['enabled']) { ?>
          <ul class="view-row view-detail-row">
            <li class="view-col-title view-detail-title"><?php echo h($columns['detail']['label']); ?></li>
            <li class="view-col-item view-detail-item"><?php echo ht($character['detail']); ?></li>
          </ul>
        <?php } ?>
      </div>

      <div class="page-back-wrap">
        <a href="./index.html">一覧に戻る</a>
      </div>

</div>

</body>
</html>
