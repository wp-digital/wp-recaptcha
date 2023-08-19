<?php

namespace WPD\Recaptcha\AllowedLists;

use Cloudflare\API\Endpoints\Firewall;

class Cloudflare implements AllowedListInterface {

	/**
	 * @var Firewall $firewall
	 */
	protected Firewall $firewall;

	/**
	 * Cloudflare constructor.
	 *
	 * @param Firewall $firewall
	 */
	public function __construct( Firewall $firewall ) {
		$this->firewall = $firewall;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function is_allowed(string $ip): bool {

	}
}
