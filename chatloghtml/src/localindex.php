<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

ini_set('memory_limit', PHP_MEMORY_LIMIT);

// DB接続
$dbhChatLogFiles = connectRo(CHAT_LOG_FILES_DB);
$logLists = selectEqualChatlogfiles($dbhChatLogFiles);

goto outputPage;

/* goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>ログ一覧</title>
<style>
/*!
 * ress.css • v4.0.0
 * MIT License
 * github.com/filipelinhares/ress
 *
 * appearance の定義を追加。
 */
html{box-sizing:border-box;-webkit-text-size-adjust:100%;word-break:normal;-moz-tab-size:4;tab-size:4}*,:after,:before{background-repeat:no-repeat;box-sizing:inherit}:after,:before{text-decoration:inherit;vertical-align:inherit}*{padding:0;margin:0}hr{overflow:visible;height:0;color:inherit}details,main{display:block}summary{display:list-item}small{font-size:80%}[hidden]{display:none}abbr[title]{border-bottom:none;text-decoration:underline;text-decoration:underline dotted}a{background-color:transparent}a:active,a:hover{outline-width:0}code,kbd,pre,samp{font-family:monospace,monospace}pre{font-size:1em}b,strong{font-weight:bolder}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{border-color:inherit;text-indent:0}input{border-radius:0}[disabled]{cursor:default}[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}[type=search]{appearance:textfield;-webkit-appearance:textfield;outline-offset:-2px}[type=search]::-webkit-search-decoration{-webkit-appearance:none}textarea{overflow:auto;resize:vertical}button,input,optgroup,select,textarea{font:inherit}optgroup{font-weight:700}button{overflow:visible}button,select{text-transform:none}[role=button],[type=button],[type=reset],[type=submit],button{cursor:pointer;color:inherit}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button::-moz-focus-inner{border-style:none;padding:0}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button:-moz-focusring{outline:1px dotted ButtonText}[type=reset],[type=submit],button,html [type=button]{appearance:button;-webkit-appearance:button}button,input,select,textarea{background-color:transparent;border-style:none}a:focus,button:focus,input:focus,select:focus,textarea:focus{outline-width:0}select{appearance:none;-moz-appearance:none;-webkit-appearance:none}select::-ms-expand{display:none}select::-ms-value{color:currentColor}legend{border:0;color:inherit;display:table;white-space:normal;max-width:100%}::-webkit-file-upload-button{-webkit-appearance:button;color:inherit;font:inherit}img{border-style:none}progress{vertical-align:baseline}[aria-busy=true]{cursor:progress}[aria-controls]{cursor:pointer}[aria-disabled=true]{cursor:default}

h3 {
  background-color: #696969;
  color: #ffffff;
}
th {
  background-color: #c0c0c0;
  color: #000000;
}
tr:nth-child(2n+1) {
  background-color: #f5f5f5;
}

body {
  margin: 0;
  padding: 0;
}
h3 {
  font-size: 100%;
  padding: 0.5rem;
  margin: 0;
}
table, th, td {
  border-collapse: collapse;
  margin: 1rem 0;
}
table {
  width: 100%;
  table-layout: fixed;
  display: table;
  box-sizing: border-box;
  text-indent: initial;
  unicode-bidi: isolate;
  border-spacing: 2px;
  border-color: gray;
}
th {
    padding: 0.5rem;
    font-weight: bold;
    text-align: left;
}
tr {
  display: table-row;
  vertical-align: inherit;
  unicode-bidi: isolate;
  border-color: inherit;
}
td {
  padding: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
ul.entries-item-group {
  display: flex;
}
ul, li {
  list-style-type: none;
  word-break: break-all;
}
li.entries-item,
li.entries-no-item {
  margin-right: 0.5rem;
  border-radius: 0.2rem;
  padding: 0.2rem;
}
.scroll {
  overflow-x: auto;
}
.content-wrap {
  margin: 0;
  padding: 2rem;
}
.cell-created {
  min-width: 12rem;
  width: 12rem;
}
.cell-roomtitle {
  min-width: 20rem;
  width: 20rem;
}
.cell-logentries {
  min-width: 10rem;
}
</style>
</head>
<body>
  <div class="content-wrap">
    <h3 class="frame-title">ログ一覧</h3>
    <div class="table-wrap chatloghtml-table-wrap">
      <table>
        <tr>
          <th class="cell-created">作成日</th>
          <th class="cell-roomtitle">ルーム</th>
          <th class="cell-logentries">参加者</th>
        </tr>
        <?php foreach ($logLists as $key => $value) { ?>
          <tr>
            <td><?php echo h($value['created']); ?></td>
            <?php
              $filePath = (CHAT_LOG_HTML_PATH .$value['filename']);
              $filePathLink = ($value['filename']);
            ?>
            <?php if (file_exists($filePath)) { ?>
              <td><a href="<?php echo h($filePathLink); ?>"><?php echo h($value['roomtitle']); ?></a></td>
              <td class="scroll"><?php echo ht($value['entries']); ?></td>
            <?php } else { ?>
              <td><?php echo h($value['roomtitle']); ?></td>
              <td class="scroll"><?php echo ht($value['entries']); ?></td>
            <?php } ?>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</body>
</html>
