<?php

namespace WPD\Recaptcha\Exceptions;

class ValidationException extends \Exception {

	/**
	 * @var string[] $codes
	 */
	protected array $codes = [];

	/**
	 * @param string   $message
	 * @param string[] $codes
	 * @param \Throwable|null $previous
	 */
	public function __construct(
		string $message = '',
		array $codes = [],
		\Throwable $previous = null
	) {
		$this->codes = $codes;

		parent::__construct( $message, 0, $previous );
	}

	/**
	 * @return string[]
	 */
	public function get_codes(): array {
		return $this->codes;
	}
}
