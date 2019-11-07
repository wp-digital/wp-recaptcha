<?php

namespace Innocode\ReCaptcha\Interfaces;

use ReCaptcha\Response;

/**
 * Interface ActionInterface
 * @package Innocode\ReCaptcha\Interfaces
 */
interface ActionInterface
{
    /**
     * @return string
     */
    public function get_host() : string;

    /**
     * @return string
     */
    public function get_type() : string;

    /**
     * @return float
     */
    public function get_threshold() : float;

    /**
     * @return string
     */
    public function get_response() : string;

    /**
     * @return array
     */
    public function get_enqueue_scripts_actions() : array;

    /**
     * @return array
     */
    public function get_verify_actions() : array;

    public function init();

    /**
     * @return bool
     */
    public function can_process() : bool;

    /**
     * @param Response $response
     * @param string $ip_address
     */
    public function process( Response $response, string $ip_address );
}
