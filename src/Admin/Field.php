<?php

namespace Innocode\ReCaptcha\Admin;

/**
 * Class Field
 * @package Innocode\ReCaptcha\Admin
 */
class Field
{
    /**
     * @var Setting
     */
    protected $_setting;
    /**
     * @var string
     */
    protected $_type = 'text';
    /**
     * @var string
     */
    protected $_id;
    /**
     * @var array
     */
    protected $_attrs = [];
    /**
     * @var callable
     */
    protected $_callback;
    /**
     * @var string
     */
    protected $_description;

    /**
     * @return Setting
     */
    public function get_setting()
    {
        return $this->_setting;
    }

    /**
     * @param Setting $setting
     */
    public function set_setting( Setting $setting )
    {
        $this->_setting = $setting;
    }

    /**
     * @return string
     */
    public function get_type()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function set_type( $type )
    {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function get_id()
    {
        return $this->_id;
    }

    /**
     * @param string $id
     */
    public function set_id( $id )
    {
        $this->_id = $id;
    }

    /**
     * @return array
     */
    public function get_attrs()
    {
        return wp_parse_args( $this->_attrs, [
            'type' => 'text',
        ] );
    }

    /**
     * @param array $attrs
     */
    public function set_attrs( array $attrs )
    {
        $this->_attrs = $attrs;
    }

    /**
     * @return string
     */
    public function get_attrs_html()
    {
        return implode( ' ', array_map( function ( $name, $value ) {
            return esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
        }, array_keys( $this->_attrs ), $this->_attrs ) );
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function add_attr( $name, $value )
    {
        $this->_attrs[ $name ] = $value;
    }

    /**
     * @return callable
     */
    public function get_callback()
    {
        return $this->_callback;
    }

    /**
     * @param callable $callback
     */
    public function set_callback( callable $callback )
    {
        $this->_callback = $callback;
    }

    /**
     * @return string
     */
    public function get_description()
    {
        return $this->_description;
    }

    /**
     * @param string $description
     */
    public function set_description( $description )
    {
        $this->_description = $description;
    }

    public function get_html()
    {
        $callback = $this->get_callback();

        if ( is_callable( $callback ) ) {
            return $callback( $this );
        }

        $setting = $this->get_setting();
        $type = $this->get_type();
        $name = $setting->get_name();
        $value = $setting->get_value();
        $attrs = $this->get_attrs_html();

        switch ( $type ) {
            case 'textarea':
                $html = sprintf(
                    '<textarea id="%s" name="%s" cols="45" rows="5" %s>%s</textarea>',
                    esc_attr( $name ),
                    esc_attr( $name ),
                    $attrs,
                    esc_html( $value )
                );
                break;
            default:
                $html = sprintf(
                    "<input id=\"%s\" type=\"%s\" name=\"%s\" value=\"%s\" class=\"regular-text\" %s>",
                    esc_attr( $name ),
                    esc_attr( $type ),
                    esc_attr( $name ),
                    esc_attr( $value ),
                    $attrs
                );
                break;
        }

        $description = $this->get_description();

        if ( $description ) {
            $html = "<p class=\"description\">$description</p>";
        }

        return $html;
    }
}
