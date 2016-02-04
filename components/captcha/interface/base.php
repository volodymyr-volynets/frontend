<?php

interface numbers_frontend_components_captcha_interface_base {
	public static function generate_password($captcha_link, $letters = null, $length = 5);
	public static function captcha($options = []);
	public static function validate($captcha_link, $password);
}