<?php

function getPageRoomdir() {
  $path = $_SERVER['SCRIPT_NAME'];
  $path = preg_replace('/^.*\/rooms\//', '', $path);
  $path = preg_replace('/\/src\/.*.php/', '', $path);
  return $path;
}

function firstAccessChatroom($dbname) {
  $dbh = connectRw($dbname);
  insertInitChatrooms($dbh);
  // この関数内のみでコネクションを完結する
  $dbh->close();
}

function firstAccessChatsecrets($dbname) {
  $dbh = connectRw($dbname);
  insertChatsecrets($dbh, '');
  // この関数内のみでコネクションを完結する
  $dbh->close();
}
