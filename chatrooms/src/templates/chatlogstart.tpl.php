<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo h($chatroom['title']); ?></title>
  <!-- DB参照値用 -->
  <?php echo renderDbCssVariables($chatroom); ?>
  <!-- チャット画面用CSS -->
  <style><?php echo renderTemplateCssString($chatroom); ?></style>
  <!-- DB登録のCSS記載 -->
  <?php if (usedStr($chatroom['roomcss'])) echo '<style>' . h($chatroom['roomcss']) . '</style>'; ?>
  </head>
<body>
<div class="content-log-wrap">

  <header class="chatroom-header-wrap">
    <h3 class="chatroom-header-title">
      <?php if ($chatroom['issecret']) { ?>【秘匿】<?php } ?><?php echo h($chatroom['title']); ?>
      <div class="chatroom-header-guide">
        <?php echo h($chatroom['guide']); ?>
      </div>
    </h3>
    <div class="chatroom-item-wrap">
      <ul class="chatroom-item-group">
        <li class="chatroom-item-title">ログ表示</li>
        <li class="chatroom-item"><span id="id-info-lognum">―</span>行</li>
      </ul>
      <ul class="chatroom-item-group">
        <li class="chatroom-item-title">ログ更新</li>
        <li class="chatroom-item"><span id="id-info-logsec">0</span>秒</li>
      </ul>
    </div>
  </header>
  <div class="entries-wrap">
    <h5 class="entries-title">参加者：</h5>
    <ul id="id-chat-entries" class="entries-item-group"><?php /* id="id-chat-entries" は変更しないこと。ログ一覧で使うため */ ?>
      <?php if (!usedArr($chatentries)) { /* 参加者がいない場合 */ ?>
        <li class="entries-no-item">なし</li>
      <?php } ?>
      <?php if (usedArr($chatentries)) { /* 参加者がいる場合 */ ?>
        <?php foreach ($chatentries as $key => $chatentry) { ?>
          <li class="entries-item" style="background-color: <?php echo h($chatentry['bgcolor']); ?>;" value="<?php echo h($chatentry['characterid']); ?>">
            <span style="color: <?php echo h($chatentry['color']); ?>;" ><?php echo h($chatentry['fullname']); ?></span>
          </li>
        <?php } ?>
      <?php } ?>
    </ul>
  </div>
  <div id="id-log-wrap" class="log-wrap">
