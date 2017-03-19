<?php

class numbers_frontend_components_checkbox_numbers_base implements numbers_frontend_components_checkbox_interface_base {

	/**
	 * see html::checkbox();
	 */
	public static function checkbox($options = []) {
		// include js & css files
		layout::add_js('/numbers/media_submodules/numbers_frontend_components_checkbox_numbers_media_js_base.js', 10000);
		layout::add_css('/numbers/media_submodules/numbers_frontend_components_checkbox_numbers_media_css_base.css', 10000);
		// id with name
		if (empty($options['id']) && !empty($options['name'])) {
			$options['id'] = $options['name'];
		}
		layout::onload('numbers_checkbox(' . json_encode(['id' => $options['id']]) . ');');
		// certain keys
		foreach (['label_on', 'label_off', 'oposite_checkbox'] as $v) {
			if (isset($options[$v])) {
				$options['data-' . $v] = $options[$v];
				unset($options[$v]);
			}
		}
		// must gain proper class from previous submodule
		$options['flag_call_previous_parent'] = true;
		return html::checkbox($options);
	}
}