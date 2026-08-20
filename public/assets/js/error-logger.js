/**
 * Captures uncaught JS errors and unhandled promise rejections and reports
 * them to /log/js-error, so they show up in the admin "Error Log" page
 * alongside PHP-side errors instead of only living in the browser console.
 */
(function () {
    if (window.__kayaCmsErrorLoggerInstalled) return;
    window.__kayaCmsErrorLoggerInstalled = true;

    var ENDPOINT = '/log/js-error';
    var seen = new Set();
    var MAX_REPORTS_PER_LOAD = 25;
    var sent = 0;

    function isExtensionSource(source) {
        return typeof source === 'string' &&
            /^(chrome|moz|safari-web)-extension:\/\//.test(source);
    }

    function report(payload) {
        if (sent >= MAX_REPORTS_PER_LOAD) return;

        var key = payload.level + '|' + payload.message + '|' + payload.fileSource + '|' + payload.line;
        if (seen.has(key)) return;
        seen.add(key);
        sent++;

        payload.url = window.location.href;

        try {
            var body = JSON.stringify(payload);
            if (navigator.sendBeacon) {
                var blob = new Blob([body], { type: 'application/json' });
                navigator.sendBeacon(ENDPOINT, blob);
            } else {
                fetch(ENDPOINT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: body,
                    keepalive: true
                }).catch(function () {});
            }
        } catch (e) {
            // Reporting must never itself throw.
        }
    }

    window.addEventListener('error', function (event) {
        if (isExtensionSource(event.filename)) return;

        report({
            level: 'error',
            message: event.message || 'Unknown script error',
            fileSource: event.filename || '',
            line: event.lineno || 0,
            column: event.colno || 0,
            stack: event.error && event.error.stack ? String(event.error.stack) : ''
        });
    });

    window.addEventListener('unhandledrejection', function (event) {
        var reason = event.reason;
        var message = reason && reason.message ? reason.message : String(reason);
        var stack = reason && reason.stack ? String(reason.stack) : '';

        report({
            level: 'unhandledrejection',
            message: message || 'Unhandled promise rejection',
            fileSource: '',
            line: 0,
            column: 0,
            stack: stack
        });
    });
})();
