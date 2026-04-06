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
  <h3 class="outbox-title"><?php echo h($title); ?></h3>

  <?php if (isset($inbox) && usedArr($inbox)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap my-inbox-table-wrap">
      <table>
        <tr>
          <th class="cell-modified">更新日</th>
          <th>宛先</th>
          <th>差出人</th>
          <th>タイトル</th>
        </tr>
        <?php foreach ($inbox as $key => $value) { ?>
          <tr>
              <td><?php echo h($value['modified']); ?></td>
              <td><?php echo h($value['tofullname']); ?></td>
              <td><?php echo h($value['fromfullname']); ?></td>
              <td><a href="<?php echo h($inboxFileName); ?>#id-message<?php echo h($value['id']); ?>"><?php echo h($value['title']); ?></a></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <?php if (isset($outbox) && usedArr($outbox)) { /* 登録がある場合に表示 */ ?>
    <div class="table-wrap my-outbox-table-wrap">
      <table>
        <tr>
          <th class="cell-modified">更新日</th>
          <th class="cell-status">ステータス</th>
          <th>宛先</th>
          <th>差出人</th>
          <th>タイトル</th>
        </tr>
        <?php foreach ($outbox as $key => $value) { ?>
          <tr>
            <td><?php echo h($value['modified']); ?></td>
            <td><?php echo h($value['status']); ?></td>
            <td><?php echo h($value['tofullname']); ?></td>
            <td><?php echo h($value['fromfullname']); ?></td>
            <td><a href="<?php echo h($outboxFileName); ?>#id-message<?php echo h($value['id']); ?>"><?php echo h($value['title']); ?></a></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

</body>
</html>