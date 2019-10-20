<?php

namespace Numbers\Frontend\Components\Select\Numbers;
class Base implements \Numbers\Frontend\Components\Select\Interface2\Base {

	/**
	 * see \HTML::select();
	 */
	public static function select(array $options = []) : string {
		// we do not process readonly selects
		if (empty($options['readonly'])) {
			// include js & css files
			\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Select_Numbers_Media_JS_Base.js', -10000);
			\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Select_Numbers_Media_CSS_Base.css', -10000);
			// font awesome icons
			\Library::add('FontAwesome');
			// id with name
			if (empty($options['id']) && !empty($options['name'])) {
				$options['id'] = $options['name'];
			}
			\Layout::onload('NumbersSelect(' . json_encode([
				'id' => $options['id'],
				'class' => $options['class'] ?? ''
			]) . ');');
		}
		// must gain proper class from previous submodule
		$options['flag_call_previous_parent'] = true;
		return \HTML::select($options);
	}

	/**
	 * see \HTML::multiselect();
	 */
	public static function multiselect(array $options = []) : string {
		$options['multiple'] = 1;
		return self::select($options);
	}
}