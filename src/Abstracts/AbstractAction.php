<?php

namespace Innocode\ReCaptcha\Abstracts;

use Innocode\ReCaptcha\Interfaces\ActionInterface;

/**
 * Class AbstractAction
 * @package Innocode\ReCaptcha\Abstracts
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * @return bool
     */
    public function can_process() : bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function get_dir()
    {
        return dirname( INNOCODE_WP_RECAPTCHA_FILE );
    }

    /**
     * @return string
     */
    public function get_views_dir() : string
    {
        return "{$this->get_dir()}/resources/views";
    }
    /**
     * @param string $name
     * @return string
     */
    public function get_view_file( string $name ) : string
    {
        return "{$this->get_views_dir()}/$name";
    }
}
