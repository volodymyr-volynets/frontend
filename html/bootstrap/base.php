<?php

class numbers_frontend_html_bootstrap_base extends numbers_frontend_html_class_base implements numbers_frontend_html_interface_base {

	/**
	 * see html::segment()
	 */
	public static function segment($options = []) {
		$value = $options['value'] ?? '';
		$type = $options['type'] ?? '';
		if (!empty($type)) {
			$type = 'panel-' . $type;
		} else {
			$type = 'panel-default';
		}
		$header = $options['header'] ?? null;
		$footer = $options['footer'] ?? null;
		// todo: process type here
		unset($options['value'], $options['type'], $options['header'], $options['footer']);
		$options['class'] = array_add_token($options['class'] ?? [], ['panel', $type], ' ');
		$result = '<div ' . self::generate_attributes($options) . '>';
			if ($header != null) {
				$result.= '<div class="panel-heading">' . $header . '</div>';
			}
			$result.= '<div class="panel-body">' . $value . '</div>';
			if ($footer != null) {
				$result.= '<div class="panel-footer">' . $footer . '</div>';
			}
		$result.= '</div>';
		return $result;
	}

	/**
	 * see html::input()
	 */
	public static function input($options = []) {
		if (!in_array($options['type'] ?? 'text', ['button', 'submit'])) {
			$options['class'] = array_add_token($options['class'] ?? [], 'form-control', ' ');
		}
		return parent::input($options);
	}

	/**
	 * see html::select()
	 */
	public static function select($options = []) {
		$options['class'] = array_add_token($options['class'] ?? [], 'form-control', ' ');
		return parent::select($options);
	}

	/**
	 * see html::form()
	 */
	public static function form($options = []) {
		$options['role'] = 'form';
		return parent::form($options);
	}

	/**
	 * see html::button()
	 */
	public static function button($options = []) {
		$type = $options['type'] ?? 'default';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::submit($options);
	}

	/**
	 * see html::button2()
	 */
	public static function button2($options = []) {
		$type = $options['type'] ?? 'default';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::submit($options);
	}

	/**
	 * see html::submit()
	 */
	public static function submit($options = []) {
		$type = $options['type'] ?? 'default';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::submit($options);
	}

	/**
	 * see html::table()
	 */
	public static function table($options = []) {
		$options['class'] = array_add_token($options['class'] ?? [], 'table', ' ');
		return '<div class="table-responsive">' . parent::table($options) . '</div>';
	}

	/**
	 * see html::grid()
	 */
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
			$field_new_sizes = html::percentage_to_grid_columns($field_sizes);
			// count number of fields
			$count_fields = count($v);
			//$count_class = html::number_to_word($count_fields);
			$result.= '<div class="row">';
			// we need to determine if we have label in the row
			$flag_have_label = false;
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if (($v3['label'] ?? '') . '' != '') {
						$flag_have_label = true;
					}
				}
			}
			// loop though each field and render it
			$index = 0;
			foreach ($v as $k2 => $v2) {
				$flag_first_field = true;
				foreach ($v2 as $k3 => $v3) {
					//$temp = html::number_to_word($field_new_sizes['data'][$index]);
					$result.= '<div class="col-sm-' . $field_new_sizes['data'][$index] . ' form-group">';
						//$result.= '<div class="form-group">';
						// label
						if ($flag_first_field) {
							if (($v3['label'] ?? '') .'' != '') {
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
						//$result.= '</div>';
					$result.= '</div>';
					$index++;
				}
			}
			$result.= '</div>';
		}
		return $result;
	}
}