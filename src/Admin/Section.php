<?php

namespace Innocode\ReCaptcha\Admin;

/**
 * Class Section
 * @package Innocode\ReCaptcha\Admin
 */
class Section {
	/**
	 * @var string
	 */
	protected $_name;
	/**
	 * @var string
	 */
	protected $_title;
	/**
	 * @var Field[]
	 */
	protected $_fields = [];

	/**
	 * Section constructor.
	 *
	 * @param string $name
	 * @param string $title
	 */
	public function __construct( $name, $title ) {
		$this->_name  = $name;
		$this->_title = $title;
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
	public function get_title() {
		return $this->_title;
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		return $this->_fields;
	}

	/**
	 * @param string $name
	 * @param Field $field
	 */
	public function add_field( Field $field ) {
		$this->_fields[] = $field;
	}
}
