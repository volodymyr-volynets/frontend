<?php

class numbers_frontend_html_renderers_semanticui_base extends numbers_frontend_html_renderers_class_base implements numbers_frontend_html_renderers_interface_base {

	public static function segment($options = []) {
		$value = $options['value'] ?? '';
		// todo: proper type handling with global border
		$type = $options['type'] ?? '';
		$header = $options['header'] ?? [];
		$footer = $options['footer'] ?? [];
		array_key_unset($options, ['value', 'type', 'header', 'footer']);
		// single segment if we do not have header nor footer
		if ($header == null && $footer == null) {
			$options['class'] = array_add_token($options['class'] ?? [], ['ui', 'segment', $type], ' ');
			return '<div ' . self::generateAttributes($options) . '>' . $value . '</div>';
		} else {
			$options['class'] = array_add_token($options['class'] ?? [], ['ui', 'segments', $type], ' ');
			$result = '<div ' . self::generateAttributes($options) . '>';
				if ($header != null) {
					if (!empty($type)) {
						$type.= ' message';
					}
					$result.= '<div class="ui segment ' . $type . '">' . $header . '</div>';
				}
				$result.= '<div class="ui segment">' . $value . '</div>';
				if ($footer != null) {
					$result.= '<div class="ui segment secondary">' . $footer . '</div>';
				}
			$result.= '</div>';
			return $result;
		}
	}

	public static function form($options = []) {
		$options['class'] = array_add_token($options['class'] ?? [], 'ui form', ' ');
		return parent::form($options);
	}

	public static function button($options = []) {
		$type = $options['type'] ?? '';
		$options['class'] = array_add_token($options['class'] ?? [], 'ui button ' . $type, ' ');
		return parent::submit($options);
	}

	public static function button2($options = []) {
		$type = $options['type'] ?? '';
		$options['class'] = array_add_token($options['class'] ?? [], 'ui button ' . $type, ' ');
		return parent::submit($options);
	}

	public static function submit($options = []) {
		$type = $options['type'] ?? '';
		$options['class'] = array_add_token($options['class'] ?? [], 'ui button ' . $type, ' ');
		return parent::submit($options);
	}

	public static function grid($options = []) {
		$rows = isset($options['options']) ? $options['options'] : [];
		unset($options['options']);
		$result = '';
		foreach ($rows as $k => $v) {
			// we need to determine field sizes
			$field_sizes = [];
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					$field_sizes[] = $v3['options']['percent'] ?? null;
				}
			}
			$field_new_sizes = \HTML::percentage_to_grid_columns($field_sizes);
			// count number of fields
			$count_fields = count($v);
			$count_class = \HTML::number_to_word($count_fields);
			$result.= '<div class="' . $count_class . ' fields">';
			// we need to determine if we have label in the row
			$flag_have_label = false;
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if (($v3['label'] ?? null) . '' != '') {
						$flag_have_label = true;
					}
				}
			}
			// loop though each field and render it
			$index = 0;
			foreach ($v as $k2 => $v2) {
				$flag_first_field = true;
				foreach ($v2 as $k3 => $v3) {
					$temp = \HTML::number_to_word($field_new_sizes['data'][$index]);
					$result.= '<div class="' . $temp . ' wide field">';
					// label
					if ($flag_first_field) {
						if (($v3['label'] ?? null) . '' != '') {
							$result.= $v3['label'];
						} else if ($flag_have_label) {
							$result.= '<label>&nbsp;</label>';
						}
						$flag_first_field = false;
					} else {
						if ($flag_have_label) {
							$result.= '<label>&nbsp;</label>';
						}
					}
					$result.= $v3['value'] ?? '';
					// todo: add description
					//$v3['description']
					$result.= '</div>';
					$index++;
				}
			}
			$result.= '</div>';
		}
		return $result;
	}
}