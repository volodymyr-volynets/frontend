<?php

class numbers_frontend_components_select_numbers_base implements numbers_frontend_components_select_interface_base {

	/**
	 * see \HTML::select();
	 */
	public static function select($options = []) {
		// we do not process readonly selects
		if (empty($options['readonly'])) {
			// include js & css files
			Layout::add_js('/numbers/media_submodules/numbers_frontend_components_select_numbers_media_js_base.js', 10000);
			Layout::add_css('/numbers/media_submodules/numbers_frontend_components_select_numbers_media_css_base.css', 10000);
			// font awesome icons
			library::add('fontawesome');
			// id with name
			if (empty($options['id']) && !empty($options['name'])) {
				$options['id'] = $options['name'];
			}
			Layout::onload('numbers_select(' . json_encode(['id' => $options['id']]) . ');');
		}
		// must gain proper class from previous submodule
		$options['flag_call_previous_parent'] = true;
		return \HTML::select($options);
	}

	/**
	 * see \HTML::multiselect();
	 */
	public static function multiselect($options = []) {
		$options['multiple'] = 1;
		return self::select($options);
	}
}