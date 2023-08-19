<?php

// phpcs:disable WPD.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WPD.Security.NonceVerification.Missing
// phpcs:disable WordPress.Security.SafeRedirect.wp_redirect_wp_redirect

namespace WPD\Recaptcha\Misc;

use Vectorface\Whip\Whip;
use WPD\Recaptcha\Exceptions\ValidationException;
use WPD\Recaptcha\Firewall;
use WPD\Recaptcha\Forms\ThresholdableInterface;
use WPD\Recaptcha\FormsRepository;
use WPD\Recaptcha\Providers\Service;
use WPD\Recaptcha\Providers\VisibleInterface;
use WPD\Recaptcha\Request;
use WPD\Recaptcha\Response;
use WPD\Recaptcha\Validation;
use WPD\Recaptcha\VerificationCode;
use WPD\Recaptcha\View;

final class Controller {

	/**
	 * @var Service $service
	 */
	private Service $service;
	/**
	 * @var string $secret_key
	 */
	private string $secret_key;
	/**
	 * @var Whip $whip
	 */
	protected Whip $whip;
	/**
	 * @var Firewall $firewall
	 */
	protected Firewall $firewall;
	/**
	 * @var Validation $validation
	 */
	private Validation $validation;
	/**
	 * @var View $view
	 */
	private View $view;
	/**
	 * @var int $challenge_ttl
	 */
	private int $challenge_ttl;

	/**
	 * Controller constructor.
	 *
	 * @param Service    $service
	 * @param string     $secret_key
	 * @param Whip       $whip
	 * @param Firewall   $firewall
	 * @param Validation $validation
	 * @param View       $view
	 * @param int        $challenge_ttl
	 */
	public function __construct(
		Service $service,
		string $secret_key,
		Whip $whip,
		Firewall $firewall,
		Validation $validation,
		View $view,
		int $challenge_ttl
	) {
		$this->service       = $service;
		$this->secret_key    = $secret_key;
		$this->whip          = $whip;
		$this->firewall      = $firewall;
		$this->validation    = $validation;
		$this->view          = $view;
		$this->challenge_ttl = $challenge_ttl;
	}

	/**
	 * @return void
	 */
	public function no_js_warning(): void {
		printf(
			<<<HTML
			<noscript>
				<p class="message">%s</p>
			</noscript>
			HTML,
			esc_html__( 'Please enable JavaScript in your browser settings to submit this form.', 'wpd-recaptcha' )
		);
	}

	/**
	 * @return void
	 */
	public function token(): void {
		if ( $this->service instanceof VisibleInterface ) {
			echo $this->service->html();
		}

		echo '<input type="hidden" name="wpd-recaptcha-token" />';
	}

