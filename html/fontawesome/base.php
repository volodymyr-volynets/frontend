<?php

class numbers_frontend_html_fontawesome_base {

	/**
	 * @see html::icon()
	 */
	public static function icon($options = []) {
		// if we are rendering image
		if (isset($options['file'])) {
			return numbers_frontend_html_class_base::icon($options);
		} else if (isset($options['type'])) {
			library::add('fontawesome');
			// generating class & rendering tag
			$options['class'] = array_add_token($options['class'] ?? [], 'fa fa-' . $options['type'], ' ');
			if (!empty($options['class_only'])) {
				return implode(' ', $options['class']);
			} else {
				$options['tag'] = $options['tag'] ?? 'i';
				return html::tag($options);
			}
		}
	}
}