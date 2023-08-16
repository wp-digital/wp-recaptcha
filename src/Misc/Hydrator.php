<?php

namespace WPD\Recaptcha\Misc;

trait Hydrator {

	/**
	 * @param string $array
	 * @return self
	 */
	public static function from_array( string $array ): self {
		return new self( $array );
	}
}