	/**
	 * @param FormsRepository $forms_repository
	 * @return void
	 */
	public function enqueue_scripts( FormsRepository $forms_repository ): void {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			'wpd-recaptcha',
			$this->service->get_script_url(),
			[],
			null,
			true
		);
		wp_add_inline_script(
			'wpd-recaptcha',
			$this->service->js_snippet( $forms_repository )
		);
	}

	/**
	 * @param FormsRepository $forms_repository
	 * @return void
	 */
	public function validate( FormsRepository $forms_repository ): void {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return;
		}

		$action = current_action();
		$form   = $forms_repository->find_by_validation_action( $action );

		if ( $form === null ) {
			return;
		}

		if (
			! isset( $_POST['wpd-recaptcha-token'] ) ||
			! is_string( $_POST['wpd-recaptcha-token'] ) ||
			$_POST['wpd-recaptcha-token'] === ''
		) {
			$form->fail(
				new \WP_Error(
					'wpd_recaptcha_missing_token',
					esc_html__( 'The token is missing.', 'wpd-recaptcha' ),
					[
						'codes' => [ Response::ERROR_MISSING_INPUT_RESPONSE ],
					]
				)
			);

			return;
		}

		$remote_ip = $this->whip->getValidIpAddress();

		try {
			$request = new Request( $this->secret_key, $_POST['wpd-recaptcha-token'] );

			if ( $remote_ip !== false ) {
				$request->set_remote_ip( $remote_ip );
			}

			$response = ( $this->validation )( $request );
		} catch ( ValidationException $exception ) {
			$form->fail(
				new \WP_Error(
					'wpd_recaptcha_validation_failed',
					esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
					[
						'codes' => $exception->get_codes(),
					]
				)
			);

			return;
		}

		if ( $response->get_action() !== $form->action() ) {
			$form->fail(
				new \WP_Error(
					'wpd_recaptcha_validation_failed',
					esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
					[
						'codes' => [ Response::ERROR_INVALID_ACTION ],
					]
				)
			);

			return;
		}

		if ( ! in_array( $response->get_hostname(), $form->allowed_hosts(), true ) ) {
			$form->fail(
				new \WP_Error(
					'wpd_recaptcha_validation_failed',
					esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
					[
						'codes' => [ Response::ERROR_INVALID_HOSTNAME ],
					]
				)
			);

			return;
		}

		if ( $remote_ip && $this->firewall->is_allowed( $remote_ip ) ) {
			$form->success( $response );

			return;
		}

		if ( $response->get_challenge_ts()->getTimestamp() + $this->challenge_ttl < time() ) {
			$form->fail(
				new \WP_Error(
					'wpd_recaptcha_validation_failed',
					esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
					[
						'codes' => [ Response::ERROR_CHALLENGE_EXPIRED ],
					]
				)
			);

			return;
		}

		if ( $response->score_exits() ) {
			if (
				(
					$form instanceof ThresholdableInterface &&
					$response->get_score() < $form->threshold()
				) ||
				$response->get_score() <= 0.1
			) {
				$form->fail(
					new \WP_Error(
						'wpd_recaptcha_validation_failed',
						esc_html__( 'The validation failed.', 'wpd-recaptcha' ),
						[
							'codes' => [ Response::ERROR_SCORE_TOO_LOW ],
						]
					)
				);

				return;
			}
		}

		$form->success( $response );
	}

	/**
	 * @param \WP_User $user
	 * @return void
	 */
	public function verify( \WP_User $user ): void {
		$remote_ip = $this->whip->getValidIpAddress();

		$code = VerificationCode::generate(
			$user->ID,
			time(),
			$remote_ip !== false ? $remote_ip : '',
			$_SERVER['HTTP_USER_AGENT'] ?? ''
		);

		$site_name = is_multisite()
			? get_network()->site_name
			: wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$subject = sprintf(
			/* translators: %s: Site title. */
			__( '[%s] Verification Code', 'wpd-recaptcha' ),
			$site_name
		);

		$message = __( 'Someone is trying to sign in to the following account:', 'wpd-recaptcha' ) . "\r\n\r\n";
		// translators: %s: Site title.
		$message .= sprintf( __( 'Site Name: %s', 'wpd-recaptcha' ), $site_name ) . "\r\n\r\n";
		// translators: %s: User login.
		$message .= sprintf( __( 'Username: %s', 'wpd-recaptcha' ), $user->user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'wpd-recaptcha' ) . "\r\n\r\n";
		$message .= __( 'To sign in, enter the following verification code into the input field:', 'wpd-recaptcha' ) . "\r\n\r\n";
		$message .= "$code\r\n\r\n";

		$subject = apply_filters( 'wpd_recaptcha_verification_email_subject', $subject, $code, $user );
		$message = apply_filters( 'wpd_recaptcha_verification_email_message', $message, $code, $user );

		if ( $message && ! wp_mail( $user->user_email, $subject, $message ) ) {
			wp_die(
				esc_html__( 'The verification email could not be sent.', 'wpd-recaptcha' ),
				esc_html__( 'Error', 'wpd-recaptcha' ),
				[
					'response' => 500,
				]
			);
		}

		$code->save();

		$link = add_query_arg(
			[
				'action' => 'wpd_recaptcha_verification',
				'login'  => rawurlencode( $user->user_login ),
			],
			network_site_url( 'wp-login.php', 'login' )
		);

		if ( ! empty( $_POST['rememberme'] ) ) {
			$link = add_query_arg( 'rememberme', 'forever', $link );
		}

		if ( isset( $_POST['redirect_to'] ) ) {
			$link = add_query_arg( 'redirect_to', rawurlencode( $_POST['redirect_to'] ), $link );
		}

		if ( isset( $_POST['interim-login'] ) ) {
			$link = add_query_arg( 'interim-login', 1, $link );
		}

		$link = apply_filters( 'wpd_recaptcha_verification_link', $link, $code, $user );

		wp_safe_redirect( $link );
		exit;
	}

	/**
	 * @return void
	 */
	public function verification(): void {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->authenticate();

			return;
		}

		if ( ! isset( $_GET['login'] ) || ! is_string( $_GET['login'] ) ) {
			wp_safe_redirect( wp_login_url() );
			exit;
		}

		$messages = new \WP_Error();
		$messages->add(
			'wpd_recaptcha_verification',
			esc_html__( 'Please enter the verification code.', 'wpd-recaptcha' ),
			'message'
		);

		if ( isset( $_GET['interim-login'] ) ) {
			$GLOBALS['interim_login'] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		login_header(
			esc_html__( 'Verification Code', 'wpd-recaptcha' ),
			'',
			$messages
		);

		( $this->view )( 'verification' );

		login_footer();
		exit;
	}

	/**
	 * @return void
	 */
	private function authenticate(): void {
		$interim_login   = isset( $_POST['interim-login'] );
		$failed_redirect = site_url( 'wp-login.php?error=wpd_recaptcha_verification_failed' );

		if ( $interim_login ) {
			$failed_redirect = add_query_arg( 'interim-login', 1, $failed_redirect );
		}

		if (
			! isset( $_POST['login'] ) ||
			! is_string( $_POST['login'] ) ||
			$_POST['login'] === '' ||
			! isset( $_POST['code'] ) ||
			! is_string( $_POST['code'] ) ||
			$_POST['code'] === ''
		) {
			wp_redirect( $failed_redirect );
			exit;
		}

		$user_login = sanitize_user( wp_unslash( $_POST['login'] ) );
		$user       = get_user_by( 'login', $user_login );

		if ( ! ( $user instanceof \WP_User ) ) {
			wp_redirect( $failed_redirect );
			exit;
		}

		$remote_ip = $this->whip->getValidIpAddress();
		$code      = new VerificationCode(
			$user->ID,
			sanitize_text_field( wp_unslash( $_POST['code'] ) ),
			time(),
			$remote_ip !== false ? $remote_ip : '',
			$_SERVER['HTTP_USER_AGENT'] ?? ''
		);
		$is_valid  = $code->validate( $this->challenge_ttl );

		$code->clear(); // Clear the code, so it can't be used again.

		if ( ! $is_valid ) {
			wp_redirect( $failed_redirect );
			exit;
		}

		$remember_me = ! empty( $_POST['rememberme'] );

		wp_set_auth_cookie( $user->ID, $remember_me );

		if ( $interim_login ) {
			$this->interim_login();

			return;
		}

		$redirect_to = $_POST['redirect_to'] ?? '';

		if ( $redirect_to && is_ssl() && false !== strpos( $redirect_to, 'wp-admin' ) ) {
			$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
		}

		$requested_redirect_to = $_POST['redirect_to'] ?? '';
		$redirect_to           = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

		if ( $redirect_to ) {
			wp_safe_redirect( $redirect_to );
			exit;
		}

		if (
			is_multisite() &&
			! get_active_blog_for_user( $user->ID ) &&
			! is_super_admin( $user->ID )
		) {
			wp_redirect( user_admin_url() );
			exit;
		}

		if ( is_multisite() && ! $user->has_cap( 'read' ) ) {
			wp_redirect( get_dashboard_url( $user->ID ) );
			exit;
		}

		if ( ! $user->has_cap( 'edit_posts' ) ) {
			wp_redirect(
				$user->has_cap( 'read' )
					? admin_url( 'profile.php' )
					: home_url()
			);
			exit;
		}

		wp_redirect( admin_url() );
		exit;
	}

	/**
	 * @return void
	 */
	private function interim_login(): void {
		$GLOBALS['interim_login'] = 'success'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$message = sprintf(
			'<p class="message">%s</p>',
			__( 'You have logged in successfully.' )
		);

		login_header( '', $message );

		echo '</div>';

		do_action( 'login_footer' );

		if ( isset( $_POST['customize-login'] ) ) {
			echo <<<HTML
			<script type="text/javascript">setTimeout( function(){ new wp.customize.Messenger({ url: '<?php echo wp_customize_url(); ?>', channel: 'login' }).send('login') }, 1000 );</script>
			HTML;
		}

		echo '</body></html>';
		exit;
	}

	/**
	 * @return void
	 */
	public function verification_errors(): void {
		if (
			! isset( $_GET['error'] ) ||
			$_GET['error'] !== 'wpd_recaptcha_verification_failed'
		) {
			return;
		}

		add_filter(
			'wp_login_errors',
			function ( \WP_Error $errors ) {
				$errors->add(
					'wpd_recaptcha_verification_failed',
					esc_html__( 'The verification failed. Please try again.', 'wpd-recaptcha' )
				);

				return $errors;
			}
		);
	}

	/**
	 * @return void
	 */
	public function admin_page(): void {
		( $this->view )( 'admin-page' );
	}
}
