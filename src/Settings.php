<?php

namespace WPD\Recaptcha;

final class Settings {

	/**
	 * @var string $base_group
	 */
	public string $base_group;
	/**
	 * @var Setting[] $settings
	 */
	private array $settings;

	/**
	 * Settings constructor.
	 *
	 * @param string    $base_group
	 * @param Setting[] $settings
	 */
	public function __construct(
		string $base_group,
		Setting ...$settings
	) {
		$this->base_group = $base_group;
		$this->settings   = $settings;
	}

	/**
	 * @param string $option_page
	 * @return void
	 */
	public function register( string $option_page ): void {
		add_settings_section(
			"{$this->base_group}_firewall",
			__( 'Firewall', 'wpd-recaptcha' ),
			function (): void {
				printf(
					'<p>%s</p>',
					__( 'Allows to bypass protection using rules.', 'wpd-recaptcha' )
				);
			},
			$option_page
		);

		foreach ( $this->settings as $setting ) {
			$setting->register();

			add_settings_field(
				$setting->name,
				$setting->label,
				function () use ( $setting ): void {
					$setting->render();
				},
				$option_page,
				"{$this->base_group}_firewall"
			);
		}
	}

	/**
	 * @return void
	 */
	public function delete(): void {
		foreach ( $this->settings as $setting ) {
			$setting->delete();
		}
	}
}
