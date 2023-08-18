<?php

namespace WPD\Recaptcha;

final class View {

	/**
	 * @var string $base_path
	 */
	private string $base_path;

	/**
	 * View constructor.
	 *
	 * @param string $base_path
	 */
	public function __construct( string $base_path ) {
		$this->base_path = $base_path;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function __invoke( string $name ): void {
		require "$this->base_path/$name.php";
	}
}
