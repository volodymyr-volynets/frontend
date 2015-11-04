<?php

class numbers_frontend_captcha_class_base {

	/**
	 * Generate random string for captcha
	 *
	 * @return string
	 */
	public function generate() {
		// todo: add policies here
		$letters = '123456789';
		$length = 5;
		$result = '';
		for ($i = 0; $i < $length; $i++) {
			 $result.= $letters[mt_rand(0, strlen($letters) - 1)];
		}
		return $result;
	}

	/**
	 * Validate captcha
	 *
	 * @param string $captcha_id
	 * @param string $password
	 * @return boolean
	 */
	public function validate($captcha_id, $password) {
		$stored = session::get(['numbers', 'captcha', $captcha_id]);
		// checkbox validation first, password check second
		if (!empty($stored['garbage_verified']) || (!empty($stored['password']) && $stored['password'] == $password)) {
			session::set(['numbers', 'captcha', $captcha_id], []);
			return true;
		}
	}
}