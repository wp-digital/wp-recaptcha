<?php

namespace Innocode\ReCaptcha\Interfaces;


/**
 * Interface AllowListInterface
 * @package Innocode\ReCaptcha\Interfaces
 */
interface AllowIPListInterface {

	/**
	 * @return array
	 */
	public function get_allowed_ips(): array;

}
