<?php

namespace WPD\Recaptcha;

use WPD\Recaptcha\Misc\Arrayable;

class Request implements Arrayable {

	/**
	 * @var string $secret
	 */
	protected string $secret;
	/**
	 * @var string $response
	 */
	protected string $response;
	/**
	 * @var string|null $remote_ip
	 */
	protected ?string $remote_ip = null;
	/**
	 * @var string|null $idempotency_key
	 */
	protected ?string $idempotency_key = null;

	/**
	 * @param string $secret
	 * @param string $response
	 */
	public function __construct(
		string $secret,
		string $response
	) {
		$this->secret   = $secret;
		$this->response = $response;
	}

	/**
	 * @param string $remote_ip
	 * @return Request
	 */
	public function set_remote_ip( string $remote_ip ): Request {
		$this->remote_ip = $remote_ip;

		return $this;
	}

	/**
	 * @param string $idempotency_key
	 * @return Request
	 */
	public function set_idempotency_key( string $idempotency_key ): Request {
		$this->idempotency_key = $idempotency_key;

		return $this;
	}

	/**
	 * @return array
	 */
	public function to_array(): array {
		$array = [
			'secret'   => $this->secret,
			'response' => $this->response,
		];

		if ( $this->remote_ip !== null ) {
			$array['remoteip'] = $this->remote_ip;
		}

		if ( $this->idempotency_key !== null ) {
			$array['idempotency_key'] = $this->idempotency_key;
		}

		return $array;
	}
}
