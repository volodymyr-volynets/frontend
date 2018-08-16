<?php

namespace Numbers\Frontend\Components\Checkbox\Numbers;
class Base implements \Numbers\Frontend\Components\Checkbox\Interface2\Base {

	/**
	 * see \HTML::checkbox();
	 */
	public static function checkbox(array $options = []) : string {
		// include js & css files
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Checkbox_Numbers_Media_JS_Base.js', 10000);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Checkbox_Numbers_Media_CSS_Base.css', 10000);
		// id with name
		if (empty($options['id']) && !empty($options['name'])) {
			$options['id'] = $options['name'];
		}
		\Layout::onload('NumbersCheckbox(' . json_encode(['id' => $options['id']]) . ');');
		// certain keys
		foreach (['label_on', 'label_off', 'oposite_checkbox'] as $v) {
			if (isset($options[$v])) {
				$options['data-' . $v] = $options[$v];
				unset($options[$v]);
			}
		}
		// must gain proper class from previous submodule
		$options['flag_call_previous_parent'] = true;
		return \HTML::checkbox($options);
	}
}