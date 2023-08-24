<?php

namespace WPD\Recaptcha\Misc;

interface Hashable {

	/**
	 * @return string
	 */
	public function to_hash(): string;
}
