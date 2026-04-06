(function(global){
  // タイマー保持
  var heartbeatTimerId = null;

  function sendHeartbeat() {
    jQuery.ajax({
      url: CHAT_ONLINE_COUNT_API,
      type: 'POST',
      dataType: 'json',
      data: {},
      cache: false
    }).done(function(data) {
      if (!data) return;
      if (data.code !== 0) return;

      $('#id-onlinecount').text(data.onlinecount || 0);
    }).fail(function(jqXHR, textStatus, errorThrown) {
      console.log(jqXHR);
      console.log(textStatus);
      console.log(errorThrown);
      // 通信失敗時は0にするか現状維持か選べる
      $('#id-onlinecount').text('―');
    });
  }

  function startHeartbeatTimer(beatsec) {
    beatsec = beatsec || 30000;
    if (typeof CHAT_ONLINE_COUNT_API === 'undefined') return;

    if (heartbeatTimerId !== null) {
      clearInterval(heartbeatTimerId);
    }
    sendHeartbeat();
    heartbeatTimerId = setInterval(sendHeartbeat, beatsec);
  }

  // 外部公開
  global.startHeartbeatTimer = startHeartbeatTimer;
})(window);

