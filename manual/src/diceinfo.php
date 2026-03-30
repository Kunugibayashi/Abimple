<?php
require_once(__DIR__ . '/../../core/src/config.php');
require_once(__DIR__ . '/../../core/src/functions.php');
require_once(__DIR__ . '/../../core/src/session.php');
require_once(__DIR__ . '/../../core/src/database.php');
require_once(__DIR__ . '/../../core/src/administrator.php');

$statusArray = array();

for ($i = 1; $i <= 9; $i++) {
  $isDiceConst = "NAMELIST_FREE{$i}_ISDICE";
  $nameConst = "NAMELIST_FREE{$i}_NAME";

  // 定数が定義されており、かつ値が 1 (有効) であるかを確認
  if (defined($isDiceConst) && constant($isDiceConst) === 1) {
    $column = "free{$i}";
    $name = constant($nameConst);
    $inputName = '{' . $name . '}';

    $statusArray[] = $inputName;
  }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>Chatダイスについて</title>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="<?php echo h(SITE_LINK); ?>favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <!-- 共通CSS -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/base.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/<?php echo h(SITE_TEMPLATE); ?>.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>assets/css/user-edit.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- レスポンシブ用 -->
  <link rel="stylesheet" href="<?php echo h(SITE_LINK); ?>core/css/responsive.css?up=<?php echo h(SITE_UPDATE); ?>"/>
  <!-- script -->
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-3.6.0.min.js"></script>
  <script src="<?php echo h(SITE_LINK); ?>core/js/jquery-abmple.js?up=<?php echo h(SITE_UPDATE); ?>"></script>
</head>
<body>
<div class="content-wrap">
  <h3 class="diceinfo-title">Chatダイスについて</h3>

  <div class="note-wrap">
    <p class="note">
      入室後のチャット画面ではダイスを振ることが可能です。<br>
      名簿の登録データも計算式に含めることができます。管理者が使用許可している場合、以下にリストが表示されます。<br>
      表示されていない場合は使用できません。<br>
    </p>
  </div>

  <h4 class="diceinfo-title">制限事項</h3>
  <div class="table-wrap">
    <table>
      <tbody>
        <tr>
          <th class="cell-dicerule">制限</th>
          <th class="cell-diceinfo">内容</th>
          <th class="cell-diceexample">NG例</th>
        </tr>
        <tr>
          <td>コメント</td>
          <td>最初の半角スペース、または、全角スペース以降はコメントになります。</td>
          <td>2d6 + 3 >= 7</td>
        </tr>
        <tr>
          <td>使用可能文字</td>
          <td>数字、d、四則演算記号、括弧、比較記号のみ。</td>
          <td>1D6+10A>=12</td>
        </tr>
        <tr>
          <td>ダイス上限</td>
          <td>最大 10d100。</td>
          <td>11d100</td>
        </tr>
        <tr>
          <td>括弧の入れ子</td>
          <td>括弧の中に括弧を書くことはできません。</td>
          <td>((1+2)*3)</td>
        </tr>
        <tr>
          <td>変数名</td>
          <td>以下の「使用可能変数」で表示されている項目のみ {} で使用可能です。</td>
          <td>{未設定の項目}</td>
        </tr>
      </tbody>
    </table>
  </div>


  <h4 class="diceinfo-title">使用可能変数</h3>
  <div class="table-wrap">
    <table>
      <tbody>
        <?php if(!usedArr($statusArray)) { ?>
          <tr>
            <td>使用可能な変数はありません。</td>
          </tr>
        <?php } ?>
        <?php foreach ($statusArray as $key => $data) { ?>
          <tr>
            <td><?php echo h($data); ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <h4 class="diceinfo-title">利用例</h3>
  <div class="table-wrap">
    <table>
      <tbody>
        <tr>
          <th class="cell-diceinput">入力例</th>
          <th class="cell-dicexample">表示例</th>
        </tr>
        <tr>
          <td>10d100</td>
          <td>10d100 ＞ 501[16, 64, 10, 60, 29, 95, 59, 81, 54, 33] ＞ 501</td>
        </tr>
        <tr>
          <td>2d6>=7　【行動判定】</td>
          <td>2d6>=7 【行動判定】 ＞ 10[5, 5]>=7 ＞ 10>=7 ＞ 成功</td>
        </tr>
        <tr>
          <td>2d6+2>=7　【行動判定】＋補正値</td>
          <td>2d6+2>=7 【行動判定】＋補正値 ＞ 4[1, 3]+2>=7 ＞ 6>=7 ＞ 失敗</td>
        </tr>
        <tr>
          <td>2d6+{自由設定9}>=7　【行動判定】＋変数</td>
          <td>2d6+{自由設定9}>=7 【行動判定】＋変数 ＞ 10[6, 4]+(3)>=7 ＞ 13>=7 ＞ 成功</td>
        </tr>
        <tr>
          <td>2d6>=2d6　左右比較</td>
          <td>2d6>=2d6 左右比較 ＞ 7[4, 3]>=3[2, 1] ＞ 7>=3 ＞ 成功</td>
        </tr>
        <tr>
          <td>2d6>={自由設定9}*3</td>
          <td>2d6>={自由設定9}*3 ＞ 12[6, 6]>=(3)*3 ＞ 12>=9 ＞ 成功</td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
