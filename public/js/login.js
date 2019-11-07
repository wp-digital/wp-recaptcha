(function (globalSettings, settings) {

    'use strict';

    settings.ids.forEach(function (id) {
        var form = document.getElementById(id);

        if (!form) return;

        grecaptcha.ready(function () {
            grecaptcha.execute(globalSettings.key, {
                action: settings.action
            }).then(function (token) {
                var recaptchaField = document.getElementById('recaptcha');

                if (recaptchaField) recaptchaField.value = token;

                document.body.className = document.body.className
                    .replace('innocode_recaptcha_loading', 'innocode_recaptcha_loaded');
            });
        });
    });
})(window.innocodeRecaptcha, window.innocodeRecaptchaLogin);
