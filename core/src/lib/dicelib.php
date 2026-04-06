<?php
require_once(__DIR__ . '/../logger.php');

/**
 * 最初の「空白（半角・全角）」で最大2つに分割
 */
function parseInput(string $text): array {
  // uフラグは全角スペース（マルチバイト）を正しく扱うため
  $parts = preg_split('/[\s　]+/u', trim($text), 2);

  $command = $parts[0];
  $comment = isset($parts[1]) ? $parts[1] : "";

  return [
    'command' => $command,
    'comment' => $comment,
  ];
}

/**
 * () が正しく閉じているかのチェック。
 */
function checkParentheses(string $text): bool {
  $inBrace = false;

  // 文字列を1文字ずつ配列としてループ
  $chars = mb_str_split($text, 1, 'UTF-8');

  foreach ($chars as $char) {
    if ($char === '(') {
      // すでに括弧が開いている場合は二重括弧
      if ($inBrace) {
        return false;
      }
      $inBrace = true;
    } elseif ($char === ')') {
      // 開いていないのに閉じ括弧が来た場合は不正
      if (!$inBrace) {
        return false;
      }
      $inBrace = false;
    }
  }

  // ループ終了時に「まだ括弧が開いたまま」なら不正
  if ($inBrace) {
    return false;
  }
  return true;
}

/**
 * {} が正しく閉じているかのチェック。
 */
function checkBraces(string $text): bool {
  $inBrace = false;

  // 文字列を1文字ずつ配列としてループ
  $chars = mb_str_split($text, 1, 'UTF-8');

  foreach ($chars as $char) {
    if ($char === '{') {
      // すでに括弧が開いている場合は二重括弧
      if ($inBrace) {
        return false;
      }
      $inBrace = true;
    } elseif ($char === '}') {
      // 開いていないのに閉じ括弧が来た場合は不正
      if (!$inBrace) {
        return false;
      }
      $inBrace = false;
    }
  }

  // ループ終了時に「まだ括弧が開いたまま」なら不正
  if ($inBrace) {
    return false;
  }
  return true;
}

/**
 * 文字列からダイス表記（XdX）をすべて抽出する
 */
function extractDiceNotations(string $text): array {
    // 桁数を問わず数字の連続をキャッチする
    $pattern = '/\d+d\d+/i';
    if (preg_match_all($pattern, $text, $matches)) {
        return $matches[0];
    }
    return [];
}

/**
 * 抽出されたダイスリストから、上限（10d100）を超えたダイス表記のみを取得する
 */
function getInvalidDice(array $diceList): array {
  $invalidList = [];
  $maxCount = 10;
  $maxSides = 100;

  foreach ($diceList as $dice) {
    // 'd' で分割して、個数と面数を取り出す
    $parts = explode('d', $dice);

    $count = (int)$parts[0];
    $sides = (int)$parts[1];

    // 個数が上限超え、または面数が上限超え、または0以下の場合
    if ($count > $maxCount || $sides > $maxSides || $count <= 0 || $sides <= 0) {
      $invalidList[] = $dice;
    }
  }

  return $invalidList;
}

/**
 * XdXX 形式を振り、指定された形式で結果を返す
 */
function rollDice(string $diceNotation): array {
  $parts = explode('d', $diceNotation);
  $count = (int)$parts[0];
  $sides = (int)$parts[1];

  $rolls = [];
  $total = 0;

  for ($i = 0; $i < $count; $i++) {
    // 1から面数までの乱数を生成
    $result = random_int(1, $sides);
    $rolls[] = $result;
    $total += $result;
  }

  return [
    'notation' => $diceNotation,
    'total' => $total,
    'rolls' => $rolls, // 出目の内訳 [10, 55, 2, ...]
    'display' => $total .'[' .(implode(', ', $rolls)) .']', // 合計[出目の内訳]
  ];
}

/**
 * {} 形式の単語を括弧付きのリストとして取得
 */
function getBracedTexts(string $text): array {
  $result = [];

  // 正規表現パターン: { と } を含めて全体を抽出
  $pattern = '/\{[^{}]+\}/';

  // 一致するものをすべて検索
  if (preg_match_all($pattern, $text, $matches)) {
    $result = $matches[0];
  }

  return $result;
}

/**
 * 四則演算（+ - * /）を計算する
 */
