<?php

namespace Innocode\ReCaptcha;

use PasswordHash;
use WP_Error;

/**
 * Class Helpers
 * @package Innocode\ReCaptcha
 */
class Helpers
{
    /**
     * @return bool
     */
    public static function is_post_request() : bool
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param int $user_id
     * @return string
     */
    public static function generate_user_verification_code( int $user_id ) : string
    {
        $length = static::get_verification_code_length();
        $code = wp_generate_password( $length, false );
        $hashed = time() . ':' . wp_hash_password( $code );
        update_user_meta( $user_id, 'verification_code', $hashed );

        return $code;
    }

    /**
     * @return string
     */
    public static function get_verification_code_length() : string
    {
        return apply_filters( 'innocode_recaptcha_verification_code_length', 6 );
    }

    /**
     * @param int $user_id
     * @return string
     */
    public static function get_user_verification_code( int $user_id ) : string
    {
        return (string) get_user_meta( $user_id, 'verification_code', true );
    }

    /**
     * @param int $user_id
     */
    public static function delete_user_verification_code( int $user_id )
    {
        delete_user_meta( $user_id, 'verification_code' );
    }

    /**
     * @param string $code
     * @param string $hash
     * @return string|WP_Error
     */
    public static function validate_verification_code( string $code, string $hash )
    {
        global $wp_hasher;

        if ( empty( $wp_hasher ) ) {
            require_once( ABSPATH . WPINC . '/class-phpass.php' );
            $wp_hasher = new PasswordHash( 8, true );
        }

        if ( false === strpos( $hash, ':' ) ) {
            return new WP_Error( 'invalid_verification_code', __( 'Invalid verification code.', 'innocode-recaptcha' ) );
        }

        list( $hash_time, $hashed_code ) = explode( ':', $hash, 2 );

        if ( ! $code || ! $wp_hasher->CheckPassword( $code, $hashed_code ) ) {
            return new WP_Error( 'invalid_verification_code', __( 'Invalid verification code.', 'innocode-recaptcha' ) );
        }

        if ( $hash_time < time() - static::get_verification_code_lifetime() ) {
            return new WP_Error( 'expired_verification_code', __( 'Invalid verification code.', 'innocode-recaptcha' ) );
        }

        return $code;
    }

    /**
     * @return int
     */
    public static function get_verification_code_lifetime() : int
    {
        return apply_filters( 'innocode_recaptcha_verification_code_lifetime', 15 * MINUTE_IN_SECONDS );
    }

    public static function clear_rp_cookie()
    {
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
        setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
    }

    /**
     * @param string $handle
     * @param array  $settings
     */
    public static function enqueue_script( string $handle, array $settings = [] )
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $name = "innocode-recaptcha-$handle";
        wp_enqueue_script(
            $name,
            plugins_url( "public/js/$handle$suffix.js", INNOCODE_WP_RECAPTCHA_FILE ),
            [],
            INNOCODE_WP_RECAPTCHA_VERSION,
            true
        );
        wp_localize_script(
            $name,
            'innocodeRecaptcha' . static::dash_to_camel( $handle ),
            $settings
        );
    }

    /**
     * @param string $str
     * @return string
     */
    public static function dash_to_camel( string $str ) : string
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace( '-', ' ', $str )
            )
        );
    }
}
