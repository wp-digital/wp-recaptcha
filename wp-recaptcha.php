<?php
/**
 * Plugin Name: reCAPTCHA
 * Description: Helps to protect website with Google reCAPTCHA v3.
 * Version: 2.3.0
 * Author: Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.5.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\ReCaptcha;

define( 'INNOCODE_WP_RECAPTCHA_VERSION', '2.3.0' );
define( 'INNOCODE_WP_RECAPTCHA_FILE', __FILE__ );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'innocode_recaptcha_init' ) ) {
    function innocode_recaptcha_init() {
        /**
         * @var ReCaptcha\Plugin $innocode_recaptcha
         */
        global $innocode_recaptcha;

        $innocode_recaptcha->run();
    }
}

if ( defined( 'RECAPTCHA_KEY' ) && defined( 'RECAPTCHA_SECRET' ) ) {
    $GLOBALS['innocode_recaptcha'] = new ReCaptcha\Plugin();

    add_action( 'init', 'innocode_recaptcha_init' );
}

if ( ! function_exists( 'innocode_recaptcha' ) ) {
    function innocode_recaptcha() {
        /**
         * @var ReCaptcha\Plugin $innocode_recaptcha
         */
        global $innocode_recaptcha;

        if ( is_null( $innocode_recaptcha ) ) {
            trigger_error( 'Missing required constants RECAPTCHA_KEY and RECAPTCHA_SECRET.', E_USER_ERROR );
        }

        return $innocode_recaptcha;
    }
}
