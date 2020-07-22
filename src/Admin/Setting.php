<?php

namespace Innocode\ReCaptcha\Admin;

use InvalidArgumentException;

/**
 * Class Setting
 * @property string   $type
 * @property string   $description
 * @property callable $sanitize_callback
 * @property bool     $show_in_rest
 * @property mixed    $default
 * @package Innocode\ReCaptcha\Admin
 */
class Setting
{
    /**
     * @var string
     */
    protected $_name;
    /**
     * @var string
     */
    protected $_title;
    /**
     * @var array
     */
    protected $_args = [];

    /**
     * Setting constructor.
     * @param string $name
     * @param string $title
     */
    public function __construct( $name, $title )
    {
        $this->_name = $name;
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set( $name, $value )
    {
        $this->_args[ $name ] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( array_key_exists( $name, $this->_args ) ) {
            return $this->_args[ $name ];
        }

        throw new InvalidArgumentException(
            sprintf(
                'Property %s doesn\'t exist in class %s',
                $name,
                get_class( $this )
            )
        );
    }

    /**
     * @return array
     */
    public function get_args()
    {
        return wp_parse_args( $this->_args, [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ] );
    }

    /**
     * @return mixed
     */
    public function get_value()
    {
        return get_option( $this->get_name() );
    }
}
