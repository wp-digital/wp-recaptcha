<?php
/**
 * Plugin Name: Bot Protection
 * Description: Helps to protect website with Google reCAPTCHA v3 or Cloudflare Turnstile.
 * Version: 3.0.2
 * Author: SMFB Dinamo
 * Author URI: https://smfb-dinamo.com
 * Tested up to: 6.3.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if (
	(
		defined( 'WPD_RECAPTCHA_KEY' ) &&
		defined( 'WPD_RECAPTCHA_SECRET' )
	) ||
	(
		defined( 'RECAPTCHA_KEY' ) &&
		defined( 'RECAPTCHA_SECRET' )
	)
) {
	WPD\Recaptcha\Bootstrap::init( __FILE__ );
}
