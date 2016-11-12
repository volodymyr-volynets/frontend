<?php

class numbers_frontend_system_controller_error extends object_controller {

	public $title = 'Js Error Handler';

	/**
	 * This would process error message sent from frontend
	 */
	public function action_index() {
		$input = request::input();
		if (!empty($input['token'])) {
			$crypt = new crypt();
			$token_data = $crypt->token_validate($input['token'], ['skip_time_validation' => true]);
			if (!($token_data === false || $token_data['id'] !== 'general')) {
				$input['data'] = json_decode($input['data'], true);
				error_base::error_handler('javascript', $input['data']['message'], $input['data']['file'], $input['data']['line']);
			}
		}
		// rendering
		layout::render_as(file_get_contents(__DIR__ . '/error.png'), 'image/png');
	}
}