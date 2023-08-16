<?php

namespace WPD\Recaptcha\Forms;

use WPD\Recaptcha\Response;

interface FormInterface {

	/**
	 * @return string
	 */
	public function action(): string;

	/**
	 * @return string
	 */
	public function no_js_warning_action(): string;

	/**
	 * @return string
	 */
	public function enqueue_scripts_action(): string;

	/**
	 * @return string
	 */
	public function validation_action(): string;

	/**
	 * @param Response $response
	 * @return void
	 */
	public function handle_validated( Response $response ): void;

	/**
	 * @param \WP_Error $error
	 * @return void
	 */
	public function handle_failed( \WP_Error $error ): void;
}
