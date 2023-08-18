<?php defined( 'ABSPATH' ) || die ?>
<form name="verificationform" id="verificationform" action="<?= esc_url( site_url( 'wp-login.php?action=wpd_recaptcha_verification', 'login_post' ) ) ?>" method="post">
    <p>
        <label for="verification_code">
            <?php _e( 'Verification Code', 'wpd-recaptcha' ) ?><br>
            <input type="text" name="code" id="verification_code" class="input" size="6" autofocus autocomplete="off" autocapitalize="off" required>
        </label>
    </p>
    <?php do_action( 'wpd_recaptcha_verification_form' ) ?>
    <p class="submit">
        <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Verify', 'wpd-recaptcha' ) ?>">
		<?php if ( isset( $_GET['login'] ) ) : ?>
			<input type="hidden" name="login" value="<?= esc_attr( wp_unslash( $_GET['login'] ) ) ?>">
		<?php endif ?>
		<?php if ( ! empty( $_GET['rememberme'] ) ) : ?>
            <input type="hidden" name="rememberme" value="forever">
        <?php endif ?>
        <?php if ( isset( $_GET['redirect_to'] ) ) : ?>
            <input type="hidden" name="redirect_to" value="<?= sanitize_url( $_GET['redirect_to'] ) ?>">
        <?php endif ?>
		<?php if ( isset( $_GET['interim-login'] ) ) : ?>
			<input type="hidden" name="interim-login" value="1">
		<?php endif ?>
    </p>
</form>
