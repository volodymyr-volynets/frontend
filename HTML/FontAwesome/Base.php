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
			\Library::add('FontAwesome');
			// generating class & rendering tag
			$options['class'] = ($options['class'] ?? '') . ' ' . $options['type'];
			if (!empty($options['class_only'])) {
				return $options['class'];
			} else {
				$options['tag'] = $options['tag'] ?? 'i';
				return \HTML::tag($options);
			}
		}
	}
}