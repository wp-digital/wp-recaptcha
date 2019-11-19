# reCAPTCHA

### Description

Helps to protect website with [Google reCAPTCHA v3](https://www.google.com/recaptcha/intro/v3.html).

### Install

- Preferable way is to use [Composer](https://getcomposer.org/):

    ````
    composer require innocode-digital/wp-recaptcha
    ````

    By default it will be installed as [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins).
    But it's possible to control with `extra.installer-paths` in `composer.json`.

- Alternate way is to clone this repo to `wp-content/mu-plugins/` or `wp-content/plugins/`:

    ````
    cd wp-content/plugins/
    git clone git@github.com:innocode-digital/wp-recaptcha.git
    cd wp-recaptcha/
    composer install
    ````

If plugin was installed as regular plugin then activate **reCAPTCHA** from Plugins page 
or [WP-CLI](https://make.wordpress.org/cli/handbook/): `wp plugin activate wp-recaptcha`.

### Usage

Add required constants (usually to `wp-config.php`):

````
define( 'RECAPTCHA_KEY', '' );
define( 'RECAPTCHA_SECRET', '' );
````
    
### Documentation

It's possible to change length of verification code:

````
add_filter( 'innocode_recaptcha_verification_code_length', function ( $length ) {
    return $length; // Default is 6.
} );
````

It's possible to change lifetime of verification code:
    
````
add_filter( 'innocode_recaptcha_verification_code_lifetime', function ( $lifetime ) {
    return $lifetime; // In seconds. Default is 15 minutes (15 * MINUTE_IN_SECONDS).
} );
````

It's possible to change title of verification code message:

````
add_filter( 'innocode_recaptcha_retrieve_verification_code_title', function ( $title, $user, $type ) {
    return $title;
}, 10, 3 );
````

It's possible to change verification code message body:

````
add_filter( 'innocode_recaptcha_retrieve_verification_code_message', function ( $message, $user, $type ) {
    return $message;
}, 10, 3 );
````

### Advanced Usage

It's possible to use ReCAPTCHA on custom rendered login forms with
[wp_login_form()](https://developer.wordpress.org/reference/functions/wp_login_form/):

````
add_action( 'wp_enqueue_scripts', [ innocode_recaptcha(), 'enqueue_scripts' ] );
add_action( 'wp_enqueue_scripts', [ innocode_recaptcha()->get_actions()['login'], 'enqueue_scripts' ] );
````

If it's needed to change form id then this id should be added to action forms ids list:

````
add_action( 'innocode_recaptcha_login_forms_ids', function ( $ids ) {
    $ids[] = 'custom-login-form';
    
    return $ids;
} );
````

It's possible to use ReCAPTCHA on custom forms:

`ExampleFormAction.php`

````
<?php

use Innocode\ReCaptcha\Abstracts\AbstractAction;

class ExampleFormAction extends AbstractAction
{
    // @TODO: Implement \Innocode\ReCaptcha\Interfaces\ActionInterface
}
````
`functions.php`
````
add_action( 'init', function () {
    innocode_recaptcha()->add_action( 'example', new ExampleFormAction() );
}, 1 );
````
