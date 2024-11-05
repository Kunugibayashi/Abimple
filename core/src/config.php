<?php
/* エラー出力設定。
 * 編集任意。
 *
 * 1:エラーを出力する
 * 0:エラーを出力しない
 */
ini_set('display_errors', 1);

/* 管理者ユーザー名。
 * 必ず変更してください。
 * 英数字のみ。
 *
 * このユーザー名で登録したユーザーが管理者画面で編集できます。
 */
define('ADMIN_USERNAME', 'admin');

/* サイト名。
 * 必ず変更してください。
 */
define('SITE_TITLE', 'Abimple');

/* index.phpまでのPATH。
 * 必ず変更してください。
 *
 * 例）https://abitopia.com/Abimple/index.php であれば '/Abimple'
 * 例）https://abitopia.com/ab/Abitopia/index.php であれば '/ab/Abitopia'
 */
define('SITE_ROOT', '/Abimple');

/* テンプレート名。
 * 編集任意。
 *
 * core/css/ 配下のテンプレート名を入力してください。拡張子はいりません。
 * 例）
 * template1 … シンプルなテンプレート
 */
define('SITE_TEMPLATE', 'template1');

/* 私書を公開するか。
 * 編集任意。
 * 私書機能を使わない場合は手動でメニューから削除してください。
 *
 * 1:私書を全体に公開する
 * 0:私書を個人のみに公開する
 */
define('SITE_LETTER_OPEN', 1);

/* サイト更新日。
 * 編集任意。
 *
 * CSSなどを更新した際に変更すると、ブラウザをキャッシュクリアしなくとも
 * 新しいファイルが使用されるようになります。
 * 例）2022年1月24日20時00分の場合 '202201242000'
 *
 * CCSデザイン時は以下を指定して下さい。毎回読み込みが行われます。
 * define('SITE_UPDATE', time());
 */
define('SITE_UPDATE', '202311262000');

/* 1ページに表示する項目数。
 * 編集任意。
 *
 * 巨大な数値をいれると読み込みが重くなります。
 */
define('PAGING_LIMIT', 25);

/* ページング時、前後何ページまでリンクを表示するか。
 * 編集任意。
 */
define('PAGING_PVNT_COUNT', 6);

/* 検索キーワードを何文字まで許容するか。
 * 編集任意。
 */
define('SEARCHKEY_LIMIT', 20);

/* 名簿の表示カラム設定。
 * 編集任意。
 * 管理ユーザーはすべてのカラムが常に表示されます。
 */
