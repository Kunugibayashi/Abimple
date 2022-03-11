jQuery(function(){
  // input Enter サブミット無効化
  jQuery('input').on('keydown', function(event){
    if(event.which === 13){
      return false;
    }
  });

  // 同じ階層のフォームをサブミット
  jQuery('.form-submit').on('click', function(){
    jQuery(this).parent().find('form').submit();
  });

  // メニュー全体の表示非表示切り替え
  jQuery('button.menu-button').on('click', function(){
    jQuery('div.index-menu').toggle('slow');
  });

  // メニュー表示非表示切り替え
  jQuery('h2.menu-title').on('click', function(){
    jQuery(this).next('ul').toggle('slow');
  });

  // HTMLタグの説明
  jQuery('div.htmltag-mark').on('mouseover', function(){
    var divStr = [
      '<div class="htmltag-tooltip-wrap">',
      '使用可能タグは以下の通りです。',
      '<ul class="htmltag-tooltip">',
      '<li>', 'ルビ用：', '&lt;ruby&gt;', 'Abimple', '&lt;rp&gt;', '（', '&lt;/rp&gt;', '&lt;rt&gt;', 'あびぷる', '&lt;/rt&gt;', '&lt;rp&gt;', '）', '&lt;/rp&gt;', '&lt;/ruby&gt;', '</li>',
      '<li>', '装飾用：', '&lt;span style=&quot;', '&quot;&gt;', '&lt;/span&gt;', '</li>',
      '<ul>',
      '</div>',
    ];
    var htmltag = jQuery(divStr.join(''));
    jQuery(this).append(htmltag);
  });
  jQuery('div.htmltag-mark').on('mouseout', function(){
    jQuery(this).find("div.htmltag-tooltip-wrap").remove();
  });

  // フレーム表示の場合は親全画面リロードでトップに戻る
  jQuery('button.sitetop-button').on('click', function(){
    parent.location.reload();
  });

  // 名前色
  jQuery('input.select-color').on('input', function(){
    var code = jQuery(this).val();
    jQuery(this).parent().find('input[name="color"]').val(code);
  });
  jQuery('input[name="color"]').on('change', function(){
    var code = jQuery(this).val();
    jQuery(this).parent().find('input.select-color').val(code);
  });

  // 背景色
  jQuery('input.select-bgcolor').on('input', function(){
    var code = jQuery(this).val();
    jQuery(this).parent().find('input[name="bgcolor"]').val(code);
  });
  jQuery('input[name="bgcolor"]').on('change', function(){
    var code = jQuery(this).val();
    jQuery(this).parent().find('input.select-bgcolor').val(code);
  });
});
