<?php

namespace Numbers\Frontend\HTML\FontAwesome;
class Base {

	/**
	 * @see \HTML::icon()
	 */
	public static function icon($options = []) {
		// if we are rendering image
		if (isset($options['file'])) {
			return \Numbers\Frontend\HTML\Renderers\Common\Base::icon($options);
		} else if (isset($options['type'])) {
			\Library::add('fontawesome');
			// generating class & rendering tag
			$options['class'] = array_add_token($options['class'] ?? [], 'fa fa-' . $options['type'], ' ');
			if (!empty($options['class_only'])) {
				return implode(' ', $options['class']);
			} else {
				$options['tag'] = $options['tag'] ?? 'i';
				return \HTML::tag($options);
			}
		}
	}
}