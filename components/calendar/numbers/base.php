<?php

class numbers_frontend_components_calendar_numbers_base implements numbers_frontend_components_calendar_interface_base {

	/**
	 * Render calendar widget
	 *
	 * @param array $options
	 * @return string
	 */
	public static function calendar($options = []) {
		// include js & css files
		layout::add_js('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_calendar.js');
		layout::add_css('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_calendar.css');
		// font awesome icons
		library::add('fontawesome');
		// widget parameters
		$type = $options['calendar_type'] ?? 'date';
		$widget_options = [
			'id' => $options['id'],
			'type' => $type,
			'format' => $options['calendar_format'] ?? format::get_date_format($type),
			'date_week_start_day' => $options['date_week_start_day'] ?? 1,
			'date_disable_week_days' => $options['date_disable_week_days'] ?? null,
			'master_id' => $options['calendar_master_id'] ?? null,
			'slave_id' => $options['calendar_slave_id'] ?? null,
			'append_icon' => $options['calendar_append_icon'] ?? false,
		];
		layout::onload('numbers_calendar(' . json_encode($widget_options) . ');');
		// rendering input
		if ($type == 'time') {
			$options['size'] = $options['size'] ?? 11;
		} else if ($type == 'datetime') {
			$options['size'] = $options['size'] ?? 22;
		} else {
			$options['size'] = $options['size'] ?? 13;
		}
		if (!empty($options['calendar_placeholder'])) {
			$options['placeholder'] = format::get_date_placeholder($widget_options['format']);
		}
		return html::input($options);
	}
}