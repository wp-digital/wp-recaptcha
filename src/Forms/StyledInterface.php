<?php

namespace WPD\Recaptcha\Forms;

interface StyledInterface {

	/**
	 * @return string
	 */
	public function enqueue_styles_action(): string;

	/**
	 * @return void
	 */
	public function enqueue_styles(): void;
}
