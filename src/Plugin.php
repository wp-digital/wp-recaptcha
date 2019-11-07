<?php

namespace Innocode\ReCaptcha;

use Innocode\ReCaptcha\Abstracts\AbstractAction;
use Innocode\ReCaptcha\Actions\LoginFormAction;
use ReCaptcha\ReCaptcha;
use Vectorface\Whip\Whip;

/**
 * Class Plugin
 * @package Innocode\ReCaptcha
 */
final class Plugin
{
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
     * Plugin constructor.
     */
    public function __construct()
    {
        if ( defined( 'RECAPTCHA_API_SCRIPT_URL' ) ) {
            $this->_api_script_url = RECAPTCHA_API_SCRIPT_URL;
        }

        $this->_key = defined( 'RECAPTCHA_KEY' ) ? RECAPTCHA_KEY : '';
        $this->_secret = defined( 'RECAPTCHA_SECRET' ) ? RECAPTCHA_SECRET : '';
        $this->_recaptcha = new ReCaptcha( $this->_secret );
        $this->_whip = new Whip();
        $this->_actions[] = new LoginFormAction();
    }

    public function run()
    {
        $actions = $this->get_actions();
        $enqueue_scripts_actions = array_unique(
            array_reduce(
                $actions,
                function ( array $enqueue_scripts_actions, AbstractAction $action ) {
                    return array_merge( $enqueue_scripts_actions, $action->get_enqueue_scripts_actions() );
                },
                []
            )
        );
        $verify_actions = array_unique(
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
    }

    /**
     * @return string
     */
    public function get_api_script_url() : string
    {
        return add_query_arg( 'render', $this->get_key(), $this->_api_script_url );
    }

    /**
     * @return string
     */
    public function get_key() : string
    {
        return $this->_key;
    }

    /**
     * @return ReCaptcha
     */
    public function get_recaptcha() : ReCaptcha
    {
        return $this->_recaptcha;
    }

    /**
     * @return Whip
     */
    public function get_whip() : Whip
    {
        return $this->_whip;
    }

    /**
     * @return AbstractAction[]
     */
    public function get_actions() : array
    {
        return $this->_actions;
    }

    public function enqueue_scripts()
    {
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

    public function verify()
    {
        $verify_action = current_action();

        foreach ( $this->get_actions() as $action ) {
            if (
                ! in_array( $verify_action, $action->get_verify_actions() ) ||
                ! $action->can_process()
            ) {
                continue;
            }

            $ip_address = (string) $this->get_whip()->getValidIpAddress();
            $response = $this->get_recaptcha()
                ->setExpectedHostname( $action->get_host() )
                ->setExpectedAction( $action->get_type() )
                ->setScoreThreshold( $action->get_threshold() )
                ->verify( $action->get_response(), $ip_address );
            $action->process( $response, $ip_address );
        }
    }
}
