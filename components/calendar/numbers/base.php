<?php

class numbers_frontend_components_calendar_numbers_base implements numbers_frontend_components_calendar_interface_base {

	/**
	 * see html::calendar()
	 */
	public static function calendar($options = []) {
		// include js & css files
		if (empty($options['readonly'])) {
			layout::add_js('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_calendar.js');
			layout::add_css('/numbers/media_submodules/numbers_frontend_components_calendar_numbers_calendar.css');
		}
		// font awesome icons
		library::add('fontawesome');
		// widget parameters
		$type = $options['calendar_type'] ?? 'date';
		$widget_options = [
			'id' => $options['id'],
			'type' => $type,
			'format' => $options['calendar_format'] ?? format::get_date_format($type),
			'date_week_start_day' => $options['calendar_date_week_start_day'] ?? 1,
			'date_disable_week_days' => $options['calendar_date_disable_week_days'] ?? null,
			'master_id' => $options['calendar_master_id'] ?? null,
			'slave_id' => $options['calendar_slave_id'] ?? null
		];
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
		if (isset($options['calendar_icon']) && ($options['calendar_icon'] == 'left' || $options['calendar_icon'] == 'right')) {
			$position = $options['calendar_icon'];
			$icon_type = $type == 'time' ? 'clock-o' : 'calendar';
			unset($options['calendar_icon']);
			if (empty($options['readonly'])) {
				$icon_onclick = 'numbers_calendar_var_' . $options['id'] . '.show();';
			} else {
				$icon_onclick = null;
			}
			$icon_value = html::span(['onclick' => $icon_onclick, 'class' => 'numbers_calendar_icon numbers_calendar_prevent_selection', 'value' => html::icon(['type' => $icon_type])]);
			$result = html::input_group(['value' => html::input($options), $position => $icon_value]);
			$div_id = $options['id'] . '_div_holder';
			$result.= html::div(['id' => $div_id, 'class' => 'numbers_calendar_div_holder']);
			$widget_options['holder_div_id'] = $div_id;
		} else {
			$result = html::input($options);
		}
		// we do not render a widget if readonly
		if (empty($options['readonly'])) {
			layout::onload('numbers_calendar(' . json_encode($widget_options) . ');');
		}
		return $result;
	}
}