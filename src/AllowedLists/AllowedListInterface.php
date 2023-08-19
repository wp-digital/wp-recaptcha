<?php

namespace WPD\Recaptcha\AllowedLists;

interface AllowedListInterface {

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function is_allowed( string $ip ): bool;
}
