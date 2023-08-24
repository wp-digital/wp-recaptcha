<?php

namespace WPD\Recaptcha\Exceptions;

class NotReadableFileException extends \Exception {

	/**
	 * @param string          $file
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct( string $file, int $code = 0, \Throwable $previous = null ) {
		parent::__construct(
			sprintf(
				'File %s is not readable.',
				$file
			),
			$code,
			$previous
		);
	}
}
