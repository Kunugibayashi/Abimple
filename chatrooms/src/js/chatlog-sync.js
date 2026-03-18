(function(global){
  // タイマー保持
  var chatTimerId = null;

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

  // 参加者を更新
  function applychatentry(string) {
    var elm = jQuery('#id-chat-entries');
    if (elm.length) elm.html(string);
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

  // サーバから差分ログを取得して update → append を適用
  function chatReload() {
    var domminid = parseInt(jQuery('#id-domminid').val(), 10);
    var dommaxid = parseInt(jQuery('#id-dommaxid').val(), 10);
    var syncmodifiedts = jQuery('#id-syncmodifiedts').val();
    var lognum = parseInt(jQuery('#id-lognum').val(), 10);
    var logsec = parseInt(jQuery('#id-logsec').val(), 10);
    var usebell = parseInt(jQuery('#id-usebell').val(), 10);

    // API URL確認用。確認時はコメントを外すこと。
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

      var chatentry = data.chatentry || '';
      var updatelist = data.updatelog || [];
      var appendlist = data.appendlog || [];
      var syncmodifiedts = data.syncmodifiedts || 0;
      var isringbell = data.isringbell || 0;

      applychatentry(chatentry);
      applyupdate(updatelist);
      applyappend(appendlist, lognum);
      ringBell(isringbell, usebell);

      if (syncmodifiedts != 0) {
        jQuery('#id-syncmodifiedts').val(String(syncmodifiedts));
      }

      syncHiddenIdsFromDom();
    });
  }

  // チャット更新タイマーを開始（既存タイマーは停止）
  function startChatTimer() {
    var sec = parseInt(jQuery('#id-logsec').val(), 10);

    if (chatTimerId !== null) {
      clearInterval(chatTimerId);
      chatTimerId = null;
    }
    if (sec === 0) return;

    chatTimerId = setInterval(function() {
      chatReload();
    }, sec);
  }

  // ベルを鳴らす
  function ringBell(isringbell, usebell) {
    if (!usebell) return;
    if (!isringbell) return;
    if (typeof bellAudio === 'undefined') return;
    if (!bellAudio) return;

    bellAudio.pause();
    bellAudio.currentTime = 0;

    bellAudio.play().catch(function(e) {
      console.log('audio play blocked', e);
    });
  }

  jQuery(function() {

    // ログ行数の変更時は全再取得
    jQuery('#id-lognum').on('change', function() {
      resetChatState();
      chatReload();
    });

    // リロード時間の変更時は全再取得
    jQuery('#id-logsec').on('change', function() {
      resetChatState();
      chatReload();
      startChatTimer();
    });

  });

  // 外部公開
  global.syncHiddenIdsFromDom = syncHiddenIdsFromDom;
  global.chatReload = chatReload;
  global.startChatTimer = startChatTimer;

})(window);
