<?php

namespace WPD\Recaptcha;

final class VerificationCode {

	/**
	 * @var int $user_id
	 */
	private int $user_id;
	/**
	 * @var string $code
	 */
	private string $code;
	/**
	 * @var int $timestamp
	 */
	private int $timestamp;
	/**
	 * @var string $ip_address
	 */
	private string $ip_address;
	/**
	 * @var string $user_agent
	 */
	private string $user_agent;

	/**
	 * VerificationCode constructor.
	 *
	 * @param int    $user_id
	 * @param string $code
	 * @param int    $timestamp
	 * @param string $ip_address
	 * @param string $user_agent
	 */
	public function __construct(
		int $user_id,
		string $code,
		int $timestamp,
		string $ip_address,
		string $user_agent
	) {
		$this->user_id    = $user_id;
		$this->code       = $code;
		$this->timestamp  = $timestamp;
		$this->ip_address = $ip_address;
		$this->user_agent = $user_agent;
	}

	/**
	 * @param int    $user_id
	 * @param int    $timestamp
	 * @param string $ip_address
	 * @param string $user_agent
	 * @return self
	 */
	public static function generate(
		int $user_id,
		int $timestamp,
		string $ip_address,
		string $user_agent
	): self {
		$length = apply_filters( 'wpd_recaptcha_verification_code_length', 6 );
		$code   = wp_generate_password( $length, false );

		return new self( $user_id, $code, $timestamp, $ip_address, $user_agent );
	}

	/**
	 * @return void
	 */
	public function save(): void {
		$code = "$this->timestamp:{$this->to_hash()}";

		update_user_meta( $this->user_id, 'wpd_recaptcha_verification_code', $code );
	}

	/**
	 * @param int $ttl
	 * @return bool
	 */
	public function validate( int $ttl ): bool {
		$hash = get_user_meta( $this->user_id, 'wpd_recaptcha_verification_code', true );

		if ( empty( $hash ) || ! is_string( $hash ) ) {
			return false;
		}

		$parts     = explode( ':', $hash );
		$hash      = $parts[1];
		$hashed_at = (int) $parts[0];

		if ( $hashed_at + $ttl < $this->timestamp ) {
			return false;
		}

		return hash_equals( $hash, self::hash( $this->code, $hashed_at, $this->ip_address, $this->user_agent ) );
	}

	/**
	 * @return void
	 */
	public function clear(): void {
		delete_user_meta( $this->user_id, 'wpd_recaptcha_verification_code' );
	}

	/**
	 * @return string
	 */
	public function to_hash(): string {
		return self::hash( $this->code, $this->timestamp, $this->ip_address, $this->user_agent );
	}

	/**
	 * @param string $code
	 * @param int    $timestamp
	 * @param string $ip_address
	 * @param string $user_agent
	 * @return string
	 */
	public static function hash( string $code, int $timestamp, string $ip_address, string $user_agent ): string {
		$hash = wp_hash( "$code|$timestamp|$ip_address|$user_agent", 'nonce' );

		return "$timestamp:$hash";
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->code;
	}
}
