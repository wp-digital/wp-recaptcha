<?php

namespace Innocode\ReCaptcha\Actions;

use Innocode\ReCaptcha\Abstracts\AbstractAction;
use Innocode\ReCaptcha\Helpers;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;
use Vectorface\Whip\Whip;
use WP_Error;
use WP_User;

/**
 * Class LoginFormAction
 * @package Innocode\ReCaptcha
 */
class LoginFormAction extends AbstractAction
{
    /**
     * @return string
     */
    public function get_host() : string
    {
        return (string) wp_parse_url( site_url( 'wp-login.php', 'login_post' ), PHP_URL_HOST );
    }

    /**
     * @return string
     */
    public function get_type() : string
    {
        return 'login';
    }

    /**
     * @return float
     */
    public function get_threshold() : float
    {
        return 0.5;
    }

    /**
     * @return string
     */
    public function get_response() : string
    {
        return isset( $_POST['recaptcha'] ) && is_string( $_POST['recaptcha'] ) ? $_POST['recaptcha'] : '';
    }

    /**
     * @return array
     */
    public function get_enqueue_scripts_actions() : array
    {
        $enqueue_scripts_actions = [];
        $action                  = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

        if ( in_array( $action, $this->get_actions() ) ) {
            $enqueue_scripts_actions[] = 'login_enqueue_scripts';
        }

        return $enqueue_scripts_actions;
    }

    /**
     * @return array
     */
    public function get_verify_actions() : array
    {
        return array_map( function ( $action ) {
            return "login_form_$action";
        }, $this->get_actions() );
    }

