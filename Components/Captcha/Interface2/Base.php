<?php

namespace Numbers\Frontend\Components\Captcha\Interface2;
interface Base {
	public static function captcha(array $options = []) : string;
	public function validate(string $value, array $options = []) : array;
}