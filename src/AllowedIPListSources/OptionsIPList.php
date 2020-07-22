<?php

namespace Innocode\ReCaptcha\AllowedIPListSources;

use Innocode\ReCaptcha\Admin\Field;
use Innocode\ReCaptcha\Admin\OptionsPage;
use Innocode\ReCaptcha\Abstracts\AbstractAllowIPList;
use Innocode\ReCaptcha\Admin\Section;
use Innocode\ReCaptcha\Admin\Setting;

/**
 * Class OptionsList.php
 * @package Innocode\ReCaptcha\AllowedIPListSourcces
 */
class OptionsIPList extends AbstractAllowIPList {


	/**
	 * @var OptionsPage
	 */
	private $_options_page;


	/**
	 * OptionsIPList constructor.
	 */
	public function __construct() {

		$this->_init_options_page();
		$this->_init_sections();
		$this->_init_fields();
		$this->_run_hooks();
	}

	protected function _run_hooks() {

		add_action( 'admin_menu', function () {
			$this->_add_options_page();
		} );
		add_action( 'admin_init', function () {
			$this->_add_sections();
			$this->_add_fields();
		} );
	}

	/**
	 * @return string
	 */
	protected function _get_ips_option_name() {
		return INNOCODE_WP_RECAPTCHA . '_ips_option';
	}


	/**
	 * @return OptionsPage
	 */
	public function get_options_page() {
		return $this->_options_page;
	}

	private function _init_options_page() {
		$this->_options_page = new OptionsPage(
			INNOCODE_WP_RECAPTCHA,
			INNOCODE_WP_RECAPTCHA,
			__( 'Recaptcha Settings', 'innocode-recaptcha' )
		);
		$this->_options_page->set_menu_title( __( 'Recaptcha', 'innocode-recaptcha' ) );
	}


	private function _init_sections() {
		$options_page      = $this->get_options_page();
		$options_page_name = $options_page->get_name();
		$section_name      = $options_page_name . '_ips';
		$section           = new Section( $section_name, __( 'Allowed IPs', 'innocode-recaptcha' ) );
		$options_page->add_section( $section );

	}

	private function _init_fields() {
		$options_page = $this->get_options_page();
		$sections     = $options_page->get_sections();

		$setting = new Setting( $this->_get_ips_option_name(), __( 'Allowed Ips', 'innocode-recaptcha' ) );
		$field   = new Field();
		$field->set_type( 'textarea' );
		$field->set_description( 'add one IP per row' );
		$field->set_setting( $setting );
		$sections[0]->add_field( $field );
	}


	private function _add_options_page() {
		$options_page = $this->get_options_page();

		add_options_page(
			$options_page->get_title(),
			$options_page->get_menu_title(),
			$options_page->get_capability(),
			$options_page->get_menu_slug(),
			[ $options_page, 'render' ]
		);
	}

	private function _add_sections() {
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

	private function _add_fields() {
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

	/**
	 * @return mixed
	 */
	protected function _get_option_value() {
		return get_option( $this->_get_ips_option_name() );
	}

	/**
	 * @return array
	 */
	public function get_allowed_ips(): array {
		$ips = $this->_get_option_value();

		if ( $ips && is_string( $ips ) ) {
			return array( explode( "\n", trim( $ips ) ) );
		}

		return [];
	}


}