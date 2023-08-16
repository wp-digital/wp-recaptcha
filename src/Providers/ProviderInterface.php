<?php

namespace WPD\Recaptcha\Providers;

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
	 * @return string
	 */
	public function js_snippet(): string;
}
