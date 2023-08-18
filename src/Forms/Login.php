<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

class Login extends AbstractForm implements ThresholdableInterface {

	use AllowedHosts;

	/**
	 * @return string
	 */
	public function action(): string {
		return 'login_form';
	}

	/**
	 * @return string
	 */
	public function no_js_warning_action(): string {
		return 'login_message';
	}

	/**
	 * @return string
	 */
	public function enqueue_scripts_action(): string {
		return 'login_footer';
	}

	/**
	 * @return string
	 */
	public function validation_action(): string {
		return 'login_form_login';
	}

	/**
	 * @return float
	 */
	public function threshold(): float {
		return 0.5;
	}

	/**
	 * @TODO: Remove this method before release, it's for testing only!
	 * @param Response $response
	 * @return void
	 */
//	public function success( Response $response ): void {
//		parent::success( $response );
//		add_filter(
//			'authenticate',
//			function ( $user ) {
//				if ( ! ( $user instanceof \WP_User ) ) {
//					return $user;
//				}
//				do_action( 'wpd_recaptcha_verify', $user );
//			},
//			100
//		);
//	}

	/**
	 * @param \WP_Error $error
	 * @return void
	 */
	public function fail( \WP_Error $error ): void {
		$internal_error = $this->transform_internal_error( $error );

		if ( $internal_error !== $error ) {
			wp_die(
				esc_html( $internal_error->get_error_message() ),
				esc_html__( 'Failed', 'wpd-recaptcha' ),
				[
					'code' => (int) ( $internal_error->get_error_data()['status'] ?? \WP_Http::INTERNAL_SERVER_ERROR ),
				]
			);
		}

		add_filter(
			'authenticate',
			function ( $user ) use ( $error ) {
				if ( ! ( $user instanceof \WP_User ) ) {
					return $user;
				}

				$data = $error->get_error_data();

				if ( ! is_array( $data ) || ! isset( $data['codes'] ) ) {
					return $user;
				}

				[ $code ] = $data['codes'];

				if ( in_array(
					$code,
					[
						Response::ERROR_MISSING_INPUT_RESPONSE,
						Response::ERROR_INVALID_INPUT_RESPONSE,
					],
					true
				) ) {
					return new \WP_Error(
						'wpd_recaptcha_bad_response',
						__(
							'<strong>ERROR</strong>: JavaScript doesn\'t seem to be enabled.',
							'wpd-recaptcha'
						)
					);
				}

				if ( in_array(
					$code,
					[
						Response::ERROR_TIMEOUT_OR_DUPLICATE,
						Response::ERROR_CHALLENGE_EXPIRED,
					],
					true
				) ) {
					return new \WP_Error(
						'wpd_recaptcha_timeout_or_duplicate',
						__( '<strong>ERROR</strong>: Please try again.', 'wpd-recaptcha' )
					);
				}

				if ( $code === Response::ERROR_SCORE_TOO_LOW ) {
					do_action( 'wpd_recaptcha_verify', $user );
				}

				return $user;
			},
			100
		);
	}
}
