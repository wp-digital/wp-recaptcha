<?php

namespace WPD\Recaptcha\Misc;

use WPD\Recaptcha\Exceptions\BadResponseException;

class HttpClient {

	/**
	 * @var Url
	 */
	protected Url $url;
	/**
	 * @var array
	 */
	protected array $request_args;

	/**
	 * @param Url   $url
	 * @param array $request_args
	 */
	public function __construct( Url $url, array $request_args = [] ) {
		$this->url          = $url;
		$this->request_args = $request_args;
	}

	/**
	 * @param string $path
	 * @param array  $query
	 * @param array  $request_args
	 * @return array
	 * @throws BadResponseException If the response is not valid.
	 */
	protected function request( string $path, array $query, array $request_args = [] ): array {
		$url  = ( $this->url )( $path, $query );
		$args = array_merge_recursive( $this->request_args, $request_args );

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new BadResponseException(
				$response->get_error_message(),
				is_int( $response->get_error_code() ) ? $response->get_error_code() : 0
			);
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body ) {
			throw new BadResponseException( 'Empty response body' );
		}

		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			throw new BadResponseException( 'Invalid response body' );
		}

		return $data;
	}

	/**
	 * @param string $path
	 * @param array  $query
	 * @param array  $request_args
	 * @return array
	 * @throws BadResponseException If the response is not valid.
	 */
	public function get( string $path, array $query = [], array $request_args = [] ): array {
		$args = array_merge_recursive(
			$request_args,
			[
				'method' => 'GET',
			]
		);

		return $this->request( $path, $query, $args );
	}

	/**
	 * @param string $path
	 * @param array  $body
	 * @param array  $query
	 * @param array  $request_args
	 * @return array
	 * @throws BadResponseException If the response is not valid.
	 */
	public function post( string $path, array $body = [], array $query = [], array $request_args = [] ): array {
		$args = array_merge_recursive(
			$request_args,
			[
				'method' => 'POST',
				'body'   => $body,
			]
		);

		return $this->request( $path, $query, $args );
	}
}
