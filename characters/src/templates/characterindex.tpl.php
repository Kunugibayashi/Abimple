<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>名簿一覧</title>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="./css/base.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/<?php echo h($siteTemplate); ?>.css?up=<?php echo h($updateDate); ?>"/>
  <link rel="stylesheet" href="./css/user-edit.css?up=<?php echo h($updateDate); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="./css/responsive.css?up=<?php echo h($updateDate); ?>"/>
</head>
<body>

<div class="content-wrap">
  <h3 class="frame-title">名簿一覧</h3>

    <div class="table-wrap namelist-table-wrap">
      <table>
        <tr>
          <th class="cell-fullname"><?php echo h(NAMELIST_NAME); ?></th>
          <th class="cell-color"><?php echo h(NAMELIST_COLOR); ?></th>
          <th class="cell-bgcolor"><?php echo h(NAMELIST_BGCOLOR); ?></th>
          <?php if ($columns['gender']['enabled']) { ?>
            <th class="cell-gender"><?php echo h($columns['gender']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['species']['enabled']) { ?>
            <th class="cell-species"><?php echo h($columns['species']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['team']['enabled']) { ?>
            <th class="cell-team"><?php echo h($columns['team']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['job']['enabled']) { ?>
            <th class="cell-job"><?php echo h($columns['job']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free1']['enabled']) { ?>
            <th class="cell-free1"><?php echo h($columns['free1']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free2']['enabled']) { ?>
            <th class="cell-free2"><?php echo h($columns['free2']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free3']['enabled']) { ?>
            <th class="cell-free3"><?php echo h($columns['free3']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free4']['enabled']) { ?>
            <th class="cell-free4"><?php echo h($columns['free4']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free5']['enabled']) { ?>
            <th class="cell-free5"><?php echo h($columns['free5']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free6']['enabled']) { ?>
            <th class="cell-free6"><?php echo h($columns['free6']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free7']['enabled']) { ?>
            <th class="cell-free7"><?php echo h($columns['free7']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free8']['enabled']) { ?>
            <th class="cell-free8"><?php echo h($columns['free8']['label']); ?></th>
          <?php } ?>
          <?php if ($columns['free9']['enabled']) { ?>
            <th class="cell-free9"><?php echo h($columns['free9']['label']); ?></th>
          <?php } ?>
          <th class="cell-created">作成日</th>
          <th class="cell-modified">更新日</th>
        </tr>
        <?php foreach ($characters as $key => $character) { ?>
          <tr>
            <td><a class="character-view-link" href="./<?php echo h($character['id']); ?>.html"><?php echo h($character['fullname']); ?></a></td>
            <td><span style="color: <?php echo h($character['color']); ?>; "><?php echo h($character['color']); ?></span></td>
            <td><span style="color: <?php echo h($character['bgcolor']); ?>; "><?php echo h($character['bgcolor']); ?></span></td>
            <?php if ($columns['gender']['enabled']) { ?>
              <td><?php echo h($character['gender']); ?></td>
            <?php } ?>
            <?php if ($columns['species']['enabled']) { ?>
              <td><?php echo h($character['species']); ?></td>
            <?php } ?>
            <?php if ($columns['team']['enabled']) { ?>
              <td><?php echo h($character['team']); ?></td>
            <?php } ?>
            <?php if ($columns['job']['enabled']) { ?>
              <td><?php echo h($character['job']); ?></td>
            <?php } ?>
            <?php if ($columns['free1']['enabled']) { ?>
              <td><?php echo h($character['free1']); ?></td>
            <?php } ?>
            <?php if ($columns['free2']['enabled']) { ?>
              <td><?php echo h($character['free2']); ?></td>
            <?php } ?>
            <?php if ($columns['free3']['enabled']) { ?>
              <td><?php echo h($character['free3']); ?></td>
            <?php } ?>
            <?php if ($columns['free4']['enabled']) { ?>
              <td><?php echo h($character['free4']); ?></td>
            <?php } ?>
            <?php if ($columns['free5']['enabled']) { ?>
              <td><?php echo h($character['free5']); ?></td>
            <?php } ?>
            <?php if ($columns['free6']['enabled']) { ?>
              <td><?php echo h($character['free6']); ?></td>
            <?php } ?>
            <?php if ($columns['free7']['enabled']) { ?>
              <td><?php echo h($character['free7']); ?></td>
            <?php } ?>
            <?php if ($columns['free8']['enabled']) { ?>
              <td><?php echo h($character['free8']); ?></td>
            <?php } ?>
            <?php if ($columns['free9']['enabled']) { ?>
              <td><?php echo h($character['free9']); ?></td>
            <?php } ?>
            <td><?php echo h($character['created']); ?></td>
            <td><?php echo h($character['modified']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>

</div>

</body>
</html>
