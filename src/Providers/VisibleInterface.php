<?php

namespace WPD\Recaptcha\Providers;

interface VisibleInterface {

	/**
	 * @return string
	 */
	public function html(): string;
}
