<?php

namespace Innocode\ReCaptcha\Admin;

/**
 * Class OptionsPage
 * @package Innocode\ReCaptcha\Admin
 */
class OptionsPage {
	/**
	 * @var string
	 */
	protected $_name;
	/**
	 * @var string
	 */
	protected $_menu_slug;
	/**
	 * @var array
	 */
	protected $_buttons=[];
	/**
	 * @var string
	 */
	protected $_title;
	/**
	 * @var string
	 */
	protected $_menu_title;
	/**
	 * @var string
	 */
	protected $_capability = 'manage_options';
	/**
	 * @var string
	 */
	protected $_view;
	/**
	 * @var Section[]
	 */
	protected $_sections = [];

	/**
	 * OptionsPage constructor.
	 *
	 * @param string $name
	 * @param string $menu_slug
	 * @param string $title
	 */
	public function __construct( $name, $menu_slug, $title ) {
		$this->_name       = $name;
		$this->_menu_slug  = $menu_slug;
		$this->_title      = $title;
		$this->_menu_title = $title;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function get_menu_slug() {
		return $this->_menu_slug;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->_title;
	}

	/**
	 * @return string
	 */
	public function get_menu_title() {
		return $this->_menu_title;
	}

	/**
	 * @param string $menu_title
	 */
	public function set_menu_title( $menu_title ) {
		$this->_menu_title = $menu_title;
	}

	/**
	 * @return string
	 */
	public function get_capability() {
		return $this->_capability;
	}

	public function get_buttons(){
		return $this->_buttons;
	}

	public function add_button( $key, $title ){
		$this->_buttons[ sanitize_key( $key ) ] = $title;
	}

	/**
	 * @param string $capability
	 */
	public function set_capability( $capability ) {
		$this->_capability = $capability;
	}


	public function render() {
		print '<div class="wrap">';
		print "<h2>{$this->_title}</h2>";
		$this->_render_buttons();
		print '<form action="' . admin_url( 'options.php' ) . '" method="post" enctype="modules/x-www-form-urlencoded">';
		settings_fields( $this->get_name() );
		do_settings_sections( $this->get_menu_slug() );
		submit_button();
		print '</form>';
		print '</div>';
	}


	/**
	 * @return Section[]
	 */
	public function get_sections() {
		return $this->_sections;
	}

	/**
	 * @param string $name
	 * @param Section $section
	 */
	public function add_section( Section $section ) {
		$this->_sections[] = $section;
	}

	/**
	 * @param int|null $blog_id
	 *
	 * @return string
	 */
	public function get_admin_url( $blog_id = null ) {
		return get_admin_url( $blog_id, "options-general.php?page={$this->get_menu_slug()}" );
	}

	private function _render_buttons(){
		$buttons = $this->get_buttons();
		if ( $buttons ) {
		    print '<table class="form-table">';
		    print '<tbody>';
			foreach ( $buttons as $key => $title ) { ?>
                <tr>
                    <th scope="row"><?=$title?></th>
                    <td>
                        <button type="button" data-role="innocode_recaptcha_action_button"
                                data-action="<?= INNOCODE_WP_RECAPTCHA . '_' . $key ?>"
                                class="button">Run Action</button>
                        <span class="spinner" style="float: none"></span>
                    </td>
                </tr>
			<?php }
			print '</tbody>';
			print '</table>';

		}

	}

}
