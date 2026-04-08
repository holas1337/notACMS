(function () {
    'use strict';

    var COOKIE_NAME = 'cookie_consent';
    var banner = document.getElementById('cookie-banner');
    var btnAccept = document.getElementById('cookie-accept');
    var btnDismiss = document.getElementById('cookie-dismiss');

    if (!banner || !btnAccept) return;

    function getCookie(name) {
        var pairs = document.cookie.split(';');
        for (var i = 0; i < pairs.length; i++) {
            var pair = pairs[i].trim().split('=');
            if (pair[0] === name) return decodeURIComponent(pair[1] || '');
        }
        return '';
    }

    function setCookie(name, value, days) {
        var expires = '';
        if (days) {
            var d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            expires = '; expires=' + d.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax; Secure';
    }

    if (getCookie(COOKIE_NAME) === '1') {
        banner.classList.add('is-hidden');
        return;
    }

    banner.classList.remove('is-hidden');

    btnAccept.addEventListener('click', function () {
        setCookie(COOKIE_NAME, '1', 365);
        banner.classList.add('is-hidden');
    });

    if (btnDismiss) {
        btnDismiss.addEventListener('click', function () {
            banner.classList.add('is-hidden');
        });
    }
}());
