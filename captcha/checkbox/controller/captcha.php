<?php

class numbers_frontend_captcha_checkbox_controller_captcha {

	/**
	 * This would draw captcha in png image format
	 */
	public function action_index() {
		$input = request::input();
		if (!empty($input['token'])) {
			$crypt = new crypt();
			$token_data = $crypt->token_validate($input['token'], 1, true);
			if (!($token_data === false || $token_data['id'] !== 'captcha')) {
				$garbage = session::get(['numbers', 'captcha', $token_data['data'], 'garbage']);
				$garbage_verified = session::get(['numbers', 'captcha', $token_data['data'], 'garbage_verified']);
				if ($garbage_verified === null && !empty($garbage) && !empty($input['garbage']) && $garbage == $input['garbage']) {
					// putting flag into sessions
					session::set(['numbers', 'captcha', $token_data['data'], 'garbage_verified'], true);
					layout::render_as_json(['success' => true]);
				} else {
					session::set(['numbers', 'captcha', $token_data['data'], 'garbage_verified'], false);
					layout::render_as_json(['success' => false]);
				}
			}
		}
		exit;
	}
}