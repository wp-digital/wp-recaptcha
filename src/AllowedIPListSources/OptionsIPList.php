<?php

namespace Innocode\ReCaptcha\AllowedIPListSources;

use Innocode\ReCaptcha\Admin\Field;
use Innocode\ReCaptcha\Admin\OptionsPage;
use Innocode\ReCaptcha\Abstracts\AbstractAllowIPList;
use Innocode\ReCaptcha\Admin\PluginSettings;
use Innocode\ReCaptcha\Admin\Section;
use Innocode\ReCaptcha\Admin\Setting;

/**
 * Class OptionsList.php
 * @package Innocode\ReCaptcha\AllowedIPListSourcces
 */
class OptionsIPList extends AbstractAllowIPList {


	/**
	 * @return mixed
	 */
	protected function _get_option_value() {
		return get_option( PluginSettings::get_ips_option_name() );
	}

	/**
	 * @return array
	 */
	public function get_allowed_ips(): array {
		$ips = $this->_get_option_value();

		if ( $ips && is_string( $ips ) ) {
			return array_map('trim',explode( "\n", trim( $ips ) ));
		}

		return [];
	}


}