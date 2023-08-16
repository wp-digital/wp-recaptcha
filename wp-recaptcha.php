<?php
/**
 * Plugin Name: Bot Protection
 * Description: Helps to protect website with Google reCAPTCHA v3 or Cloudflare Turnstile.
 * Version: 3.0.0
 * Author: SMFB Dinamo
 * Author URI: https://smfb-dinamo.com
 * Tested up to: 6.3.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use WPD\Recaptcha;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

(
	new Recaptcha\Plugin(
		new Recaptcha\Controller(
			new Recaptcha\Providers\Turnstile(
				new Recaptcha\Providers\Provider(
					'0x4AAAAAAADHkXYeqYvm0sNh',
					'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback'
				)
			),
			new Recaptcha\Validation(
				'0x4AAAAAAADHkefD1rCVGfBU5G1g2Kp-ecQ',
				new Recaptcha\HttpClient(
					new Recaptcha\Url( 'https://challenges.cloudflare.com/turnstile/v0' )
				),
				new Vectorface\Whip\Whip()
			)
		),
		new Recaptcha\FormsRepository(
			new Recaptcha\Forms\Login(),
			new Recaptcha\Forms\LostPassword(),
			new Recaptcha\Forms\RetrievePassword()
		)
	)
)->run();
