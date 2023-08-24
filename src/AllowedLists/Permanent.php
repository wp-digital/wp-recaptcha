<?php

namespace WPD\Recaptcha\AllowedLists;

class Permanent implements AllowedListInterface {

	/**
	 * @var array
	 */
	protected array $allowed_ips;

	/**
	 * Permanent constructor.
	 *
	 * @param array $allowed_ips
	 */
	public function __construct( array $allowed_ips ) {
		$this->allowed_ips = $allowed_ips;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function is_allowed( string $ip ): bool {
		return in_array( $ip, $this->allowed_ips, true );
	}
}
