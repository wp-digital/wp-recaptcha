<?php

namespace WPD\Recaptcha;

use DI\Container;
use DI\ContainerBuilder;
use WPD\Recaptcha\Exceptions\NotReadableFileException;

/**
 * @internal This class is not intended to be used directly. Use functions.php instead.
 */
final class Bootstrap {

	/**
	 * @var Container $container
	 */
	private Container $container;

	/**
	 * @throws NotReadableFileException Thrown when the constants.php or config.php file is not readable.
	 */
	private function __construct() {
		$constants = __DIR__ . '/../constants.php';

		if ( ! is_readable( $constants ) ) {
			throw new NotReadableFileException( $constants );
		}

		require_once $constants;

		$config = __DIR__ . '/../config.php';

		if ( ! is_readable( $config ) ) {
			throw new NotReadableFileException( $config );
		}

		$builder = new ContainerBuilder();
		$builder->addDefinitions( $config );

		$this->container = $builder->build();
	}

	/**
	 * @param string|null $file
	 * @return self|null
	 */
	public static function init( string $file = null ): ?self {
		static $instance;

		if ( ! $instance ) {
			try {
				$instance = new self();
				$instance->run( $file );
			} catch ( \Exception $exception ) {
				wp_die( esc_html( $exception->getMessage() ) );
			}
		}

		return $instance;
	}

	/**
	 * @param string|null $file
	 * @return void
	 * @throws \DI\DependencyException Throws when a dependency cannot be resolved.
	 * @throws \DI\NotFoundException   Throws when a dependency cannot be found.
	 */
	private function run( string $file = null ): void {
		/**
		 * @var Plugin $plugin
		 */
		$plugin = $this->container->get( Plugin::class );

		if ( $file !== null ) {
			register_activation_hook( $file, [ $plugin, 'activate' ] );
			register_deactivation_hook( $file, [ $plugin, 'deactivate' ] );
		}

		add_action( 'init', [ $plugin, 'run' ] );

		/**
		 * Fires when the plugin is loaded.
		 *
		 * @param Plugin $plugin
		 */
		do_action( 'wpd_recaptcha_loaded', $plugin );
	}

	/**
	 * @return Plugin
	 * @throws \DI\DependencyException Throws when a dependency cannot be resolved.
	 * @throws \DI\NotFoundException   Throws when a dependency cannot be found.
	 */
	public function plugin(): Plugin {
		return $this->container->get( Plugin::class );
	}
}
