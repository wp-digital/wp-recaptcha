<?php defined( 'ABSPATH' ) || die ?>
<div class="wrap">
	<h1><?php esc_html_e( 'Bot Protection', 'wpd-recaptcha' ) ?></h1>
	<form action="<?= esc_url( admin_url( 'options.php' ) ) ?>" method="post">
		<?php settings_fields( 'wpd_recaptcha' ) ?>
		<?php do_settings_sections( 'wpd_recaptcha' ) ?>
		<?php submit_button() ?>
	</form>
</div>

