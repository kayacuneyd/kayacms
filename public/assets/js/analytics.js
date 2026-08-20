(function () {
  if (!document.body || !document.body.dataset.contentSlug) return;

  var startedAt = Date.now();
  var sent = false;

  function send() {
    if (sent) return;
    sent = true;

    var payload = JSON.stringify({
      slug: document.body.dataset.contentSlug,
      url: window.location.href,
      referrer: document.referrer || '',
      read_seconds: Math.round((Date.now() - startedAt) / 1000)
    });

    if (navigator.sendBeacon) {
      navigator.sendBeacon('/analytics/collect', new Blob([payload], { type: 'application/json' }));
      return;
    }

    fetch('/analytics/collect', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: payload,
      keepalive: true
    }).catch(function () {});
  }

  window.addEventListener('pagehide', send);
  setTimeout(send, 15000);
})();
