<div class="view-wrap letters-view-wrap" id="id-message<?php echo h($id); ?>">
  <div class="view-contents">
    <ul class="view-row">
      <li class="view-col-title">タイトル</li>
      <li class="view-col-item"><?php echo h($title); ?></li>
    </ul>
    <ul class="view-row">
      <li class="view-col-title">宛先</li>
      <li class="view-col-item"><?php echo h($tofullname); ?></li>
    </ul>
    <ul class="view-row">
      <li class="view-col-title">差出人</li>
      <li class="view-col-item"><?php echo h($fromfullname); ?></li>
    </ul>
    <ul class="view-row">
      <li class="view-col-title">日付</li>
      <li class="view-col-item"><?php echo h($modified); ?></li>
    </ul>
    <ul class="view-row view-message-row">
      <li class="view-col-title view-message-title"><?php echo h($title); ?></li>
      <li class="view-col-item  view-message-item"><?php echo hb($message); ?></li>
    </ul>
  </div>
</div>
