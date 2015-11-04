<?php

class numbers_frontend_captcha_checkbox_base extends numbers_frontend_captcha_class_base implements numbers_frontend_captcha_interface_base {

	/**
	 * Captcha image tag
	 *
	 * @param string $id
	 * @return string
	 */
	public function captcha($options = []) {
		$options['id'] = isset($options['id']) ? ($options['id'] . '') : 'default';
		// we need to generate a token
		$crypt = new crypt();
		$token = $crypt->token_create('captcha', $options['id']);
		// and garbage it
		$data = $this->garbage($token, $options['id']);
		layout::onload($data['js']);
		// we need to store garbage in sessions
		session::set(['numbers', 'captcha', $options['id'], 'garbage'], $data['garbage']);
		session::set(['numbers', 'captcha', $options['id'], 'garbage_verified'], null);
		$options['id'].= '_checkbox_base';
		return html::checkbox($options);
	}

	/**
	 * Generate garbage string
	 *
	 * @param string $token
	 * @param string $captcha_id
	 * @return array
	 */
	public function garbage($token, $captcha_id) {
		$result = [
			'js' => '',
			'token' => $token,
			'garbage' => ''
		];
		// generate random hash array
		$letters = 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz';
		$js_array_name = $this->random_variable($letters);
		$js_array = [];
		for ($i = 0; $i < mt_rand(30, 40); $i++) {
			$js_array[]= $this->random_letter($letters);
		}
		$js_array_processed = $js_array;
		// doing reassignments
		$js_reassign = [];
		$js = [];
		for ($i = 0; $i < mt_rand(20, 30); $i++) {
			$type = mt_rand(0, 3);
			if ($type == 0) { // reassinment
				$from = mt_rand(0, sizeof($js_array) - 1);
				$to = mt_rand(0, sizeof($js_array) - 1);
				$js_reassign[] = ['type' => 0, 'from' => $from, 'to' => $to];
				$js_array_processed[$from] = $js_array_processed[$to];
				$js[]= $js_array_name . '[' . $from . ']=' . $js_array_name . '[' . $to . '];';
			} else if ($type == 1) { // reassigment + addition
				$from = mt_rand(0, sizeof($js_array) - 1);
				$to = mt_rand(0, sizeof($js_array) - 1);
				$plus = $this->random_letter($letters);
				$js_reassign[] = ['type' => 1, 'from' => $from, 'to' => $to, 'plus' => $plus];
				$js_array_processed[$from] = $js_array_processed[$to] . $plus;
				$js[]= $js_array_name . '[' . $from . ']=' . $js_array_name . '[' . $to . '] + \'' . $plus . '\';';
			} else if ($type == 2) { // self _ addition
				$from = mt_rand(0, sizeof($js_array) - 1);
				$plus = $this->random_letter($letters);
				$js_reassign[] = ['type' => 1, 'from' => $from, 'plus' => $plus];
				$js_array_processed[$from].= $plus;
				$js[]= $js_array_name . '[' . $from . ']=' . $js_array_name . '[' . $from . '] + \'' . $plus . '\';';
			} else if ($type == 3) { // assignment though other variable
				$from = mt_rand(0, sizeof($js_array) - 1);
				$to = mt_rand(0, sizeof($js_array) - 1);
				$js_reassign[] = ['type' => 0, 'from' => $from, 'to' => $to];
				$js_array_processed[$from] = $js_array_processed[$to];
				$temp_var = $this->random_variable($letters, [$js_array_name]);
				$js[]= 'var ' . $temp_var . '=' . $js_array_name . '[' . $to . '];' . $js_array_name . '[' . $from . ']=' . $temp_var . ';';
			}
		}
		// and we need to randomize assignment with ifs, switch and other garbage
		foreach ($js as $k => $v) {
			$type = mt_rand(0, 3);
			if ($type == 0) {
				continue;
			} else if ($type == 1 || $type == 2) { // if
				$condition = mt_rand(0, 2);
				if ($condition == 0) {
					$condition = 'true';
				} else if ($condition == 1) {
					$temp = mt_rand(1, 100);
					$condition = $temp . '==' . $temp;
				} else if ($condition == 2) {
					$temp = $this->random_letter($letters);
					$condition = "'" . $temp . "'=='" . $temp . "'";
				}
				if ($type == 1) {
					$js[$k] = 'if(' . $condition . '){' . $v . '}';
				} else {
					$temp_var = $this->random_variable($letters, [$js_array_name]);
					$js[$k] = 'if(!' . $condition . '){true}else{' . $v . '}';
				}
			} else if ($type == 3) { // switch
				$js[$k] = 'switch(1){default:' . $v . '}';
			}
		}
		$result['garbage'] = implode('', $js_array_processed);
		// generating javascript
		$js2 = [];
		$js2[]= 'var ' . $js_array_name . ' = ' . json_encode($js_array) . ';';
		$js2 = array_merge($js2, $js);
		$token2 = urldecode($token);
		$js2[] = "numbers.ajax.post('/numbers/frontend/captcha/checkbox/controller/captcha', {token: '$token2', garbage: {$js_array_name}.join('')}, function(data) { if (data.success) { $('#{$captcha_id}_checkbox_base').prop({disabled: true}); } });";
		$result['js'] = "$('#{$captcha_id}_checkbox_base').click(function() {" . implode('', $js2) . "});";
		return $result;
	}

	/**
	 * Generate random letter from a string
	 *
	 * @param string $letters
	 * @return string
	 */
	private function random_letter($letters) {
		return $letters[mt_rand(0, strlen($letters) - 1)];
	}

	/**
	 * Generate variable
	 *
	 * @param string $letters
	 * @param array $existing
	 * @return string
	 */
	private function random_variable($letters, $existing = []) {
		do {
			$str = '';
			for ($i = 0; $i < mt_rand(3, 10); $i++) {
				$str.= $this->random_letter($letters);
			}
		} while(in_array($str, $existing));
		return $str;
	}
}