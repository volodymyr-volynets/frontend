<?php

class numbers_frontend_captcha_combined_base extends numbers_frontend_captcha_class_base implements numbers_frontend_captcha_interface_base {

	/**
	 * Captcha image tag
	 *
	 * @param string $id
	 * @return string
	 */
	public function captcha($options = []) {
		$options['id'] = isset($options['id']) ? ($options['id'] . '') : 'default';
		if (empty($options['name'])) {
			$options['name'] = $options['id'];
		}
		// assembling
		$checkbox_captcha = new numbers_frontend_captcha_checkbox_base();
		$js = 'document.write("' . addcslashes($checkbox_captcha->captcha(['id' => $options['id']]), '"') . ' are you human?");';
		$result = html::script(['value' => $js]);
		$result.= '<noscript>';
			$result.= html::input(['name' => $options['name'], 'id' => $options['id'], 'size' => 8]);
			$gd_captcha = new numbers_frontend_captcha_gd_base();
			$result.= $gd_captcha->captcha(['id' => $options['id']]);
		$result.= '</noscript>';
		return $result;
	}
}