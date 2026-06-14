(function () {
    'use strict';

    if (typeof dbcmFrontend === 'undefined') {
        return;
    }

    var consentCookieName = dbcmFrontend.consentCookieName || 'dbcm_cookie_consent';
    var strings = dbcmFrontend.strings || {};
    var policyUrl = dbcmFrontend.policyUrl || '';
    var theme = dbcmFrontend.theme === 'dark' ? 'dark' : 'light';
    var maxAgeSeconds = 60 * 60 * 24 * 365;

    function getCookie(name) {
        var escaped = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var match = document.cookie.match(new RegExp('(?:^|; )' + escaped + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function setCookie(name, value, maxAge) {
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    function hide(el) {
        if (!el) {
            return;
        }
        el.hidden = true;
    }

    function show(el) {
        if (!el) {
            return;
        }
        el.hidden = false;
    }

    function applyTexts(banner, fab) {
        var text = banner.querySelector('#dbcm-cookie-banner-text');
        var policy = banner.querySelector('#dbcm-cookie-banner-policy');
        var declineButton = banner.querySelector('[data-dbcm-consent="declined"]');
        var acceptButton = banner.querySelector('[data-dbcm-consent="accepted"]');

        if (text) {
            text.textContent = strings.title || '';
        }
        if (declineButton) {
            declineButton.textContent = strings.decline || 'Decline';
        }
        if (acceptButton) {
            acceptButton.textContent = strings.accept || 'Accept';
        }
        if (fab) {
            fab.textContent = strings.settings || 'Cookie settings';
        }

        if (policyUrl && policy) {
            policy.href = policyUrl;
            policy.textContent = strings.policy || 'Privacy policy';
            policy.hidden = false;
        }
    }

    function updateUiByConsent(consent, banner, fab) {
        if (!consent) {
            show(banner);
            hide(fab);
            return;
        }

        hide(banner);
        show(fab);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.getElementById('dbcm-cookie-banner');
        var fab = document.getElementById('dbcm-cookie-fab');

        if (!banner || !fab) {
            return;
        }

        applyTexts(banner, fab);
        banner.classList.add('dbcm-theme-' + theme);
        fab.classList.add('dbcm-theme-' + theme);

        var consent = getCookie(consentCookieName);
        updateUiByConsent(consent, banner, fab);

        banner.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || !target.matches('[data-dbcm-consent]')) {
                return;
            }

            var value = target.getAttribute('data-dbcm-consent');
            if (value !== 'accepted' && value !== 'declined') {
                return;
            }

            setCookie(consentCookieName, value, maxAgeSeconds);
            window.location.reload();
        });

        fab.addEventListener('click', function () {
            setCookie(consentCookieName, '', 0);
            updateUiByConsent('', banner, fab);
        });
    });
})();
