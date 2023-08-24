<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Forms\FormInterface;
use WPD\Recaptcha\Forms\StyledInterface;

final class FormsRepository {

	/**
	 * Original forms.
	 *
	 * @var FormInterface[] $forms
	 */
	private array $forms;
	/**
	 * Forms with filters applied and stored in memory.
	 *
	 * @var FormInterface[] $in_memory_forms
	 */
	private array $in_memory_forms;

	/**
	 * FormsRepository constructor.
	 *
	 * @param FormInterface[] $forms
	 */
	public function __construct( array $forms ) {
		$this->forms = $forms;
	}

	/**
	 * @return FormInterface[]
	 * @throws \InvalidArgumentException If validation actions are not unique.
	 */
	public function all(): array {
		if ( ! empty( $this->in_memory_forms ) ) {
			return $this->in_memory_forms;
		}

		$this->in_memory_forms = apply_filters( 'wpd_recaptcha_forms', $this->forms );

		$validation_actions        = $this->validation_actions();
		$unique_validation_actions = array_unique( $validation_actions );

		if ( count( $validation_actions ) !== count( $unique_validation_actions ) ) {
			throw new \InvalidArgumentException(
				'Validation actions must be unique'
			);
		}

		return $this->in_memory_forms;
	}

	/**
	 * @return FormInterface[]
	 */
	private function column( string $column ): array {
		return array_unique(
			array_map(
				fn ( FormInterface $form ) => $form->{$column}(),
				$this->all()
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
	public function enqueue_styles_actions(): array {
		return array_unique(
			array_map(
				fn ( StyledInterface $form ) => $form->enqueue_styles_action(),
				array_filter(
					$this->all(),
					fn ( FormInterface $form ) => $form instanceof StyledInterface
				)
			)
		);
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
	 * @param string $column
	 * @param string $value
	 * @return FormInterface|null
	 */
	private function find_by_column( string $column, string $value ): ?FormInterface {
		foreach ( $this->all() as $form ) {
			if ( $form->{$column}() === $value ) {
				return $form;
			}
		}

		return null;
	}

	/**
	 * @param string $action
	 * @return FormInterface|null
	 */
	public function find_by_enqueue_styles_action( string $action ): ?FormInterface {
		return $this->find_by_column( 'enqueue_styles_action', $action );
	}

	/**
	 * @param string $action
	 * @return FormInterface|null
	 */
	public function find_by_validation_action( string $action ): ?FormInterface {
		return $this->find_by_column( 'validation_action', $action );
	}

	/**
	 * @return string|null
	 */
	public function did_action(): ?string {
		foreach ( $this->all() as $form ) {
			if ( did_action( $form->action() ) ) {
				return $form->action();
			}
		}

		return null;
	}
}
