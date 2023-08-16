<?php

namespace WPD\Recaptcha\Providers;

class Turnstile extends Service implements VisibleInterface {

	/**
	 * @return string
	 */
	public function html(): string {
		return <<<HTML
<div class="wpd-recaptcha-turnstile" style="width: 300px; height: 65px"></div>
HTML;
	}

	/**
	 * @return string
	 */
	public function js_snippet(): string {
		return <<<JS
window.onloadTurnstileCallback = function () {
	{$this->provider->js_snippet()}
    document.querySelectorAll('.wpd-recaptcha-turnstile').forEach(function (el) {
        turnstile.render(el, {
            sitekey: '{$this->provider->get_site_key()}',
            action: '{$form->action()}',
			callback: function (token) {
                var form = el.closest('form');
				if (form) {
                	recaptchaCallback(el.closest('form'), token);
				}
			},
        });
    });
};
JS;
	}
}

