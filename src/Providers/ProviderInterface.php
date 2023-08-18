<?php

namespace WPD\Recaptcha\Providers;

use WPD\Recaptcha\FormsRepository;

interface ProviderInterface {

	/**
	 * @return string
	 */
	public function get_site_key(): string;

	/**
	 * @return string
	 */
	public function get_script_url(): string;

	/**
	 * @param FormsRepository $forms_repository
	 * @return string
	 */
	public function js_snippet( FormsRepository $forms_repository ): string;
}