    public function init()
    {
        add_action( 'login_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_filter( 'login_message', [ $this, 'no_js_warning' ] );
        add_action( 'login_form', [ $this, 'print_field' ] );
        add_action( 'lostpassword_form', [ $this, 'print_field' ] );
        add_filter( 'login_form_middle', [ $this, 'get_field' ] );
        add_action( 'login_form_verification', [ $this, 'verification_action' ] );
        add_action( 'wp_login_errors', [ $this, 'add_verification_errors' ] );
        add_filter( 'login_body_class', [ $this, 'add_body_classes' ], 10, 2 );
        add_action( 'wp_login', [ $this, 'delete_verification_code' ], 10, 2 );
    }

    /**
     * @return bool
     */
    public function can_process() : bool
    {
        return Helpers::is_post_request();
    }

    /**
     * @param Response $response
     * @param string $ip_address
     */
    public function process( Response $response, string $ip_address )
    {
        global $action;

        $is_blocked_ip = Helpers::is_ip_blocked( $ip_address );
        if ( $response->isSuccess() && ! ( $action == 'login' && $is_blocked_ip ) ) {
            return;
        }

        $error_codes = $response->getErrorCodes();

        switch ( $action ) {
            case 'login':
                if ( in_array( ReCaptcha::E_MISSING_INPUT_RESPONSE, $error_codes ) ) {
                    add_filter( 'authenticate', [ $this, 'get_error_missing_input_response' ], 99 );

                    return;
                }
                Helpers::add_blocked_ip( $ip_address );

                add_filter( 'authenticate', [ $this, 'retrieve_verification_code' ], 99 );

                return;
            case 'lostpassword':
            case 'retrievepassword':
                if ( in_array( ReCaptcha::E_MISSING_INPUT_RESPONSE, $error_codes ) ) {
                    add_action( 'lostpassword_post', [ $this, 'add_error_missing_input_response' ] );

                    return;
                }

                add_action( 'lostpassword_post', [ $this, 'add_error_failed' ] );

                return;
        }
    }

    /**
     * @return array
     */
    public function get_forms_ids() : array
    {
        return apply_filters( 'innocode_recaptcha_login_forms_ids', [
            'loginform',
            'lostpasswordform',
        ] );
    }

    /**
     * @return array
     */
    public function get_actions() : array
    {
        return [
            'login',
            'lostpassword',
            'retrievepassword',
        ];
    }

    public function enqueue_scripts()
    {
        Helpers::enqueue_script( 'login', [
            'action' => $this->get_type(),
            'ids'    => $this->get_forms_ids(),
        ] );

        $selectors        = array_map( function ( string $id ) {
            return "#$id > *";
        }, $this->get_forms_ids() );
        $selector         = implode( ', ', $selectors );
        $loading_selector = implode( ', .innocode_recaptcha_loading ', $selectors );

        wp_add_inline_style( 'login', "$selector { transition: opacity 0.25s; }
.innocode_recaptcha_loading $loading_selector { opacity: 0; }" );
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function no_js_warning( string $message ) : string
    {
        $message .= sprintf( "<noscript>
    <p class=\"message\">%s</p>
</noscript>\n", __( 'The login form requires JavaScript. Please enable JavaScript in your browser settings.', 'innocode-recaptcha' ) );

        return $message;
    }

    public function print_field()
    {
        echo $this->get_field();
    }

    /**
     * @return string
     */
    public function get_field() : string
    {
        return '<input type="hidden" id="recaptcha" name="recaptcha">';
    }

    /**
     * @param null|WP_User|WP_Error $user
     *
     * @return null|WP_Error
     */
    public function get_error_missing_input_response( $user ) : WP_Error
    {
        if ( ! ( $user instanceof WP_User ) ) {
            return $user;
        }

        return new WP_Error(
            'innocode_recaptcha_missing_input_response',
            __( '<strong>ERROR</strong>: JavaScript is not enabled or you\'re a robot.', 'innocode-recaptcha' )
        );
    }

    /**
     * @param WP_Error $errors
     */
    public function add_error_missing_input_response( WP_Error $errors )
    {
        $errors->add(
            'innocode_recaptcha_missing_input_response',
            __( '<strong>ERROR</strong>: JavaScript is not enabled or you\'re a robot.', 'innocode-recaptcha' )
        );
    }

    /**
     * @param WP_Error $errors
     */
    public function add_error_failed( WP_Error $errors )
    {
        $errors->add(
            'innocode_recaptcha_failed',
            __( '<strong>ERROR</strong>: Failed reCAPTCHA verification. Please try again in case when you\'re not a robot.', 'innocode-recaptcha' )
        );
    }

    /**
     * @param null|WP_User|WP_Error $user
     *
     * @return null|WP_User|WP_Error
     */
    public function retrieve_verification_code( $user )
    {
        if ( ! ( $user instanceof WP_User ) ) {
            return $user;
        }

        $code = Helpers::generate_user_verification_code( $user->ID );

        if ( is_multisite() ) {
            $site_name = get_network()->site_name;
        } else {
            $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
        }

        $message = __( 'Someone is trying to sign in to the following account:', 'innocode-recaptcha' ) . "\r\n\r\n";
        $message .= sprintf( __( 'Site Name: %s', 'innocode-recaptcha' ), $site_name ) . "\r\n\r\n";
        $message .= sprintf( __( 'Username: %s', 'innocode-recaptcha' ), $user->user_login ) . "\r\n\r\n";
        $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'innocode-recaptcha' ) . "\r\n\r\n";
        $message .= __( 'To sign in, enter the following verification code into the input field:', 'innocode-recaptcha' ) . "\r\n\r\n";
        $message .= "$code\r\n";
        $title   = sprintf( __( '[%s] Verification Code', 'innocode-recaptcha' ), $site_name );
        $title   = apply_filters( 'innocode_recaptcha_retrieve_verification_code_title', $title, $user, 'email' );
        $message = apply_filters( 'innocode_recaptcha_retrieve_verification_code_message', $message, $code, $user, 'email' );

        if ( $message && ! wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message ) ) {
            wp_die( __( 'The email could not be sent. Possible reason: your host may have disabled the mail() function.' ) );
        }


        add_action( 'wp_login_failed', function ( string $username ) use ( $user ) {
            $key = get_password_reset_key( $user );
            $this->redirect_to_verification( 'email', $username, $key );
        } );

        return new WP_Error( 'innocode_recaptcha_retrieve_verification_code', '' );
    }

    /**
     * @param string $method
     * @param string $username
     * @param string $key
     */
    public function redirect_to_verification( string $method, string $username, string $key )
    {
        $redirect_to = add_query_arg(
            [
                'method' => $method,
                'login'  => rawurlencode( $username ),
                'key'    => $key,
            ],
            site_url( "wp-login.php?action=verification" )
        );

        if ( ! empty( $_POST['rememberme'] ) ) {
            $redirect_to = add_query_arg( 'rememberme', 'forever', $redirect_to );
        }

        if ( isset( $_REQUEST['redirect_to'] ) ) {
            $redirect_to = add_query_arg( 'redirect_to', urlencode( $_REQUEST['redirect_to'] ), $redirect_to );
        }

        wp_redirect( $redirect_to );
        exit;
    }

    public function verification_action()
    {
        $method      = isset( $_REQUEST['method'] ) ? $_REQUEST['method'] : '';
        $redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
        $rp_cookie   = 'wp-resetpass-' . COOKIEHASH;
        $user        = false;
        $hash        = '';

        if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
            list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
            $user = check_password_reset_key( $rp_key, $rp_login );

            if ( ! is_wp_error( $user ) ) {
                $hash = Helpers::get_user_verification_code( $user->ID );
            }
        }

        if ( $method != 'email' || ! $user || ! $hash ) {
            Helpers::clear_rp_cookie();
            wp_redirect( wp_login_url( $redirect_to ) );
            exit;
        }

        $rememberme = ! empty( $_REQUEST['rememberme'] );

        if ( ! Helpers::is_post_request() ) {
            $errors = new WP_Error();
            $errors->add( 'sent', sprintf(
                __( 'A verification code was sent via %s to <strong>%s</strong>.', 'innocode-recaptcha' ),
                'email',
                $user->user_login
            ), 'message' );

            login_header( __( 'Enter Verification Code', 'innocode-recaptcha' ), '', $errors );

            $file = $this->get_view_file( 'verification.php' );
            require_once $file;

            login_footer();
            exit;
        }

        $code = isset( $_POST['code'] ) && is_string( $_POST['code'] ) ? $_POST['code'] : '';
        $code = Helpers::validate_verification_code( $code, $hash );
        Helpers::delete_user_verification_code( $user->ID );
        Helpers::clear_rp_cookie();

        if ( is_wp_error( $code ) ) {
            wp_redirect( add_query_arg(
                'verification',
                str_replace( '_', '', $code->get_error_code() ),
                wp_login_url( $redirect_to )
            ) );
            exit;
        }

        wp_set_auth_cookie( $user->ID, $rememberme );

        // remove blocked ip on success verification
        $ip_address = (string) ( new Whip() )->getValidIpAddress();
        Helpers::remove_blocked_ip( $ip_address );

        if ( $redirect_to && is_ssl() && false !== strpos( $redirect_to, 'wp-admin' ) ) {
            $redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
        }

        $requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
        $redirect_to           = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

        if ( $redirect_to ) {
            wp_safe_redirect( $redirect_to );
            exit;
        }

        switch ( true ) {
            case is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ):
                wp_redirect( user_admin_url() );
                exit;
            case is_multisite() && ! $user->has_cap( 'read' ):
                wp_redirect( get_dashboard_url( $user->ID ) );
                exit;
            case ! $user->has_cap( 'edit_posts' ):
                wp_redirect(
                    $user->has_cap( 'read' )
                        ? admin_url( 'profile.php' )
                        : home_url()
                );
                exit;
            default:
                wp_redirect( admin_url() );
                exit;
        }
    }

