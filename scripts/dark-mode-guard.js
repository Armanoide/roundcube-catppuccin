/**
 * Guards against dark-mode class creeping back onto <html>
 * for light Catppuccin flavors (Latte).
 *
 * Also syncs Roundcube's built-in dark/light toggle button
 * so it reflects the flattened flavour state.
 */
(function () {
    var html = document.documentElement;

    function removeDarkMode() {
        html.classList.remove('dark-mode');

        var btn = document.querySelector('#taskmenu a.theme');
        if (btn) {
            btn.classList.remove('dark');
            btn.classList.add('light');

            var span = btn.querySelector('span');
            if (span) {
                var txt = span.textContent || span.innerText || '';
                span.textContent = txt.replace(/Dark|Light/gi, 'Light');
            }
        }
    }

    // Run immediately in case <head> script has already added it
    removeDarkMode();

    // Standard events
    document.addEventListener('DOMContentLoaded', removeDarkMode, false);
    window.addEventListener('load', removeDarkMode, false);

    // Watch for class changes so any late-injected code is caught
    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'class') {
                    removeDarkMode();
                    break;
                }
            }
        });
        observer.observe(html, { attributes: true, attributeOldValue: false });
    }
})();
