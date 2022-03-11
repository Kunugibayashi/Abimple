<?php
/* 管理ページアクセス制御。
 */

function isAdmin() {
  $username = getUsername();
  if (!usedStr($username)) {
    return false;
  }
  if ($username === ADMIN_USERNAME) {
    return true;
  }
  return false;
}

function adminOnly() {
  $result = isAdmin();
  if (!$result) {
    echo 'この機能は管理ユーザーのみ許可されています。';
    exit;
  }
}
