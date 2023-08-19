<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Misc\Controller;

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
	 * @var string $admin_page
	 */
	public string $admin_page;
	/**
	 * @var Settings $settings
	 */
	private Settings $settings;

	/**
	 * Plugin constructor.
	 *
	 * @param Controller      $controller
	 * @param FormsRepository $forms_repository
	 * @param string		  $admin_page
	 * @param Settings        $settings
	 */
	public function __construct(
		Controller $controller,
		FormsRepository $forms_repository,
		string $admin_page,
		Settings $settings
	) {
		$this->controller       = $controller;
		$this->forms_repository = $forms_repository;
		$this->admin_page       = $admin_page;
		$this->settings         = $settings;
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

		add_action( 'admin_menu', [ $this, 'admin_page' ] );
		add_action( 'admin_init', function () {
			$this->settings->register( $this->admin_page );
		} );
	}

	/**
	 * @return void
	 */
	public function admin_page(): void {
		add_submenu_page(
			'options-general.php',
			esc_html__( 'Bot Protection', 'wpd-recaptcha' ),
			esc_html__( 'Bot Protection', 'wpd-recaptcha' ),
			'manage_options',
			$this->admin_page,
			[ $this->controller, 'admin_page' ]
		);
	}
}
