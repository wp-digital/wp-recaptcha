<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Forms\FormInterface;

final class FormsRepository {

	/**
	 * @var FormInterface[] $forms
	 */
	private array $forms;

	/**
	 * FormsRepository constructor.
	 *
	 * @param FormInterface ...$forms
	 * @throws \InvalidArgumentException If validation actions are not unique.
	 */
	public function __construct( FormInterface ...$forms ) {
		$this->forms = apply_filters( 'wpd_recaptcha_forms', $forms );

		$validation_actions        = $this->validation_actions();
		$unique_validation_actions = array_unique( $validation_actions );

		if ( count( $validation_actions ) !== count( $unique_validation_actions ) ) {
			throw new \InvalidArgumentException(
				'Validation actions must be unique'
			);
		}
	}

	/**
	 * @return FormInterface[]
	 */
	private function column( string $column ): array {
		return array_unique(
			array_map(
				fn ( FormInterface $form ) => $form->{$column}(),
				$this->forms
			)
		);
	}

	/**
	 * @return string[]
	 */
	public function actions(): array {
		return $this->column( 'action' );
	}

	/**
	 * @return string[]
	 */
	public function no_js_warning_actions(): array {
		return $this->column( 'no_js_warning_action' );
	}

	/**
	 * @return string[]
	 */
	public function enqueue_scripts_actions(): array {
		return $this->column( 'enqueue_scripts_action' );
	}

	/**
	 * @return string[]
	 */
	public function validation_actions(): array {
		return $this->column( 'validation_action' );
	}

	/**
	 * @param string $action
	 * @return FormInterface|null
	 */
	public function find_by_validation_action( string $action ): ?FormInterface {
		foreach ( $this->forms as $form ) {
			if ( $form->validation_action() === $action ) {
				return $form;
			}
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	public function did_action(): ?string {
		foreach ( $this->forms as $form ) {
			if ( did_action( $form->action() ) ) {
				return $form->action();
			}
		}

		return null;
	}
}
