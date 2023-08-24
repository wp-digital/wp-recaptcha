<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

class LostPassword extends AbstractForm implements StyledInterface {

	use AllowedHosts, LoginStyles;

	/**
	 * @return string
	 */
	public function action(): string {
		return 'lostpassword_form';
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
		return 'login_form_lostpassword';
	}

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
			'lostpassword_errors',
			function ( \WP_Error $errors ) use ( $error ): \WP_Error {
				if ( $errors->has_errors() ) {
					return $errors;
				}

				$data = $error->get_error_data();

				if ( ! is_array( $data ) || ! isset( $data['codes'] ) ) {
					return $errors;
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
					$errors->add(
						'wpd_recaptcha_bad_response',
						__(
							'<strong>ERROR</strong>: JavaScript doesn\'t seem to be enabled.',
							'wpd-recaptcha'
						)
					);
				} elseif ( in_array(
					$code,
					[
						Response::ERROR_TIMEOUT_OR_DUPLICATE,
						Response::ERROR_CHALLENGE_EXPIRED,
					],
					true
				) ) {
					$errors->add(
						'wpd_recaptcha_timeout_or_duplicate',
						__( '<strong>ERROR</strong>: Please try again.', 'wpd-recaptcha' )
					);
				} elseif ( $code === Response::ERROR_SCORE_TOO_LOW ) {
					$errors->add(
						'wpd_recaptcha_score_too_low',
						__( '<strong>ERROR</strong>: Sorry, we cannot confirm that this is not a bot attempt. Please try again.', 'wpd-recaptcha' )
					);
				}

				return $errors;
			}
		);
	}
}
