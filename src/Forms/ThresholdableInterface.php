<?php

namespace WPD\Recaptcha\Forms;

interface ThresholdableInterface {

	/**
	 * @return float
	 */
	public function threshold(): float;
}
