<?php

namespace WPD\Recaptcha\AllowedLists;

class Configurable implements AllowedListInterface {

	/**
	 * @var string
	 */
	protected string $option_name;

	/**
	 * Configurable constructor.
	 *
	 * @param string $option_name
	 */
	public function __construct( string $option_name ) {
		$this->option_name = $option_name;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function is_allowed( string $ip ): bool {
		$allowed_ips = (string) get_option( $this->option_name, '' );
		$allowed_ips = array_map( 'trim', explode( "\n", $allowed_ips ) );

		return in_array( $ip, $allowed_ips, true );
	}
}
