<?php

namespace WPD\Recaptcha;

/**
 * @return string|\WP_Error
 */
function admin_page() {
	try {
		return Bootstrap::init()->plugin()->admin_page;
	} catch ( \Exception $exception ) {
		return new \WP_Error( 'wpd_recaptcha_admin_page', $exception->getMessage() );
	}
}
