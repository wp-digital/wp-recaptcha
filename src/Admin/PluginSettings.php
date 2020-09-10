<?php

namespace Innocode\ReCaptcha\Admin;


use Innocode\ReCaptcha\Plugin;

/**
 * Class PluginSettings.php
 * @package Innocode\ReCaptcha\Admin
 */
class PluginSettings
{
// add flush button  here
// add method for adding textarea from OptionsIPList

	/**
	 * @var OptionsPage
	 */
	private $_options_page;


	/**
	 * OptionsIPList constructor.
	 */
	public function __construct()
	{

		$this->_init_options_page();
		$this->_init_options();
		$this->_run_hooks();


	}

	protected function _run_hooks()
	{

		add_action( 'admin_menu', function () {
			$this->_add_options_page();
		} );
		add_action( 'admin_init', function () {
			$this->_add_sections();
			$this->_add_fields();
		} );
		add_action( 'wp_ajax_' . INNOCODE_WP_RECAPTCHA . '_refresh_allowed_ips', function () {
			Plugin::refresh_ip_lists();
			wp_send_json_success();

		} );
		add_action( 'current_screen', function () {
			if ( get_current_screen()->id == 'settings_page_' . INNOCODE_WP_RECAPTCHA ) {
				add_action( 'admin_enqueue_scripts', function () {
					wp_enqueue_script( INNOCODE_WP_RECAPTCHA . '_admin', plugins_url( "public/js/admin.js", INNOCODE_WP_RECAPTCHA_FILE ) );
				} );
			}
		} );

	}

	/**
	 * @return string
	 */
	public static function get_ips_option_name()
	{
		return INNOCODE_WP_RECAPTCHA . '_ips_option';
	}

	public static function get_options_page_slug()
	{
		return INNOCODE_WP_RECAPTCHA;
	}


	/**
	 * @return OptionsPage
	 */
	public function get_options_page()
	{
		return $this->_options_page;
	}

	private function _init_options_page()
	{
		$this->_options_page = new OptionsPage(
			static::get_options_page_slug(),
			static::get_options_page_slug(),
			__( 'Recaptcha Settings', 'innocode-recaptcha' )
		);
		$this->_init_button_actions();
		$this->_options_page->set_menu_title( __( 'Recaptcha', 'innocode-recaptcha' ) );
	}

	private function _init_button_actions()
	{
		$buttons = apply_filters( INNOCODE_WP_RECAPTCHA . '_admin_button_actions', [ 'refresh_allowed_ips' => __( 'Refresh ips', 'innocode-recaptcha' ) ] );
		if ( $buttons ) {
			foreach ( $buttons as $key => $title ) {
				$this->_options_page->add_button( $key, $title );
			}
		}
	}

	private function _init_options()
	{
		$options_page                 = $this->get_options_page();
		$options_page_name            = $options_page->get_name();
		$section_name                 = $options_page_name . '_ips';
		$section                      = new Section( $section_name, __( 'Allowed IPs', 'innocode-recaptcha' ) );
		$setting                      = new Setting( static::get_ips_option_name(), __( 'Allowed Ips', 'innocode-recaptcha' ) );
		$setting->sanitize_callback = 'sanitize_textarea_field';
		$field                        = new Field();
		$field->set_type( 'textarea' );
		$field->set_description( 'add one IP per row' );
		$field->set_setting( $setting );
		$section->add_field( $field );
		$options_page->add_section( $section );

	}


	private function _add_options_page()
	{
		$options_page = $this->get_options_page();

		add_options_page(
			$options_page->get_title(),
			$options_page->get_menu_title(),
			$options_page->get_capability(),
			$options_page->get_menu_slug(),
			[ $options_page, 'render' ]
		);
	}


	private function _add_sections()
	{
		$options_page      = $this->get_options_page();
		$options_page_slug = $options_page->get_menu_slug();

		foreach ( $options_page->get_sections() as $section ) {
			add_settings_section(
				$section->get_name(),
				$section->get_title(),
				null,
				$options_page_slug
			);
		}
	}

	private function _add_fields()
	{
		$options_page      = $this->get_options_page();
		$options_page_slug = $options_page->get_menu_slug();

		foreach ( $options_page->get_sections() as $section ) {
			$section_name = $section->get_name();

			foreach ( $section->get_fields() as $field ) {
				$setting      = $field->get_setting();
				$setting_name = $setting->get_name();

				register_setting( $options_page_slug, $setting_name, $setting->get_args() );
				add_settings_field(
					$setting_name,
					$setting->get_title(),
					function () use ( $field ) {
						echo $field->get_html();
					},
					$options_page_slug,
					$section_name,
					[
						'label_for' => $setting_name,
					]
				);
			}
		}
	}


}