    /**
     * @param WP_Error $errors
     *
     * @return WP_Error
     */
    public function add_verification_errors( WP_Error $errors ) : WP_Error
    {
        if ( ! isset( $_GET['verification'] ) ) {
            return $errors;
        }

        switch ( $_GET['verification'] ) {
            case 'invalidverificationcode':
                $errors->add(
                    'invalid_verification_code',
                    __( '<strong>ERROR</strong>: Your verification code appears to be invalid. Please try again.', 'innocode-recaptcha' )
                );

                break;
            case 'expiredverificationcode':
                $errors->add(
                    'expired_verification_code',
                    __( '<strong>ERROR</strong>: Your verification code has expired. Please try again.', 'innocode-recaptcha' )
                );

                break;
        }

        return $errors;
    }

    /**
     * @param array $classes
     * @param string $action
     *
     * @return array
     */
    public function add_body_classes( array $classes, string $action )
    {
        if ( ! in_array( $action, $this->get_actions() ) ) {
            return $classes;
        }

        $classes[] = 'innocode_recaptcha_loading';

        return $classes;
    }

    /**
     * @param string $user_login
     * @param WP_User $user
     */
    public function delete_verification_code( string $user_login, WP_User $user )
    {
        Helpers::delete_user_verification_code( $user->ID );
    }

}
