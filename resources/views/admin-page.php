<?php defined( 'ABSPATH' ) || die;

$admin_page = WPD\Recaptcha\admin_page();

if ( is_wp_error( $admin_page ) ) {
	return;
} ?>
<div class="wrap">
	<h1><?php esc_html_e( 'Bot Protection', 'wpd-recaptcha' ) ?></h1>
	<form action="<?= esc_url( admin_url( 'options.php' ) ) ?>" method="post">
		<?php settings_fields( $admin_page ) ?>
		<?php do_settings_sections( $admin_page ) ?>
		<?php submit_button() ?>
	</form>
</div>
