<?php

namespace WPD\Recaptcha\Providers;

use WPD\Recaptcha\FormsRepository;

class ReCaptcha extends Service {

	/**
	 * @param FormsRepository $forms_repository
	 * @return string
	 */
	public function js_snippet( FormsRepository $forms_repository ): string {
		return <<<JS
(function () {
    if (typeof grecaptcha === 'undefined') {
		grecaptcha = {};
	}
	grecaptcha.ready = function (cb) {
		var c;
		if (typeof grecaptcha === 'undefined') {
			c = '___grecaptcha_cfg';
			window[c] = window[c] || {};
			(window[c]['fns'] = window[c]['fns']||[]).push(cb);
		} else {
			cb();
		}
	}
    {$this->provider->js_snippet( $forms_repository )}
	document.querySelectorAll('form[method="post"]').forEach(function (form) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			grecaptcha.ready(function() {
				grecaptcha.execute('{$this->provider->get_site_key()}', {
					action: '{$forms_repository->did_action()}'
				}).then(function (token) {
                    recaptchaCallback(form, token);
					form.submit();
				});
			});
		});
	});
})();
JS;
	}
}
