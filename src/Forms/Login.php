<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

class Login implements FormInterface, ThresholdableInterface {

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
		return 'login_enqueue_scripts';
	}

	/**
	 * @return string
	 */
	public function validation_action(): string {
		return 'login_form_login';
	}

	/**
	 * @param Response $response
	 * @return void
	 */
	public function handle_validated( Response $response ): void {
		var_dump( $response);die;
	}

	/**
	 * @param \WP_Error $error
	 * @return void
	 */
	public function handle_failed( \WP_Error $error ): void {
var_dump( $error);die;
	}

	/**
	 * @return float
	 */
	public function threshold(): float {
		return 0.5;
	}
}
