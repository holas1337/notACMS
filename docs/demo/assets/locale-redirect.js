(function () {
    'use strict';
    var COOKIE = 'lang';
    var html = document.documentElement;
    var locales = (html.dataset.locales || '').split(',').filter(Boolean);
    var defaultLocale = html.dataset.defaultLocale || locales[0] || 'en';

    // Detect known bots/crawlers to avoid redirect loops in SEO tools
    // Uses a conservative list of legitimate search engine crawlers
    function isBot() {
        var ua = (navigator.userAgent || '').toLowerCase();
        var botPatterns = [
            /googlebot/,
            /bingbot/,
            /slurp/,
            /duckduckbot/,
            /baiduspider/,
            /yandexbot/,
            /facebookexternalhit/,
            /linkedinbot/,
            /twitterbot/,
            /applebot/,
            /semrushbot/,
            /ahrefsbot/,
            /mj12bot/,
            /dotbot/,
            /aspiegelbot/,
            /petalbot/,
            /screaming frog/
        ];
        for (var i = 0; i < botPatterns.length; i++) {
            if (botPatterns[i].test(ua)) {
                return true;
            }
        }
        return false;
    }

    // Skip all redirect logic for bots
    if (isBot()) {
        return;
    }

    function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));

        return m ? decodeURIComponent(m[1]) : null;
    }

    function setCookie(value) {
        // Session cookie — no Max-Age/Expires, cleared when browser closes
        var secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = COOKIE + '=' + encodeURIComponent(value) + '; path=/; SameSite=Lax' + secure;
    }

    function getAlternateUrl(targetLocale) {
        // Try hreflang <link> elements already parsed in <head>
        var link = document.querySelector('link[rel="alternate"][hreflang="' + targetLocale + '"]');
        if (link) {
            try {
                return new URL(link.getAttribute('href')).pathname;
            } catch (e) {}
        }

        return targetLocale === defaultLocale ? '/' : '/' + targetLocale + '/';
    }

    var currentLocale = html.lang || defaultLocale;
    var saved = getCookie(COOKIE);

    if (saved && locales.length && locales.indexOf(saved) === -1) {
        setCookie(defaultLocale);
        saved = null;
    }

    if (!saved) {
        if (currentLocale !== defaultLocale) {
            // User is already on a non-default locale URL — treat as explicit choice.
            setCookie(currentLocale);
        } else {
            var browserLang = (navigator.language || navigator.userLanguage || '').toLowerCase();
            var preferred = defaultLocale;
            for (var i = 0; i < locales.length; i++) {
                if (browserLang.startsWith(locales[i])) {
                    preferred = locales[i];
                    break;
                }
            }
            setCookie(preferred);
            if (preferred !== currentLocale) {
                window.location.replace(getAlternateUrl(preferred));
            }
        }
    } else if (saved !== currentLocale) {
        window.location.replace(getAlternateUrl(saved));
    }

    // Save preference when user manually switches language
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a.lang-switch').forEach(function (link) {
            link.addEventListener('click', function () {
                var targetLang = this.getAttribute('hreflang');
                if (targetLang && locales.indexOf(targetLang) !== -1) {
                    setCookie(targetLang);
                }
            });
        });
    });
}());
