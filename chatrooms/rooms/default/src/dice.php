<?php
/* ログに発言を出力する。
 * jQuery による POSTリクエストからのアクセスを想定。
 */
require_once(__DIR__ .'/../../../../core/src/config.php');
require_once(__DIR__ .'/../../../../core/src/functions.php');
require_once(__DIR__ .'/../../../../core/src/session.php');
require_once(__DIR__ .'/../../../../core/src/database.php');
require_once(__DIR__ .'/../../../../core/src/administrator.php');
require_once(__DIR__ .'/../../../../core/src/logger.php');

require_once(__DIR__ .'/../../../../core/src/lib/dicelib.php');

$jsonArray = array();
$inputParams = array();
$parsedArray = array();

$inputParams['characterid'] = inputParam('characterid', 20);
$inputParams['dice'] = inputParam('dice', 200);

logDebug('inputParams = ' .json_encode($inputParams, JSON_UNESCAPED_UNICODE));

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';

$messageString = '';
$outputCmment = '';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // GETは処理しない。
  exit;
}
/* 以降はPOST通信を想定。
 */
// CSRF対策
checkChatToken($inputParams['characterid']);

// DB接続
$dbhCharacters = connectRo(CHARACTERS_DB);
$dbhChatrooms  = connectRo(__DIR__ .'/' .CHAT_ROOMS_DB);
$dbhChatentries = connectRw(__DIR__ .'/' .CHAT_ENTRIES_DB);
$dbhChatlogs = connectRw(__DIR__ .'/' .CHAT_LOGS_DB);

$chatrooms = selectChatroomsConfig($dbhChatrooms);
$chatroom = $chatrooms[0]; // 必ずある想定

$characters = selectCharactersId($dbhCharacters, $inputParams['characterid']);
if (!usedArr($characters)) {
  // 不正アクセス
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '名簿が存在しません。';
  goto outputPage;
}
$character = $characters[0];

// 本人確認
identityUser($character['userid'], $character['username']);

$myChatentries = selectEqualChatentries($dbhChatentries, [
  'characterid' => $character['id'],
]);
if (!usedArr($myChatentries)) {
  // 不正アクセス
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '入室していません。';
  goto outputPage;
}
$myChatentry = $myChatentries[0];

// ステータス用パラメータリスト作成
$statusArray = createStatusArray($character);
$inputNames = array_column($statusArray, 'inputName');

logDebug('statusArray = ' .json_encode($statusArray, JSON_UNESCAPED_UNICODE));

// コメントとコマンドに分割
['command' => $inputCommand, 'comment' => $inputComment] = parseInput($inputParams['dice']);
$parsedArray['command'] = $inputCommand;
$parsedArray['comment'] = $inputComment;


// ダイス空白チェック
if (!usedStr($inputCommand)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ダイス目が入力されていません。';
  goto outputPage;
}

// () 閉じチェック
if (!checkParentheses($inputCommand)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '() が正しく閉じていません。';
  goto outputPage;
}

// {} 閉じチェック
if (!checkBraces($inputCommand)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '{} が正しく閉じていません。';
  goto outputPage;
}

// ダイス表記の取り出し
$diceNotations = extractDiceNotations($inputCommand);

logDebug('diceNotations = ' .json_encode($diceNotations, JSON_UNESCAPED_UNICODE));

// ダイス表記が範囲内かの確認（10d100）
$invalidDice = getInvalidDice($diceNotations);
if (!empty($invalidDice)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = implode(', ', $invalidDice) .'は利用可能ダイス範囲外です。ダイス値の最大は 10d100 です。';
  goto outputPage;
}

// {} 単語の取り出し
$bracedTexts = getBracedTexts($inputCommand);

logDebug('bracedTexts = ' .json_encode($bracedTexts, JSON_UNESCAPED_UNICODE));

// 使用可能な変数か確認
$unknowns = array_diff($bracedTexts, $inputNames);
if (!empty($unknowns)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = implode(', ', $unknowns) .'は使用できません。';
  goto outputPage;
}

// 計算過程
$processString = $inputCommand;

// 評価する式
$expressionString = $inputCommand;

// ダイス表記の抽出用パターン
$pattern = '/\d+d\d+/i';

// ダイスを振る
$rollDices = array(); // 振った結果を記録する配列
$processString = preg_replace_callback($pattern, function($matches) use (&$rollDices) {
  $notation = $matches[0];
  $rollResult = rollDice($notation);

  $rollDices[] = $rollResult;

  // その場で見つかったダイスを「合計[出目]」の形式で返す
  return $rollResult['display'];
}, $inputCommand);

// 評価用の式を作成（1d6+1d6 -> 3+5 のように数値のみに展開）
$tempRolls = $rollDices;
$expressionString = preg_replace_callback($pattern, function($matches) use (&$tempRolls) {
  // 保存しておいた結果を順番に取り出して数値だけを返す
  $rollResult = array_shift($tempRolls);
  return $rollResult['total'];
}, $inputCommand);

