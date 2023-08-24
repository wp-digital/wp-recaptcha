# Bot Protection

### Description

Helps to protect website with [Google reCAPTCHA v3](https://www.google.com/recaptcha/about/) or
[Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/).

### Install

- Preferable way is to use [Composer](https://getcomposer.org/):

    ````
    composer require wp-digital/wp-recaptcha
    ````

    By default, it will be installed as [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins).
    But it's possible to control with `extra.installer-paths` in `composer.json`.

- Alternate way is to clone this repo to `wp-content/mu-plugins/` or `wp-content/plugins/`:

    ````
    cd wp-content/plugins/
    git clone git@github.com:wp-digital/wp-recaptcha.git
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

Coming soon...
