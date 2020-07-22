<?php

namespace Innocode\ReCaptcha\Abstracts;


use Innocode\ReCaptcha\Interfaces\AllowIPListInterface;

/**
 * Class AbstractAllowList
 * @package Innocode\ReCaptcha\Abstracts
 */
abstract class AbstractAllowIPList implements AllowIPListInterface {
	/**
	 * @return array
	 */
	abstract public function get_allowed_ips(): array;

}
