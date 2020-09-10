<?php

namespace Innocode\ReCaptcha\AllowedIPListSources;

use Innocode\ReCaptcha\Abstracts\AbstractAllowIPList;

/**
 * Class ConfigConstantIPList.php
 * @package Innocode\ReCaptcha\AllowedIPListSourcces
 */
class ConfigConstantIPList extends AbstractAllowIPList {


	public function get_allowed_ips(): array {

		if ( defined( 'INNOCODE_WP_RECAPTCHA_ALLOWED_IPS' ) && INNOCODE_WP_RECAPTCHA_ALLOWED_IPS ) {
			return explode( ",", trim( INNOCODE_WP_RECAPTCHA_ALLOWED_IPS ) );
		}

		return [];
	}


}