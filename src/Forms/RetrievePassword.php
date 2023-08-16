<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

class RetrievePassword implements FormInterface {

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
		return 'login_enqueue_scripts';
	}

	/**
	 * @return string
	 */
	public function validation_action(): string {
		return 'login_form_retrievepassword';
	}

	/**
	 * @param Response $response
	 * @return void
	 */
	public function handle_validated( Response $response ): void {

	}

	/**
	 * @param \WP_Error $error
	 * @return void
	 */
	public function handle_failed( \WP_Error $error ): void {

	}
}
