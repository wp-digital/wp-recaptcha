<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Misc\Hydrator;

class Response {

	use Hydrator;

	/**
	 * @var array $data
	 */
	protected array $data;
	/**
	 * @var bool $success
	 */
	protected bool $success;
	/**
	 * @var float|null $score
	 */
	protected float $score;
	/**
	 * @var string $action
	 */
	protected string $action;
	/**
	 * @var \DateTimeImmutable $challenge_ts
	 */
	protected \DateTimeImmutable $challenge_ts;
	/**
	 * @var string $hostname
	 */
	protected string $hostname;
	/**
	 * @var array $error_codes
	 */
	protected array $error_codes;
	/**
	 * @var string $cdata
	 */
	protected string $cdata;

	/**
	 * @param array $data
	 * @throws \Exception If the challenge_ts is not a valid date string.
	 */
	public function __construct( array $data = [] ) {
		$this->data = $data;

		$this->success      = $data['success'] ?? false;
		$this->score        = $data['score'] ?? 0;
		$this->action       = $data['action'] ?? '';
		$this->challenge_ts = new \DateTimeImmutable( $data['challenge_ts'] ?? 'now' );
		$this->hostname     = $data['hostname'] ?? '';
		$this->error_codes  = $data['error-codes'] ?? [];
		$this->cdata        = $data['cdata'] ?? '';
	}

	/**
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * @return float
	 */
	public function get_score(): float {
		return $this->score;
	}

	/**
	 * @return string
	 */
	public function get_action(): string {
		return $this->action;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function get_challenge_ts(): \DateTimeImmutable {
		return $this->challenge_ts;
	}

	/**
	 * @return string
	 */
	public function get_hostname(): string {
		return $this->hostname;
	}

	/**
	 * @return array
	 */
	public function get_error_codes(): array {
		return $this->error_codes;
	}
}
