<?php defined( 'ABSPATH' ) || die;
/**
 * @var string $method
 * @var string $rememberme
 * @var string $redirect_to
 */
?>
<form name="verificationform" id="verificationform" action="<?= esc_url( site_url( 'wp-login.php?action=verification', 'login_post' ) ) ?>" method="post">
    <p>
        <label for="verification_code">
            <?php _e( 'Verification Code', 'innocode-recaptcha' ) ?><br>
            <input type="text" name="code" id="verification_code" class="input" size="6" autofocus autocomplete="off" autocapitalize="off">
        </label>
    </p>
    <?php do_action( 'verification_form' ) ?>
    <p class="submit">
        <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Verify', 'innocode-recaptcha' ) ?>">
        <input type="hidden" name="method" value="<?= esc_attr( $method ) ?>">
        <?php if ( $rememberme ) : ?>
            <input type="hidden" name="rememberme" value="forever">
        <?php endif ?>
        <?php if ( $redirect_to ) : ?>
            <input type="hidden" name="redirect_to" value="<?= esc_attr( $redirect_to ) ?>">
        <?php endif ?>
    </p>
</form>
