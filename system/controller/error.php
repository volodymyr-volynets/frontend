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

	/**
	 * Error action
	 */
	public function action_error() {
		$result = '';
		// show human readable messages first
		if (count(error_base::$errors) > 0) {
			$messages = [];
			foreach (error_base::$errors as $k => $v) {
				if ($v['errno'] == -1) {
					foreach ($v['error'] as $k2 => $v2) {
						$messages[] = i18n(null, $v2);
					}
				}
			}
			if (empty($messages)) {
				$messages[] = i18n(null, 'Internal Server Error: 500');
			}
			$result.= html::message(['type' => 'danger', 'options' => $messages]);
		}
		// show full description second
		if (application::get('flag.error.show_full') && count(error_base::$errors) > 0) {
			foreach (error_base::$errors as $k => $v) {
				$result.= '<h3>' . error_base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</h3>';
				$result.= '<br />';
				$result.= '<div>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</div>';
				$result.= '<br />';
				// showing code only when we debug
				if (debug::$debug) {
					$result.= '<div><pre>' . $v['code'] . '</pre></div>';
					$result.= '<br />';
					$result.= '<div><pre>' . implode("\n", $v['backtrace']) . '</pre></div>';
				}
				$result.= '<hr />';
			}
		}
		echo $result;
	}
}