<?php

interface numbers_frontend_components_captcha_interface_base {
	public static function captcha(array $options = []) : string;
	public function validate(string $value, array $options = []) : array;
}