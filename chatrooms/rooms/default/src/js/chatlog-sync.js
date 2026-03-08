(function(global){

  // id-chatlog-123 → 123 の数値IDを取り出す
  function parseChatlogId(idstr) {
    if (typeof idstr !== 'string') return NaN;
    if (idstr.indexOf('id-chatlog-') !== 0) return NaN;
    return parseInt(idstr.slice('id-chatlog-'.length), 10);
  }

  // DOM内ログから最小ID・最大IDを取得してhiddenに反映
  function syncHiddenIdsFromDom() {
    var logs = jQuery('#id-log-wrap').children('[id^="id-chatlog-"]');
    var minId = 0;
    var maxId = 0;

    logs.each(function() {
      var n = parseChatlogId(this.id);
      if (Number.isNaN(n)) return;
      if (minId === 0 || n < minId) minId = n;
      if (maxId === 0 || n > maxId) maxId = n;
    });

    jQuery('#id-domminid').val(minId);
    jQuery('#id-dommaxid').val(maxId);
  }

  // DOM状態をリセット
  function resetChatState() {
    var wrap = jQuery('#id-log-wrap');
    wrap.empty();

    jQuery('#id-domminid').val('0');
    jQuery('#id-dommaxid').val('0');
    jQuery('#id-syncmodifiedts').val('0');
  }

  // 既存ログを差分更新（HTML置換）
  function applyupdate(list) {
    if (!Array.isArray(list) || list.length === 0) return;

    list.forEach(function(item) {
      var elm = jQuery('#id-chatlog-' + item.id);
      if (elm.length) elm.html(item.loghtml);
    });
  }

  // 新規ログを末尾追加し、limit超過の古いログを削除
  function applyappend(list, lognum) {
    if (!Array.isArray(list) || list.length === 0) return;

    var container = jQuery('#id-log-wrap');
    if (container.length === 0) return;

    var frag = document.createDocumentFragment();

    list.forEach(function(item) {
      var div = document.createElement('div');
      div.id = 'id-chatlog-' + item.id;
      div.innerHTML = item.loghtml;
      frag.appendChild(div);
    });

    container[0].insertBefore(frag, container[0].firstChild);

    var logs = container.children('[id^="id-chatlog-"]');
    if (lognum > 0 && logs.length > lognum) {
      logs.slice(lognum).remove();
    }
  }

  // ログ行数、リロード時間の変更時は全再取得
  jQuery(function() {
    jQuery('#id-lognum, #id-logsec').on('change', function() {
      resetChatState();
      chatReload();
    });
  });

  // サーバから差分ログを取得して update → append を適用
  function chatReload() {
    var domminid = parseInt(jQuery('#id-domminid').val(), 10);
    var dommaxid = parseInt(jQuery('#id-dommaxid').val(), 10);
    var syncmodifiedts = jQuery('#id-syncmodifiedts').val();
    var lognum = parseInt(jQuery('#id-lognum').val(), 10);
    var logsec = parseInt(jQuery('#id-logsec').val(), 10);

    // DEBUG: API URL確認用。確認時はコメントを外すこと。
    // console.log('CHATLOG_API=', CHATLOG_API);
    // console.log('domminid=', domminid);
    // console.log('dommaxid=', dommaxid);
    // console.log('syncmodifiedts=', syncmodifiedts);

    if (Number.isNaN(domminid)) domminid = 0;
    if (Number.isNaN(dommaxid)) dommaxid = 0;
    if (Number.isNaN(lognum) || lognum <= 0) lognum = 100;
    if (Number.isNaN(logsec) || logsec < 0) logsec = 60000;

    // ログ上の表示を更新
    $('#id-info-lognum').text(lognum);
    $('#id-info-logsec').text(logsec / 1000);

    jQuery.ajax({
      url: CHATLOG_API,
      type: 'GET',
      dataType: 'json',
      data: {
        domminid: domminid,
        dommaxid: dommaxid,
        syncmodifiedts: syncmodifiedts,
        lognum: lognum
      }
    }).done(function(data) {
      if (!data || data.code !== 0) return;

      var updatelist = data.updatelog || [];
      var appendlist = data.appendlog || [];

      applyupdate(updatelist);
      applyappend(appendlist, lognum);

      if (data.syncmodifiedts != 0) {
        jQuery('#id-syncmodifiedts').val(String(data.syncmodifiedts));
      }

      syncHiddenIdsFromDom();
    });
  }

  // タイマー保持
  var chatTimer = null;

  // チャット更新タイマーを開始（既存タイマーは停止）
  function startChatTimer(logsec) {
    if (chatTimer !== null) clearInterval(chatTimer);
    chatTimer = setInterval(chatReload, logsec);
  }

  // 外部公開
  global.syncHiddenIdsFromDom = syncHiddenIdsFromDom;
  global.chatReload = chatReload;
  global.startChatTimer = startChatTimer;

})(window);
