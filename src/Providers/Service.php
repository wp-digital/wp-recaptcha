<?php

namespace WPD\Recaptcha\Providers;

abstract class Service implements ProviderInterface {

	/**
	 * @var Provider $provider
	 */
	protected Provider $provider;

	/**
	 * Service constructor.
	 *
	 * @param Provider $provider
	 */
	public function __construct( Provider $provider ) {
		$this->provider = $provider;
	}

	/**
	 * @return string
	 */
	public function get_site_key(): string {
		return $this->provider->get_site_key();
	}

	/**
	 * @return string
	 */
	public function get_script_url(): string {
		return $this->provider->get_script_url();
	}

	/**
	 * @return string
	 */
	public function js_snippet(): string {
		return $this->provider->js_snippet();
	}
}
