<?php
/*
Plugin Name: ReCaptcha
Description:
Version: 1.1
Plugin URI: https://github.com/shtrihstr/wp-recaptcha
Author: Oleksandr Strikha
Author URI: https://github.com/shtrihstr
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if( defined( 'RECAPTCHA_KEY' ) && defined( 'RECAPTCHA_SECRET' ) ) {


    function recaptcha_get_script() {
        return "<script src='https://www.google.com/recaptcha/api.js' async defer></script>\n";
    }

    function recaptcha_print_script() {
        echo recaptcha_get_script();
    }

    function recaptcha_print_login_footer() {
        echo "<style>.g-recaptcha { transform: scale(0.90, 0.90) translate(-15px, 0); }</style>\n";
        echo "<script>
        var recaptchaLoginBtn = document.querySelector('#loginform #wp-submit');
        if( recaptchaLoginBtn ) {
            recaptchaLoginBtn.style.display = 'none';
        }
        function recaptchaSuccess() {
            if( recaptchaLoginBtn ) {
                recaptchaLoginBtn.style.display = 'block';
            }
        }
        </script>\n";
    }

    function recaptcha_get_field() {
        return "<div class='g-recaptcha' data-sitekey='" . esc_attr( RECAPTCHA_KEY ) . "' data-callback='recaptchaSuccess' style='padding: 0 0 20px'></div>\n";
    }

    function recaptcha_print_field() {
        echo recaptcha_get_field();
    }

    function recaptcha_validate() {
        if( ! empty( $_POST['log'] ) ) {

            $valid_captcha = false;

            if( isset( $_POST[ 'g-recaptcha-response' ] ) ) {
                $form_response = $_POST[ 'g-recaptcha-response' ];

                $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => RECAPTCHA_SECRET,
                        'response' => $form_response,
                        'remoteip' => $_SERVER[ 'REMOTE_ADDR' ],
                    ]
                ] );

                $json = wp_remote_retrieve_body( $response );
                $data = json_decode( $json, true );

                if( $data && isset( $data['success'] ) && true === $data['success'] && $data['hostname'] === $_SERVER[ 'HTTP_HOST' ] ) {
                    $valid_captcha = true;
                }

            }

            if( ! $valid_captcha ) {
                unset( $_POST['log'] );
                add_filter( 'wp_login_errors', function( WP_Error $errors ) {
                    $errors->remove( 'empty_username' );
                    $errors->add( 'captcha', __( 'Please confirm you\'re not a robot.', 'recaptcha' ) );
                    return $errors;
                } );
            }
        }
    }

    add_action( 'login_head', 'recaptcha_print_script' );
    add_action( 'login_footer', 'recaptcha_print_login_footer' );
    add_action( 'login_form_top', 'recaptcha_get_script' );
    add_filter( 'login_form_middle', 'recaptcha_get_field' );
    add_action( 'login_form', 'recaptcha_print_field');
    add_action( 'login_form_login', 'recaptcha_validate' );

}