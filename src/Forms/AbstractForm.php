<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

abstract class AbstractForm implements FormInterface {

	/**
	 * @param Response $response
	 * @return void
	 */
	public function success( Response $response ): void {
		do_action( 'wpd_recaptcha_form_success', $this, $response );
	}

	/**
	 * @param \WP_Error $error
	 * @return \WP_Error
	 */
	protected function transform_internal_error( \WP_Error $error ): \WP_Error {
		$data = $error->get_error_data();

		if ( ! is_array( $data ) || ! isset( $data['codes'] ) ) {
			return new \WP_Error(
				'wpd_recaptcha_unknown_error',
				esc_html__( 'Unknown error happened.', 'wpd-recaptcha' ),
				[
					'status' => \WP_Http::FORBIDDEN,
				]
			);
		}

		[ $code ] = $data['codes'];

		if ( in_array(
			$code,
			[
				Response::ERROR_MISSING_INPUT_SECRET,
				Response::ERROR_INVALID_INPUT_SECRET,
				Response::ERROR_INVALID_WIDGET_ID,
				Response::ERROR_INVALID_PARSED_SECRET,
				Response::ERROR_INTERNAL_ERROR,
			],
			true
		) ) {
			return new \WP_Error(
				'wpd_recaptcha_internal_error',
				esc_html__( 'Cannot verify that you\'re not a robot at this moment. Please contact the site administrator.', 'wpd-recaptcha' ),
				[
					'status' => \WP_Http::INTERNAL_SERVER_ERROR,
				]
			);
		}

		if ( in_array(
			$code,
			[
				Response::ERROR_BAD_REQUEST,
				Response::ERROR_INVALID_ACTION,
				Response::ERROR_INVALID_HOSTNAME,
			],
			true
		) ) {
			return new \WP_Error(
				'wpd_recaptcha_bad_request',
				esc_html__( 'Something went wrong.', 'wpd-recaptcha' ),
				[
					'status' => \WP_Http::FORBIDDEN,
				]
			);
		}

		return $error;
	}
}
