<?php

namespace Innocode\ReCaptcha;

use Innocode\ReCaptcha\Abstracts\AbstractAction;
use Innocode\ReCaptcha\Abstracts\AbstractAllowIPList;
use Innocode\ReCaptcha\Actions\LoginFormAction;
use Innocode\ReCaptcha\Admin\PluginSettings;
use Innocode\ReCaptcha\Admin\Section;
use Innocode\ReCaptcha\AllowedIPListSources\CloudFlareIPList;
use Innocode\ReCaptcha\AllowedIPListSources\ConfigConstantIPList;
use Innocode\ReCaptcha\AllowedIPListSources\OptionsIPList;
use ReCaptcha\ReCaptcha;
use Vectorface\Whip\Whip;

/**
 * Class Plugin
 * @package Innocode\ReCaptcha
 */
final class Plugin {
	/**
	 * @var string
	 */
	private $_api_script_url = 'https://www.google.com/recaptcha/api.js';
	/**
	 * @var string
	 */
	private $_key;
	/**
	 * @var string
	 */
	private $_secret;
	/**
	 * @var ReCaptcha
	 */
	private $_recaptcha;
	/**
	 * @var Whip
	 */
	private $_whip;
	/**
	 * @var AbstractAction[]
	 */
	private $_actions = [];


	/**
	 * @var array
	 */
	private $_allowed_ips = [];

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		if ( defined( 'RECAPTCHA_API_SCRIPT_URL' ) ) {
			$this->_api_script_url = RECAPTCHA_API_SCRIPT_URL;
		}

		$this->_key       = defined( 'RECAPTCHA_KEY' ) ? RECAPTCHA_KEY : '';
		$this->_secret    = defined( 'RECAPTCHA_SECRET' ) ? RECAPTCHA_SECRET : '';
		$this->_recaptcha = new ReCaptcha( $this->_secret );
		$this->_whip      = new Whip();
		new PluginSettings();

		$this->add_action( 'login', new LoginFormAction() );
	}

	/**
	 * @param string $handle
	 * @param AbstractAction $action
	 */
	public function add_action( string $handle, AbstractAction $action ) {
		$this->_actions[ $handle ] = $action;
	}


	public function run() {
		$actions                 = $this->get_actions();
		$enqueue_scripts_actions = array_unique(
			array_reduce(
				$actions,
				function ( array $enqueue_scripts_actions, AbstractAction $action ) {
					return array_merge( $enqueue_scripts_actions, $action->get_enqueue_scripts_actions() );
				},
				[]
			)
		);
		$verify_actions          = array_unique(
			array_reduce(
				$actions,
				function ( array $verify_actions, AbstractAction $action ) {
					return array_merge( $verify_actions, $action->get_verify_actions() );
				},
				[]
			)
		);

		foreach ( $enqueue_scripts_actions as $enqueue_scripts_action ) {
			add_action( $enqueue_scripts_action, [ $this, 'enqueue_scripts' ] );
		}

		foreach ( $verify_actions as $verify_action ) {
			add_action( $verify_action, [ $this, 'verify' ] );
		}

		foreach ( $actions as $action ) {
			$action->init();
		}

		$this->_fetch_ip_lists();

	}

	/**
	 * @return string
	 */
	public function get_api_script_url(): string {
		return add_query_arg( 'render', $this->get_key(), $this->_api_script_url );
	}

	protected function _fetch_ip_lists(){
		if ( ! wp_next_scheduled( INNOCODE_WP_RECAPTCHA . '_fetch_ip_lists' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', INNOCODE_WP_RECAPTCHA . '_fetch_ip_lists' );
		}

		add_action( INNOCODE_WP_RECAPTCHA . '_fetch_ip_lists', function () {
			static::refresh_ip_lists();
		} );
		$ips = (array) get_option( INNOCODE_WP_RECAPTCHA . '_combined_allowed_ips' );
		$this->set_allowed_ips( $ips );
	}

	public static function refresh_ip_lists(){

		$ip_lists = apply_filters( INNOCODE_WP_RECAPTCHA . '_allowed_ip_lists', [
			'cloudflare' => new CloudFlareIPList(),
			'constant'   => new ConfigConstantIPList(),
			'options'    => new OptionsIPList()
		] );
		$ips      = [];
		foreach ( $ip_lists as $ip_list ) {
			/**
			 * @var  $ip_list AbstractAllowIPList
			 */
			$list_ips = $ip_list->get_allowed_ips();
			if ( $list_ips ) {
				$ips = array_unique( array_merge( $ips, $list_ips ) );
			}
		}
		update_option( INNOCODE_WP_RECAPTCHA.'_combined_allowed_ips',$ips );

	}

	/**
	 * @return string
	 */
	public function get_key(): string {
		return $this->_key;
	}

	public function get_allowed_ips() {
		return $this->_allowed_ips;
	}


	public function set_allowed_ips(array $ips){
		$this->_allowed_ips = $ips;
	}


	/**
	 * @return ReCaptcha
	 */
	public function get_recaptcha(): ReCaptcha {
		return $this->_recaptcha;
	}

	/**
	 * @return Whip
	 */
	public function get_whip(): Whip {
		return $this->_whip;
	}

	/**
	 * @return AbstractAction[]
	 */
	public function get_actions(): array {
		return $this->_actions;
	}


	public function enqueue_scripts() {
		wp_enqueue_script(
			'innocode-recaptcha',
			$this->get_api_script_url(),
			[],
			null,
			true
		);
		wp_localize_script(
			'innocode-recaptcha',
			'innocodeRecaptcha',
			[
				'key' => $this->get_key(),
			]
		);
	}

	/**
	 * @param $ip_address string
	 *
	 * @return bool
	 */
	protected function is_allowed_ip( $ip_address ) {
		$allowed_ips = $this->get_allowed_ips();

		return $allowed_ips && in_array( $ip_address, $allowed_ips );
	}

	public function verify() {
		$verify_action = current_action();

		foreach ( $this->get_actions() as $action ) {
			if (
				! in_array( $verify_action, $action->get_verify_actions() ) ||
				! $action->can_process()
			) {
				continue;
			}

			$ip_address = (string) $this->get_whip()->getValidIpAddress();
			if ( ! ( $ip_address && $this->is_allowed_ip( $ip_address ) ) ) {
				$response = $this->get_recaptcha()
				                 ->setExpectedHostname( $action->get_host() )
				                 ->setExpectedAction( $action->get_type() )
				                 ->setScoreThreshold( $action->get_threshold() )
				                 ->verify( $action->get_response(), $ip_address );
				$action->process( $response, $ip_address );
			}
		}
	}
}
