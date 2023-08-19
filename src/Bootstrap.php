<?php

namespace WPD\Recaptcha;

final class Bootstrap {

	private function __construct() {

	}

	public static function init(): ?self {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->run();
		}

		return $instance;
	}

	private function run(): void {

	}
}
