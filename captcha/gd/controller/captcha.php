<?php

class numbers_frontend_captcha_gd_controller_captcha {

	/**
	 * This would draw captcha in png image format
	 */
	public function action_index() {
		$input = request::input();
		if (!empty($input['token'])) {
			$crypt = new crypt();
			$token_data = $crypt->token_validate($input['token'], 1, true);
			if (!($token_data === false || $token_data['id'] !== 'captcha')) {
				// if we got here, it means we have to generate an image
				$captcha = new numbers_frontend_captcha_gd_base();
				$password = $captcha->generate();
				// now we need to store password in sessions
				session::set(['numbers', 'captcha', $token_data['data'], 'password'], $password);
				// drawing
				$captcha->draw($password, []);
			}
		}
		exit;
	}
}