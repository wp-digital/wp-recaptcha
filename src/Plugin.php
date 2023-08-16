<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Forms\ThresholdableInterface;

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
		$actions = array_map(
			fn ( array $hooks ) => array_unique( $hooks ),
			[
				'no_js_warning'   => $this->forms_repository->no_js_warning_actions(),
				'token'           => $this->forms_repository->actions(),
				'enqueue_scripts' => $this->forms_repository->enqueue_scripts_actions(),
				'validate'        => $this->forms_repository->validation_actions(),
			]
		);

		foreach ( $actions as $action => $hooks ) {
			foreach ( $hooks as $hook ) {
				add_action( $hook, [ $this->controller, $action ] );
			}
		}

		add_action( 'wpd_recaptcha_validated', [ $this, 'handle_validated' ], 10, 2 );
		add_action( 'wpd_recaptcha_failed', [ $this, 'handle_failed' ], 10, 2 );
	}

	/**
	 * @param string   $action
	 * @param Response $response
	 * @return void
	 */
	public function handle_validated( string $action, Response $response ): void {
		$form = $this->forms_repository->find_by_validation_action( $action );

		if ( $form === null ) {
			return;
		}

		if ( $response->get_action() !== $form->action() ) {
			$form->handle_failed(
				new \WP_Error(
					'wpd_recaptcha_validation_failed',
					esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
					[
						'codes' => [ 'invalid-action' ],
					]
				)
			);

			return;
		}

		if ( $form instanceof ThresholdableInterface ) {
			if ( $response->get_score() < $form->threshold() ) {
				$form->handle_failed(
					new \WP_Error(
						'wpd_recaptcha_validation_failed',
						esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
						[
							'codes' => [ 'score-too-low' ],
						]
					)
				);

				return;
			}
		}

		$form->handle_validated( $response );
	}

	/**
	 * @param string    $action
	 * @param \WP_Error $error
	 * @return void
	 */
	public function handle_failed( string $action, \WP_Error $error ): void {
		$form = $this->forms_repository->find_by_validation_action( $action );

		if ( $form === null ) {
			return;
		}

		$form->handle_failed( $error );
	}
}
