<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\AllowedLists\AllowedListInterface;

final class Firewall {

	/**
	 * @var array AllowedListInterface[]
	 */
	private array $allowed_lists;

	/**
	 * Firewall constructor.
	 *
	 * @param AllowedListInterface[] $allowed_lists
	 */
	public function __construct( array $allowed_lists ) {
		$this->allowed_lists = $allowed_lists;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function is_allowed( string $ip ): bool {
		foreach ( $this->allowed_lists as $allowed_list ) {
			if ( $allowed_list->is_allowed( $ip ) ) {
				return true;
			}
		}

		return false;
	}
}