// {} 単語リストを具体的な値に変換
$replaceTable = [];
foreach ($statusArray as $key => $data) {
    $replaceTable[$key] = $data['status'];
}
$processString = strtr($processString, $replaceTable);
$parsedArray['process'] = $processString;

$expressionString = strtr($expressionString, $replaceTable);
$parsedArray['expression'] = $expressionString;

// 計算式に余分な値が含まれているか
if (hasInvalidCharacters($expressionString)) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = '計算式が正しくありません。使用不可な文字が含まれています。';
  goto outputPage;
}

// 評価する式に比較演算子が含まれているか
$isComparison = hasComparisonOperator($expressionString);
$parsedArray['isComparison'] = $isComparison;

// コメントがある場合は整形
if (isset($parsedArray['comment']) && $parsedArray['comment'] != '') {
  $outputCmment = ' ' .$parsedArray['comment'];
}

if ($isComparison) {
  // 最終式を右辺左辺に分ける
  $comparisonParts = splitByOperator($parsedArray['expression']);

  // 右辺左辺を独立して計算
  $leftSum = solveExpression($comparisonParts['left']);
  $rightSum = solveExpression($comparisonParts['right']);
  $scoreString = $leftSum .$comparisonParts['operator'] .$rightSum;
  $parsedArray['scoreString'] = $scoreString;

  // 比較結果を取得
  $result = evaluateCommand($expressionString);
  $parsedArray['result'] = (bool)$result;
  $parsedArray['resultString'] = $result ? '<span class="dice-success">成功</span>' : '<span class="dice-fail">失敗</span>';

  // メッセージ作成
  $messageString = (
    '<span class="fullname">'
      .'<span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span>'
      .'</span>'
      .'<span class="dice">'  .' ' .$parsedArray['command'] .$outputCmment
      .'<span class="dice-arrow">＞</span>' .$parsedArray['process']
      .'<span class="dice-arrow">＞</span>' .$parsedArray['scoreString']
      .'<span class="dice-arrow">＞</span>' .$parsedArray['resultString']
    .'</span>'
  );
} else {
  // 値の計算結果を取得
  $result = solveExpression($expressionString);
  $parsedArray['total'] = $result;

  // メッセージ作成
  $messageString = (
    '<span class="fullname">'
      .'<span style=" color:' .$myChatentry['color'] .';">' .$character['fullname'] .'</span>'
      .'</span>'
      .'<span class="dice">' .' ' .$parsedArray['command'] .$outputCmment
      .'<span class="dice-arrow">＞</span>' .$parsedArray['process']
      .'<span class="dice-arrow">＞</span>' .$parsedArray['total']
    .'</span>'
  );
}

// 発言
$result = insertChatlogs($dbhChatlogs, getUserid(), getUsername(), [
  'logtype' => LOGTYPE_DICE,
  'entrykey' => $myChatentry['entrykey'],
  'characterid' => $character['id'],
  'fullname' => CHAT_LOG_SYSTEM_NAME,
  'color' => $chatroom['color'],
  'bgcolor' => $chatroom['bgcolor'],
  'message' => $messageString,
]);
if (!$result) {
  $jsonArray['code'] = 1;
  $jsonArray['errorMessage'] = 'ダイスに失敗しました。もう一度お試しください。';
  goto outputPage;
}

$jsonArray['code'] = 0;
$jsonArray['errorMessage'] = '';


/**
 * goto文はコードが煩雑になるため使用するべきではないが、
 * ソースコードが複雑になるため、画面表示phpのページ出力開始ラベルのみ使用する。
 */
outputPage:

logDebug('messageString = ' .$messageString);
logDebug('parsedArray = ' .json_encode($parsedArray, JSON_UNESCAPED_UNICODE));

$json =  json_encode($jsonArray);
setJsonHeader();
echo($json);

exit;



/**
 * define、DBカラム名、DB値の紐づけをする。
 */
function createStatusArray(array $character): array {
  $statusArray = array();

  for ($i = 1; $i <= 9; $i++) {
    $isDiceConst = "NAMELIST_FREE{$i}_ISDICE";
    $nameConst = "NAMELIST_FREE{$i}_NAME";

    // 定数が定義されており、かつ値が 1 (有効) であるかを確認
    if (defined($isDiceConst) && constant($isDiceConst) === 1) {
      $column = "free{$i}";
      $name = constant($nameConst);
      $inputName = '{' . $name . '}';

      $statusArray[$inputName] = [
        'column'    => $column,
        'name'      => $name,
        'inputName' => $inputName,
        'status'    => (int)($character[$column] ?? 0),
      ];
    }
  }

  return $statusArray;
}
