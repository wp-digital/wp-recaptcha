<?php

namespace WPD\Recaptcha\Forms;

trait AllowedHosts {

	/**
	 * @return string[]
	 */
	public function allowed_hosts(): array {
		return [
			'localhost',
			parse_url( site_url(), PHP_URL_HOST ),
			parse_url( home_url(), PHP_URL_HOST ),
		];
	}
}
