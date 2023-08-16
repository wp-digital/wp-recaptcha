<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Exceptions\ValidationException;
use WPD\Recaptcha\Providers\Service;
use WPD\Recaptcha\Providers\VisibleInterface;

final class Controller {

	/**
	 * @var Service $service
	 */
	private Service $service;
	/**
	 * @var Validation $validation
	 */
	private Validation $validation;

	/**
	 * Controller constructor.
	 *
	 * @param Service    $service
	 * @param Validation $validation
	 */
	public function __construct(
		Service $service,
		Validation $validation
	) {
		$this->service    = $service;
		$this->validation = $validation;
	}

	/**
	 * @return void
	 */
	public function no_js_warning(): void {
		printf(
			<<<HTML
			<noscript>
				<p class="message">%s</p>
			</noscript>
			HTML,
			esc_html__( 'Please enable JavaScript in your browser settings to submit this form.', 'wpd-recaptcha' )
		);
	}

	/**
	 * @return void
	 */
	public function token(): void {
		if ( $this->service instanceof VisibleInterface ) {
			echo $this->service->html();
		}

		echo '<input type="hidden" name="wpd-recaptcha-token" />';
	}

	/**
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'wpd-recaptcha',
			$this->service->get_script_url(),
			[],
			null,
			true
		);
		wp_add_inline_script(
			'wpd-recaptcha',
			$this->service->js_snippet()
		);
	}

	/**
	 * @return void
	 */
	public function validate(): void {
	    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	        return;
		}

		$action = current_action();

		if (
			! isset( $_POST['wpd-recaptcha-token'] ) ||
			! is_string( $_POST['wpd-recaptcha-token'] ) ||
			$_POST['wpd-recaptcha-token'] === ''
		) {
			$error = new \WP_Error(
				'wpd_recaptcha_missing_token',
				esc_html__( 'The token is missing.', 'wpd-recaptcha' ),
				[
					'codes' => [ 'missing-input-secret' ],
				]
			);

			do_action( 'wpd_recaptcha_failed', $action, $error );

			return;
		}

		try {
			$response = ( $this->validation )( $_POST['wpd-recaptcha-token'] );
		} catch ( ValidationException $exception ) {
			$error = new \WP_Error(
				'wpd_recaptcha_validation_failed',
				esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
				[
					'codes' => $exception->get_codes(),
				]
			);

			do_action( 'wpd_recaptcha_failed', $action, $error );

			return;
		}

		do_action( 'wpd_recaptcha_validated', $action, $response );
	}
}
