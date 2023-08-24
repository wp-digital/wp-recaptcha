<?php

namespace WPD\Recaptcha\Forms;

trait LoginStyles {

	/**
	 * @return string
	 */
	public function enqueue_styles_action(): string {
		return 'login_enqueue_scripts';
	}

	/**
	 * @return void
	 */
	public function enqueue_styles(): void {
		wp_add_inline_style(
			'login',
			<<<CSS
			#login .wpd-recaptcha-turnstile {
				margin-left: -15px;
				margin-bottom: 16px;
			}
			CSS
		);
	}
}
