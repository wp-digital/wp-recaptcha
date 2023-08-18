<?php

namespace WPD\Recaptcha;

final class Plugin {

	/**
	 * @var Controller $controller
	 */
	private Controller $controller;
	/**
	 * @var FormsRepository $forms_repository
	 */
	private FormsRepository $forms_repository;

	/**
	 * Plugin constructor.
	 *
	 * @param Controller      $controller
	 * @param FormsRepository $forms_repository
	 */
	public function __construct(
		Controller $controller,
		FormsRepository $forms_repository
	) {
		$this->controller       = $controller;
		$this->forms_repository = $forms_repository;
	}

	/**
	 * @return void
	 */
	public function run(): void {
		$actions = [
			'no_js_warning'   => $this->forms_repository->no_js_warning_actions(),
			'token'           => $this->forms_repository->actions(),
			'enqueue_scripts' => $this->forms_repository->enqueue_scripts_actions(),
			'validate'        => $this->forms_repository->validation_actions(),
		];

		foreach ( $actions as $action => $hooks ) {
			foreach ( $hooks as $hook ) {
				add_action(
					$hook,
					function () use ( $action ): void {
						$this->controller->{$action}( $this->forms_repository );
					}
				);
			}
		}

		add_action( 'wpd_recaptcha_verify', [ $this->controller, 'verify' ] );
		add_action( 'login_form_wpd_recaptcha_verification', [ $this->controller, 'verification' ] );
		add_action( 'login_form_login', [ $this->controller, 'verification_errors' ] );
	}
}
