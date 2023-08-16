<?php

namespace WPD\Recaptcha;

use Vectorface\Whip\Whip;
use WPD\Recaptcha\Exceptions\ValidationException;

class Validation {

	/**
	 * @var string $secret_key
	 */
	protected string $secret_key;
	/**
	 * @var HttpClient $http_client
	 */
	protected HttpClient $http_client;
	/**
	 * @var Whip $whip
	 */
	protected Whip $whip;

	/**
	 * Validation constructor.
	 *
	 * @param string     $secret_key
	 * @param HttpClient $http_client
	 * @param Whip       $whip
	 */
	public function __construct(
		string $secret_key,
		HttpClient $http_client,
		Whip $whip
	) {
		$this->secret_key  = $secret_key;
		$this->http_client = $http_client;
		$this->whip        = $whip;
	}

	/**
	 * @param string $token
	 * @return Response
	 * @throws ValidationException
	 */
	public function __invoke( string $token ): Response {
		$request   = new Request( $this->secret_key, $token );
		$remote_ip = $this->whip->getValidIpAddress();

		if ( $remote_ip !== false ) {
			$request->set_remote_ip( $remote_ip );
		}

		try {
			$data = $this->http_client->post( 'siteverify', $request->to_array() );
			$response = new Response( $data );
		} catch ( \Exception $exception ) {
			throw new ValidationException(
				'Failed to validate token',
				[],
				$exception
			);
		}

		if ( ! $response->is_success() ) {
			throw new ValidationException(
				'Failed to validate token',
				$response->get_error_codes()
			);
		}

		return $response;
	}
}
