<?php

class numbers_frontend_components_captcha_class_base {

	/**
	 * Generate random string for captcha
	 *
	 * @param string $captcha_link
	 * @param string $letters
	 * @param int $length
	 * @return string
	 */
	public static function generate_password($captcha_link, $letters = null, $length = 5) {
		$letters = isset($letters) ? $letters : '123456789';
		$result = '';
		for ($i = 0; $i < $length; $i++) {
			 $result.= $letters[mt_rand(0, strlen($letters) - 1)];
		}
		// setting value in session
		session::set(str_replace('_', '.', get_called_class()) . '.' . $captcha_link . '.password', $result);
		return $result;
	}

	/**
	 * Validate captcha
	 *
	 * @param string $captcha_link
	 * @param string $password
	 * @return boolean
	 */
	public static function validate($captcha_link, $password) {
		$key = str_replace('_', '.', get_called_class()) . '.' . $captcha_link . '.password';
		$session_password = session::get($key);
		if (!empty($session_password) && $session_password == $password) {
			session::set($key, null);
			return true;
		} else {
			return false;
		}
	}
}