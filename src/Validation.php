<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Exceptions\ValidationException;

class Validation {

	/**
	 * @var HttpClient $http_client
	 */
	protected HttpClient $http_client;

	/**
	 * Validation constructor.
	 *
	 * @param HttpClient $http_client
	 */
	public function __construct( HttpClient $http_client ) {
		$this->http_client = $http_client;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws ValidationException If the token is invalid.
	 */
	public function __invoke( Request $request ): Response {
		try {
			$data     = $this->http_client->post( 'siteverify', $request->to_array() );
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