/* ID （必須項目） */
define('NAMELIST_ID', 'ID'); // 表示名
/* 名前 （必須項目） */
define('NAMELIST_NAME', '名前'); // 表示名
/* 文字色 （必須項目） */
define('NAMELIST_COLOR', '文字色'); // 表示名
/* 背景色 （必須項目） */
define('NAMELIST_BGCOLOR', '背景色'); // 表示名
/* 性別 （ラジオボタン） */
define('NAMELIST_GENDER', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_GENDER_NAME', '性別'); // 表示名
define('NAMELIST_GENDER_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 10 文字。
    '男' => '男',
    '女' => '女',
    'その他' => 'その他',
]);
/* 種別 （プルダウン） */
define('NAMELIST_SPECIES', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_SPECIES_NAME', '種別'); // 表示名
define('NAMELIST_SPECIES_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 10 文字。
  '--------' => '',
  '種別選択A' => '種別選択A',
  '種別選択B' => '種別選択B',
  '種別選択C' => '種別選択C',
]);
/* 所属 （プルダウン） */
define('NAMELIST_TEAM', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_TEAM_NAME', '所属'); // 表示名
define('NAMELIST_TEAM_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 10 文字。
  '--------' => '',
  '所属選択A' => '所属選択A',
  '所属選択B' => '所属選択B',
  '所属選択C' => '所属選択C',
]);
/* 仕事 （テキストボックス） */
define('NAMELIST_JOB', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_JOB_NAME', '仕事'); // 表示名
/* 自由設定1 （ラジオボタン） */
define('NAMELIST_FREE1', 1);
define('NAMELIST_FREE1_NAME', '自由設定1'); // 表示名
define('NAMELIST_FREE1_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '自由設定1選択A' => '自由設定1選択A',
  '自由設定1選択B' => '自由設定1選択B',
  '自由設定1選択C' => '自由設定1選択C',
]);
/* 自由設定2 （ラジオボタン） */
define('NAMELIST_FREE2', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE2_NAME', '自由設定2'); // 表示名
define('NAMELIST_FREE2_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '自由設定2選択A' => '自由設定2選択A',
  '自由設定2選択B' => '自由設定2選択B',
  '自由設定2選択C' => '自由設定2選択C',
]);
/* 自由設定3 （ラジオボタン） */
define('NAMELIST_FREE3', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE3_NAME', '自由設定3'); // 表示名
define('NAMELIST_FREE3_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '自由設定3選択A' => '自由設定3選択A',
  '自由設定3選択B' => '自由設定3選択B',
  '自由設定3選択C' => '自由設定3選択C',
]);
/* 自由設定4 （プルダウン） */
define('NAMELIST_FREE4', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE4_NAME', '自由設定4'); // 表示名
define('NAMELIST_FREE4_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '--------' => '',
  '自由設定4選択A' => '自由設定4選択A',
  '自由設定4選択B' => '自由設定4選択B',
  '自由設定4選択C' => '自由設定4選択C',
]);
/* 自由設定5 （プルダウン） */
define('NAMELIST_FREE5', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE5_NAME', '自由設定5'); // 表示名
define('NAMELIST_FREE5_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '--------' => '',
  '自由設定5選択A' => '自由設定5選択A',
  '自由設定5選択B' => '自由設定5選択B',
  '自由設定5選択C' => '自由設定5選択C',
]);
/* 自由設定6 （プルダウン） */
define('NAMELIST_FREE6', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE6_NAME', '自由設定6'); // 表示名
define('NAMELIST_FREE6_LIST', [ // 保存ワード「'表示名' => '値',」形式。最大 20 文字。
  '--------' => '',
  '自由設定6選択A' => '自由設定6選択A',
  '自由設定6選択B' => '自由設定6選択B',
  '自由設定6選択C' => '自由設定6選択C',
]);
/* 自由設定7 （テキストボックス） */
define('NAMELIST_FREE7', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE7_NAME', '自由設定7'); // 表示名
/* 自由設定8 （テキストボックス） */
define('NAMELIST_FREE8', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE8_NAME', '自由設定8'); // 表示名
/* 自由設定9 （テキストボックス） */
define('NAMELIST_FREE9', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE9_NAME', '自由設定9'); // 表示名
/* 自由設定10 （テキストエリア） */
define('NAMELIST_FREE10', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE10_NAME', '自由設定10'); // 表示名
/* 自由設定11 （テキストエリア） */
define('NAMELIST_FREE11', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE11_NAME', '自由設定11'); // 表示名
/* 自由設定12 （テキストエリア） */
define('NAMELIST_FREE12', 0); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_FREE12_NAME', '自由設定12'); // 表示名
/* コメント （テキストボックス） */
define('NAMELIST_COMMENT', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_COMMENT_NAME', 'コメント'); // 表示名
/* URL （テキストボックス） */
define('NAMELIST_URL', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_URL_NAME', 'URL'); // 表示名
/*詳細 （テキストエリア）  */
define('NAMELIST_DETAIL', 1); // ONOFF（1:表示する/0:表示しない）
define('NAMELIST_DETAIL_NAME', '詳細'); // 表示名


/* システム設定
 *******************************************************************************
 * Warning!!
 * 以下はシステムで使用する項目です。
 * 編集しないでください。
 *******************************************************************************
 */
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('INDEX_ROOT', DOCUMENT_ROOT.SITE_ROOT);
define('NAMELIST_VIEW_ROOT', SITE_ROOT.'/characters/src/view.php');
/* 以下の変数を追加した場合は database.php の DB作成 に処理を追加すること。
 */
define('USERS_DB', (INDEX_ROOT.'/users/src/db/users.db'));
define('INFOMATIONS_DB', (INDEX_ROOT.'/infomation/src/db/informations.db'));
define('CHARACTERS_DB', (INDEX_ROOT.'/characters/src/db/characters.db'));
define('ROOMS_DB', (INDEX_ROOT.'/chatrooms/src/db/rooms.db'));
define('ROOM_INOUT_HISTORIES_DB', (INDEX_ROOT.'/chatrooms/src/db/roominouthistories.db'));
define('CHAT_ROOMS_DB', './db/chatrooms.db');
define('CHAT_ENTRIES_DB', './db/chatentries.db');
define('CHAT_LOGS_DB', './db/chatlogs.db');
define('CHAT_SECRETS_DB', './db/chatchatsecrets.db');
define('INBOX_LETTERS_DB', (INDEX_ROOT.'/letters/src/db/inboxletters.db'));
define('OUTBOX_LETTERS_DB', (INDEX_ROOT.'/letters/src/db/outboxletters.db'));
define('ALL_LOG_OUTPUT_DIR', (INDEX_ROOT.'/logstorage/logs/'));
define('ALL_LOG_URL_LINK', (SITE_ROOT.'/logstorage/logs/'));
define('ALL_LOG_LISTS_DB', (INDEX_ROOT.'/logstorage/src/db/allloglists.db'));
