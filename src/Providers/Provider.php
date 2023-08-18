<?php

namespace WPD\Recaptcha\Providers;

use WPD\Recaptcha\FormsRepository;

class Provider implements ProviderInterface {

	/**
	 * @var string $site_key
	 */
	protected string $site_key;
	/**
	 * @var string $script_url
	 */
	protected string $script_url;

	/**
	 * Provider constructor.
	 *
	 * @param string $site_key
	 * @param string $script_url
	 */
	public function __construct(
		string $site_key,
		string $script_url
	) {
		$this->site_key   = $site_key;
		$this->script_url = $script_url;
	}

	/**
	 * @return string
	 */
	public function get_site_key(): string {
		return $this->site_key;
	}

	/**
	 * @return string
	 */
	public function get_script_url(): string {
		return $this->script_url;
	}

	/**
	 * @param FormsRepository $forms_repository
	 * @return string
	 */
	public function js_snippet( FormsRepository $forms_repository ): string {
		return <<<JS
var recaptchaCallback = function (el, token) {
    var input = el.querySelector('[name="wpd-recaptcha-token"]');
    if (input) {
		input.value = token;
	}
}
JS;
	}
}
