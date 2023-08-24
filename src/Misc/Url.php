<?php

namespace WPD\Recaptcha\Misc;

class Url {

	/**
	 * @var string
	 */
	protected string $base_url;

	/**
	 * @param string $base_url
	 */
	public function __construct( string $base_url ) {
		$this->base_url = $base_url;
	}

	/**
	 * @return string
	 */
	public function get_base_url(): string {
		return $this->base_url;
	}

	/**
	 * @param string $path
	 * @param array  $query
	 * @return string
	 */
	public function __invoke( string $path = '', array $query = [] ): string {
		$url = $this->get_base_url();

		if ( $path ) {
			$url = trailingslashit( $url ) . ltrim( $path, '/' );
		}

		if ( ! empty( $query ) ) {
			$url .= '?' . http_build_query( $query );
		}

		return $url;
	}
}
