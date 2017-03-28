<?php

class numbers_frontend_components_calendar_numbers_base implements numbers_frontend_components_calendar_interface_base {

	/**
	 * see \HTML::calendar()
	 */
	public static function calendar($options = []) {
		// include js & css files
		if (empty($options['readonly'])) {
			Layout::add_js('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_media_js_base.js');
			Layout::add_css('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_media_css_base.css');
		}
		// font awesome icons
		library::add('fontawesome');
		// widget parameters
		$type = $options['calendar_type'] ?? $options['type'] ?? 'date';
		$widget_options = [
			'id' => $options['id'],
			'type' => $type,
			'format' => $options['calendar_format'] ?? Format::get_date_format($type),
			'date_week_start_day' => $options['calendar_date_week_start_day'] ?? 1,
			'date_disable_week_days' => $options['calendar_date_disable_week_days'] ?? null,
			'master_id' => $options['calendar_master_id'] ?? null,
			'slave_id' => $options['calendar_slave_id'] ?? null
		];
		$options['type'] = 'text';
		// determine input size
		$placeholder = Format::get_date_placeholder($widget_options['format']);
		$options['size'] = strlen($placeholder);
		// set placeholder
		if (!empty($options['placeholder']) && $options['placeholder'] == 'Format::get_date_placeholder') {
			$options['placeholder'] = $placeholder;
			$options['title'] = ($options['title'] ?? '') . ' (' . $placeholder . ')';
		}
		if (isset($options['calendar_icon']) && ($options['calendar_icon'] == 'left' || $options['calendar_icon'] == 'right')) {
			$position = \HTML::align($options['calendar_icon']);
			$icon_type = $type == 'time' ? 'clock-o' : 'calendar';
			unset($options['calendar_icon']);
			if (empty($options['readonly'])) {
				$icon_onclick = 'numbers_calendar_var_' . $options['id'] . '.show();';
			} else {
				$icon_onclick = null;
			}
			$icon_value = \HTML::span(['onclick' => $icon_onclick, 'class' => 'numbers_calendar_icon numbers_prevent_selection', 'value' => \HTML::icon(['type' => $icon_type])]);
			$result = \HTML::input_group(['value' => \HTML::input($options), $position => $icon_value, 'dir' => 'ltr']);
			$div_id = $options['id'] . '_div_holder';
			$result.= \HTML::div(['id' => $div_id, 'class' => 'numbers_calendar_div_holder']);
			$widget_options['holder_div_id'] = $div_id;
		} else {
			$result = \HTML::input($options);
		}
		// we do not render a widget if readonly
		if (empty($options['readonly'])) {
			Layout::onload('numbers_calendar(' . json_encode($widget_options) . ');');
		}
		return $result;
	}
}