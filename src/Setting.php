<?php

namespace WPD\Recaptcha;

class Setting {

	/**
	 * @var string $name
	 */
	public string $name;
	/**
	 * @var string $label
	 */
	public string $label;
	/**
	 * @var string $sanitize_callback
	 */
	protected string $sanitize_callback;
	/**
	 * @var array $attributes
	 */
	protected array $attributes;

	/**
	 * Setting constructor.
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $sanitize_callback
	 * @param array  $attributes
	 */
	public function __construct(
		string $name,
		string $label,
		string $sanitize_callback = 'sanitize_text_field',
		array $attributes = []
	) {
		$this->name              = $name;
		$this->label             = $label;
		$this->sanitize_callback = $sanitize_callback;
		$this->attributes        = $attributes;
	}

	/**
	 * @return void
	 */
	public function register(): void {
		register_setting(
			'wpd_recaptcha',
			$this->name,
			[
				'sanitize_callback' => $this->sanitize_callback,
			]
		);
	}

	/**
	 * @return string
	 */
	public function value(): string {
		return (string) get_option( $this->name, '' );
	}

	/**
	 * @return void
	 */
	public function render(): void {
		$attributes = array_merge(
			[
				'type'  => 'text',
				'id'    => $this->name,
				'name'  => $this->name,
				'class' => 'regular-text code',
			],
			$this->attributes
		);
		$attrs      = '';

		/**
		 * @var string $key
		 * @var string $value
		 */
		foreach ( $attributes as $key => $value ) {
			if (
				$key === 'type' && $value === 'textarea'
	            || $key === 'description'
			) {
				continue;
			}

			$attrs .= sprintf(
				'%s="%s" ',
				esc_attr( $key ),
				esc_attr( $value )
			);
		}

		printf(
			$attributes['type'] === 'textarea' ? '<textarea %s>%s</textarea>' : '<input %s value="%s" />',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			rtrim( $attrs ),
			esc_attr( $this->value() )
		);

		if ( isset( $attributes['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $attributes['description'] )
			);
		}
	}

	/**
	 * @return void
	 */
	public function delete(): void {
		delete_option( $this->name );
	}
}
