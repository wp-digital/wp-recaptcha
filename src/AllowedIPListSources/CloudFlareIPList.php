<?php

namespace Innocode\ReCaptcha\AllowedIPListSources;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\AccessRules;
use Innocode\ReCaptcha\Abstracts\AbstractAllowIPList;

/**
 * Class CloudFlareIPList.php
 * @package Innocode\ReCaptcha\AllowedIPListSourcces
 */
class CloudFlareIPList extends AbstractAllowIPList {

	/**
	 * @var AccessRules
	 */
	protected $_api_rules;
	/**
	 * @var array
	 */
	protected $_rules_list = [];
	/**
	 * @var array
	 */
	protected $_ips = [];

	/**
	 * @return array
	 */
	public function get_allowed_ips(): array {

		return $this->_fetch_ips();

	}

	/**
	 * @return bool
	 */
	protected function _is_enabled() {
		return defined( 'RECAPTCHA_CLOUDFLARE_EMAIL' ) && defined( 'RECAPTCHA_CLOUDFLARE_API_KEY' ) && defined( 'RECAPTCHA_CLOUDFLARE_ZONE_ID' );
	}

	/**
	 *
	 */
	protected function _api_init() {
		if ( $this->_is_enabled() ) {
			$key              = new APIKey( RECAPTCHA_CLOUDFLARE_EMAIL, RECAPTCHA_CLOUDFLARE_API_KEY );
			$adapter          = new Guzzle( $key );
			$this->_api_rules = new AccessRules( $adapter );
		}
	}

	protected function _collect_list_rules() {
		$api_rules = $this->_api_rules;
		if ( $api_rules ) {

			$pages_amount = 1;
			for ( $i = 1; $i <= $pages_amount; $i ++ ) {
				try {
					$rules_list = $api_rules->listRules( RECAPTCHA_CLOUDFLARE_ZONE_ID, '', 'whitelist', '', '', $i );
				} catch ( \Exception $e ) {
					break;
				}
				if ( $rules_list->result_info->count > 0 ) {
					if ( $pages_amount == 1 && $rules_list->result_info->total_pages > 1 ) {
						$pages_amount = $rules_list->result_info->total_pages;
					}
					$this->_rules_list = array_merge( $this->_rules_list, $rules_list->result );
				}
			}
		}
	}


	/**
	 * @return array
	 */
	protected function _fetch_ips() {
		$ips       = [];
		$cache_key = INNOCODE_WP_RECAPTCHA . '_cloudflare_allowed_ips';
		if ( $this->_is_enabled() ) {

			if ( false === ( $ips = get_transient( $cache_key ) ) ) {
				$this->_api_init();
				$this->_collect_list_rules();
				$this->_collect_ips();
				if ( $this->_ips ) {
					$ips = $this->_ips;
					set_transient( $cache_key, $ips, DAY_IN_SECONDS );
				}
			}
		}

		return $ips;

	}


	protected function _collect_ips() {
		if ( $this->_rules_list ) {
			foreach ( $this->_rules_list as $rule ) {
				if ( $rule->paused === false && $rule->configuration && $rule->configuration->target == 'ip' ) {
					$this->_ips = $rule->configuration->value;
				}
			}
			$this->_ips = array_unique( $this->_ips );
		}
	}


}