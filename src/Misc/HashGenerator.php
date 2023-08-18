<?php

namespace WPD\Recaptcha\Misc;

trait HashGenerator {

	/**
	 * @return string
	 */
	public function to_hash(): string {
		$args = array_map(
			'strval',
			array_values( get_object_vars( $this ) )
		);

		return static::hash( ...$args );
	}

	/**
	 * @param string ...$args
	 * @return string
	 */
	public static function hash( string ...$args ): string {
		$data = implode( '|', $args );

		return wp_hash( $data, 'nonce' );
	}
}
