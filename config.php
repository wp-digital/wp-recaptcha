<?php

use WPD\Recaptcha;

return [
	'wpd.recaptcha.path'                       => plugin_dir_path( __FILE__ ),
	'wpd.recaptcha.key'                        => WPD_RECAPTCHA_KEY,
	'wpd.recaptcha.secret'                     => WPD_RECAPTCHA_SECRET,
	'wpd.recaptcha.allowed_ips'                => array_filter(
		explode( ',', WPD_RECAPTCHA_ALLOWED_IPS )
	),
	'wpd.recaptcha.admin_page'                 => 'wpd_recaptcha',
	'wpd.recaptcha.allowed_ips_option'         => 'wpd_recaptcha_allowed_ips',
	'wpd.recaptcha.type'                       => preg_match( '/^\dx/', WPD_RECAPTCHA_KEY )
		? 'turnstile'
		: 'recaptcha',
	'wpd.recaptcha.provider.script_url'        => static fn ( DI\Container $container ): string =>
	$container->get( 'wpd.recaptcha.type' ) === 'turnstile'
		? 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback'
		: 'https://www.google.com/recaptcha/api.js',
	'wpd.recaptcha.validation.url'             => static fn ( DI\Container $container ): string =>
	$container->get( 'wpd.recaptcha.type' ) === 'turnstile'
		? 'https://challenges.cloudflare.com/turnstile/v0'
		: 'https://www.google.com/recaptcha/api',
	'wpd.recaptcha.challenge_ttl'              => 300,

	Recaptcha\Providers\Provider::class        => DI\autowire()
		->constructor(
			DI\get( 'wpd.recaptcha.key' ),
			DI\get( 'wpd.recaptcha.provider.script_url' )
		),
	Recaptcha\Providers\Service::class         => static fn ( DI\Container $container ): Recaptcha\Providers\Service =>
	$container->get( 'wpd.recaptcha.type' ) === 'turnstile'
		? $container->get( Recaptcha\Providers\Turnstile::class )
		: $container->get( Recaptcha\Providers\Recaptcha::class ),

	'WPD\Recaptcha\Whip'                       => DI\autowire( Vectorface\Whip\Whip::class ),

	Recaptcha\AllowedLists\Permanent::class    => DI\autowire()
		->constructor( DI\get( 'wpd.recaptcha.allowed_ips' ) ),
	Recaptcha\AllowedLists\Configurable::class => DI\autowire()
		->constructor( DI\get( 'wpd.recaptcha.allowed_ips_option' ) ),

	Recaptcha\Firewall::class                  => DI\autowire()
		->constructor( [
			DI\get( Recaptcha\AllowedLists\Permanent::class ),
			DI\get( Recaptcha\AllowedLists\Configurable::class ),
		] ),

	'WPD\Recaptcha\Validation\Url'             => DI\autowire( Recaptcha\Misc\Url::class )
		->constructor( DI\get( 'wpd.recaptcha.validation.url' ) ),
	'WPD\Recaptcha\Validation\HttpClient'      => DI\create( Recaptcha\Misc\HttpClient::class )
		->constructor( DI\get( 'WPD\Recaptcha\Validation\Url' ) ),
	Recaptcha\Validation::class                => DI\autowire()
		->constructor( DI\get( 'WPD\Recaptcha\Validation\HttpClient' ) ),

	Recaptcha\View::class                      => static fn ( DI\Container $container ): Recaptcha\View =>
	new Recaptcha\View( $container->get( 'wpd.recaptcha.path' ) . 'resources/views' ),

	Recaptcha\Controller::class		           => DI\autowire()
		->constructorParameter( 'secret_key', DI\get( 'wpd.recaptcha.secret' ) )
		->constructorParameter( 'whip', DI\get( 'WPD\Recaptcha\Whip' ) )
	 	->constructorParameter( 'challenge_ttl', DI\get( 'wpd.recaptcha.challenge_ttl' ) ),

	Recaptcha\FormsRepository::class           => DI\autowire()
		->constructor( [
			DI\get( Recaptcha\Forms\Login::class ),
			DI\get( Recaptcha\Forms\LostPassword::class ),
			DI\get( Recaptcha\Forms\RetrievePassword::class ),
		] ),

	'Recaptcha\Settings\AllowedIps'            => DI\autowire( Recaptcha\Misc\Setting::class )
		->constructor(
			DI\get( 'wpd.recaptcha.allowed_ips_option' ),
			__( 'Allowed IPs', 'wpd-recaptcha' ),
			'sanitize_textarea_field',
			[
				'type'        => 'textarea',
				'class'       => '',
				'rows'        => 5,
				'cols'        => 45,
				'description' => __( 'One IP per line.', 'wpd-recaptcha' ),
			]
		),
	Recaptcha\Settings::class                  => DI\autowire()
		->constructor(
			DI\get( 'wpd.recaptcha.admin_page' ),
			[
				DI\get( 'Recaptcha\Settings\AllowedIps' ),
			]
		),

	Recaptcha\Plugin::class                    => DI\autowire()
		->constructorParameter( 'admin_page', DI\get( 'wpd.recaptcha.admin_page' ) )
];