function calculateWithoutEval(string $expression): float {
  // スペース除去と数値・演算子の切り出し
  // 正規表現で「数字（小数含む）」と「演算子」を配列に分ける
  $expression = str_replace(' ', '', $expression);
  preg_match_all('/[0-9.]+|[\+\-\*\/]/', $expression, $matches);
  $tokens = $matches[0];

  if (empty($tokens)) return 0;

  // 掛け算・割り算を処理する（優先順位のため）
  $stage1 = [];
  for ($i = 0; $i < count($tokens); $i++) {
    $token = $tokens[$i];
    if ($token === '*' || $token === '/') {
      $prev = array_pop($stage1);
      $next = $tokens[++$i];
      $res = ($token === '*') ? ($prev * $next) : ($prev / $next);
      $stage1[] = $res;
    } else {
      $stage1[] = $token;
    }
  }

  // 足し算・引き算を処理する
  $result = (float)array_shift($stage1);
  while (!empty($stage1)) {
    $op = array_shift($stage1);
    $next = (float)array_shift($stage1);
    if ($op === '+') $result += $next;
    if ($op === '-') $result -= $next;
  }

  return $result;
}

/**
 * 括弧を内を計算する
 */
function solveExpression(string $expr): float {
  // 括弧の一番深いところから順に calculateWithoutEval に投げる
  while (preg_match('/\(([^()]+)\)/', $expr, $m)) {
    $subRes = calculateWithoutEval($m[1]);
    $expr = str_replace($m[0], $subRes, $expr);
  }

  return calculateWithoutEval($expr);
}

/**
 * 文字列を比較演算子で分割して配列を返す
 */
function splitByOperator(string $text): array {
  $result = [
    'left'     => '0',
    'operator' => '',
    'right'    => '0'
  ];

  $pattern = '/(>=|<=|>|<|=)/';

  if (preg_match($pattern, $text, $matches)) {
    $operator = $matches[1];
    $result['operator'] = $operator;

    // 演算子で分割
    $parts = explode($operator, $text);

    $left = trim($parts[0] ?? '');
    $result['left'] = ($left !== '') ? $left : '0';

    $right = trim($parts[1] ?? '');
    $result['right'] = ($right !== '') ? $right : '0';
  } else {
    // 演算子が含まれていない場合、文字列全体を左辺として扱う
    $left = trim($text);
    $result['left'] = ($left !== '') ? $left : '0';
  }

  return $result;
}

/**
 * 式（>=, <=, >, <, =）を評価する
 */
function evaluateCommand(string $command): bool {
  $pattern = '/(>=|<=|>|<|=)/';
  if (!preg_match($pattern, $command, $matches)) {
    return (bool)solveExpression($command);
  }

  $operator = $matches[1];
  $sides = explode($operator, $command);

  // 左辺と右辺を eval なしで計算
  $leftVal  = solveExpression($sides[0]);
  $rightVal = solveExpression($sides[1]);

  // PHPの比較演算で判定
  switch ($operator) {
    case '>=': return $leftVal >= $rightVal;
    case '<=': return $leftVal <= $rightVal;
    case '>':  return $leftVal >  $rightVal;
    case '<':  return $leftVal <  $rightVal;
    case '=':  return $leftVal == $rightVal;
    default:   return false;
  }
}

/**
 * 文字列の中に比較演算子（>=, <=, >, <, =）が含まれているかチェックする
 */
function hasComparisonOperator(string $text): bool {
    // 2文字の演算子を先に、1文字を後に配置してマッチング
    $pattern = '/>=|<=|>|<|=/';
    $result = preg_match($pattern, $text);
    return (bool)$result;
}

/**
 * 文字列に使用可能な文字以外が含まれていないかチェックする
 * 許可する文字のリスト（正規表現）
 * 0-9 : 数字
 * \+ \- \* \/ : 四則演算
 * \( \) : 括弧
 * > < = : 比較演算
 * d : ダイス表記用の 'd'
 */
function hasInvalidCharacters(string $text): bool {
  // 空文字は不正とする
  if ($text === '') {
    return true;
  }
  $pattern = '/[^0-9d\+\-\*\/\(\)><=]/iu';
  if (preg_match($pattern, $text)) {
    return true;
  }
  return false;
}
