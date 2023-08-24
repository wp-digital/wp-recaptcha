<?php

if ( ! defined( 'WPD_RECAPTCHA_KEY' ) && defined( 'RECAPTCHA_KEY' ) ) {
	define( 'WPD_RECAPTCHA_KEY', RECAPTCHA_KEY );
}

if ( ! defined( 'WPD_RECAPTCHA_SECRET' ) && defined( 'RECAPTCHA_SECRET' ) ) {
	define( 'WPD_RECAPTCHA_SECRET', RECAPTCHA_SECRET );
}

if ( ! defined( 'WPD_RECAPTCHA_ALLOWED_IPS' ) ) {
	define( 'WPD_RECAPTCHA_ALLOWED_IPS', '' );
}
