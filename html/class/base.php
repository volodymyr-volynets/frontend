<?php

/**
 * html class is designed to help generate HTML 5 code
 */
class numbers_frontend_html_class_base implements numbers_frontend_html_interface_base {

	/**
	 * Generate attributes
	 *
	 * @param array $options
	 * @return string
	 */
	private static function generate_attributes($options) {
		$result = [];
		foreach ($options as $k => $v) {
			if (is_array($v)) {
				$v = implode(' ', $v);
			}
			$result[] = $k . '="' . addcslashes($v, '"') . '"';
		}
		return implode(' ', $result);
	}

	/**
	 * Generate selects options
	 *
	 * @param array $options
	 * @return string
	 */
	private static function generate_select_options($options) {
		$result = '';
		foreach($options as $k => $v) {
			$k = (string) $k;
			$text = $v['name'];
			// selected
			$selected = '';
			if (is_array($value) && in_array($k, $value)) {
				$selected = ' selected="selected" ';
			} else if (!is_array($value) && ($value . '') === $k) {
				$selected = ' selected="selected" ';
			}
			$temp = '';
			if (empty($v['disabled'])) {
				unset($v['disabled']);
			} else {
				$v['disabled'] = 'disabled';
			}
			if (empty($v['readonly'])) {
				unset($v['readonly']);
			} else {
				$v['disabled'] = 'disabled';
			}
			foreach($v as $k2 => $v2) {
				if (!is_array($v2) && $k2 != 'name') {
					$temp.= ' ' . $k2 . '="' . $v2 . '"';
				}
			}
			$result.= '<option value="' . $k . '"'. $selected . $temp . '>' . $text . '</option>';
		}
		return $result;
	}

	/**
	 * Link element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function a($options = []) {
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value']);
		return '<a ' . self::generate_attributes($options) . '>' . $value . '</a>';
	}

	/**
	 * Image element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function img($options = []) {
		$options['border'] = isset($options['border']) ? $options['border'] : 0;
		return '<img ' . self::generate_attributes($options) . ' />';
	}

	/**
	 * Script element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function script($options = []) {
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/javascript';
		return '<script ' . self::generate_attributes($options) . '>' . $value . '</script>';
	}

	/**
	 * Style element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function style($options = []) {
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/css';
		return '<style ' . self::generate_attributes($options) . '>' . $value . '</style>';
	}

	/**
	 * Input element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function input($options = []) {
		$options['type'] = isset($options['type']) ? $options['type'] : 'text';
		if (!empty($options['checked'])) {
			$options['checked'] = 'checked';
		} else {
			unset($options['checked']);
		}
		if (!empty($options['readonly'])) {
			$options['readonly'] = 'readonly';
		} else {
			unset($options['readonly']);
		}
		if (!empty($options['disabled'])) {
			$options['disabled'] = 'disabled';
		} else {
			unset($options['disabled']);
		}
		if (!isset($options['autocomplete'])) {
			$options['autocomplete'] = 'off';
		}
		$options['value'] = !empty($options['value']) ? htmlspecialchars($options['value']) : '';
		return '<input ' . self::generate_attributes($options) . ' />';
	}

	/**
	 * Radio element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function radio($options = []) {
		if (!empty($options['checked'])) {
			$options['checked'] = 'checked';
		} else {
			unset($options['checked']);
		}
		if (!empty($options['readonly'])) {
			$options['disabled'] = 'disabled';
		}
		unset($options['options'], $options['readonly']);
		$options['type'] = 'radio';
		return self::input($options);
	}

	/**
	 * Checkbox element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function checkbox($options = []) {
		if (empty($options['value'])) {
			$options['value'] = 1;
		}
		// we will check within an array automatically
		if (!empty($options['checked'])) {
			if (is_array($options['checked'])) {
				if (in_array($options['value'], $options['checked'])) {
					$options['checked'] = true;
				} else {
					unset($options['checked']);
				}
			} else {
				$options['checked'] = true;
			}
		} else {
			unset($options['checked']);
		}
		if (!empty($options['readonly'])) {
			$options['disabled'] = 'disabled';
		}
		unset($options['options'], $options['readonly']);
		$options['type'] = 'checkbox';
		return self::input($options);
	}

	/**
	 * Password element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function password($options = []) {
		$options['type'] = 'password';
		return self::input($options);
	}

	/**
	 * File element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function file($options = []) {
		$options['type'] = 'file';
		return self::input($options);
	}

	/**
	 * Hidden element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hidden($options = []) {
		$options['type'] = 'hidden';
		return self::input($options);
	}

	/**
	 * Textarea element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function textarea($options = []) {
		$options['wrap'] = isset($options['wrap']) ? $options['wrap'] : 'off';
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value'], $options['maxlength']);
		if (empty($options['readonly'])) {
			unset($options['readonly']);
		} else {
			$options['readonly'] = 'readonly';
		}
		return '<textarea ' . self::generate_attributes($options) . '>' . htmlspecialchars($value) . '</textarea>';
	}

	/**
	 * Select element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function select($options = []) {
		$multiselect = null;
		if (isset($options['multiselect'])) {
			$multiselect = $options['multiselect'];
			$multiselect['flag_present'] = true;
		}
		unset($options['multiselect']);
		$no_choose = false;
		if (!empty($options['multiple']) || !empty($multiselect)) {
			$options['name'] = !empty($options['name']) ? $options['name'] . '[]' : '';
			$options['multiple'] = 'multiple';
			$no_choose = true;
		}
		if (!empty($options['no_choose'])) {
			$no_choose = true;
		}
		if (!empty($options['readonly']) || !empty($options['disabled'])) {
			$options['disabled'] = 'disabled';
		} else {
			unset($options['disabled'], $options['readonly']);
		}
		// options & optgroups
		$optgroups_array = !empty($options['optgroups']) ? $options['optgroups'] : [];
		$options_array = !empty($options['options']) ? $options['options'] : [];
		$value = !empty($options['value']) ? $options['value'] : null;
		unset($options['options'], $options['optgroups'], $options['value'], $options['no_choose']);
		// assembling
		$result = '';
		if (!$no_choose) {
			$result.= '<option value=""></option>';
		}
		// options first
		if (!empty($options_array)) {
			$result.= self::generate_select_options($options_array);
		}
		// optgroups second
		if (!empty($optgroups_array)) {
			foreach ($optgroups_array as $k2 => $v2) {
				$result.= '<optgroup label="' . $v2['name'] . '" id="' . $k2 . '">';
					$result.= self::generate_select_options($v2['options']);
				$result.= '</optgroup>';
			}
		}
		return '<select ' . self::generate_attributes($options) . '>' . $result . '</select>';
	}

	/**
	 * An alias for multi select
	 *
	 * @param unknown_type $options
	 * @return string
	 */
	public static function multiselect($options = []) {
		$options['multiple'] = 1;
		return self::select($options);
	}

	/**
	 * Button element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button($options = []) {
		$options['type'] = 'button';
		$options['value'] = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		return self::input($options);
	}

	/**
	 * Button element 2nd edition
	 *
	 * @param array $options
	 * @return string
	 */
	public static function button2($options = []) {
		$options['type'] = isset($options['type']) ? $options['type'] : 'submit';
		$value = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		$options['value'] = !empty($options['name']) ? $options['name'] : 1;
		return '<button ' . self::generate_attributes($options) . '>' . $value . '</button>';
	}

	/**
	 * Submit element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function submit($options = []) {
		$options['type'] = 'submit';
		$options['value'] = isset($options['value']) ? $options['value'] : 'Submit';
		$options['class'] = isset($options['class']) ? $options['class'] : 'button';
		return self::input($options);
	}

	/**
	 * Form element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function form($options = []) {
		$options['method'] = isset($options['method']) ? $options['method'] : 'post';
		$options['action'] = isset($options['action']) ? $options['action'] : '';
		$options['accept-charset'] = isset($options['accept-charset']) ? $options['accept-charset'] : 'utf-8';
		$options['enctype'] = isset($options['enctype']) ? $options['enctype'] : 'multipart/form-data';

		// fragment
		if (!empty($options['fragment'])) {
			$options['action'].= '#' . $options['fragment'];
		}

		// assembling form
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value']);
		return '<form ' . self::generate_attributes($options) . '>' . $value . '</form>';
	}

	/**
	 * Table element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function table($options = []) {
		$rows = isset($options['options']) ? $options['options'] : [];
		$header = isset($options['header']) ? $options['header'] : (!empty($rows) ? array_keys(current($rows)) : []);
		unset($options['options'], $options['header']);
		$temp = [];
		// header first
		if (!empty($header)) {
			$temp2 = '<tr>';
				foreach ($header as $v) {
					$temp2.= '<th>' . $v . '</th>';
				}
			$temp2.= '</tr>';
			$temp[] = $temp2;
		}
		// rows
		foreach ($rows as $k => $v) {
			$temp2 = '<tr>';
				foreach ($v as $v2) {
					if (is_array($v2)) {
						$temp3 = !empty($v2['value']) ? $v2['value'] : '';
						unset($v2['value']);
						$temp2.= '<td' . self::generate_attributes($v2) . '>' . $temp3 . '</td>';
					} else {
						$temp2.= '<td>' . $v2 . '</td>';
					}
				}
			$temp2.= '</tr>';
			$temp[] = $temp2;
		}
		return '<table ' . self::generate_attributes($options) . '>' . implode('', $temp) . '</table>';
	}

	/**
	 * Fieldset element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function fieldset($options = []) {
		$value = !empty($options['value']) ? $options['value'] : '';
		$legend = !empty($options['legend']) ? $options['legend'] : '';
		unset($options['value'], $options['legend']);
		return '<fieldset' . self::generate_attributes($options) . '>' . '<legend>' . $legend . '</legend>' . $value . '</fieldset>';
	}

	/**
	 * List element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function ul($options = []) {
		$value = !empty($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'ul';
		unset($options['options'], $options['type']);
		$temp = [];
		foreach ($value as $v) {
			if (is_array($v)) {
				$temp3 = !empty($v['value']) ? $v['value'] : '';
				unset($v['value']);
				$temp[]= '<li' . self::generate_attributes($v) . '>' . $temp3 . '</li>';
			} else {
				$temp[]= '<li>' . $v . '</li>';
			}
		}
		return '<' . $type . ' ' . self::generate_attributes($options) . '>' . implode('', $temp) . '</' . $type . '>';
	}

	/**
	 * Mandatory tag
	 *
	 * @return string
	 */
	public static function mandatory() {
		return '<span class="mandatory">*</span>';
	}

	/**
	 * Tooltip element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function tooltip($options = []) {
		$value = !empty($options['value']) ? $options['value'] : '';
		unset($options['value']);
		return '<span ' . self::generate_attributes($options) . '>' . $value . '</span>';
	}

	/**
	 * Message
	 *
	 * @param mixed $msg
	 * @param string $type
	 * @return string
	 */
	public static function message($options = []) {
		$value = isset($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'other';
		unset($options['options'], $options['type']);
		$options['class'] = ['message', $type];
		if (!is_array($value)) {
			$value = [$value];
		}
		$error_type_addon = '';
		if ($type == 'error') {
			$error_type_addon = '<b>There was some errors with your submission</b></br/>';
		}
		return '<div ' . self::generate_attributes($options) . '>' . $error_type_addon . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}

	/**
	 * Render frame
	 *
	 * @param array $options
	 * @return string
	 */
	public static function frame($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		$type = isset($options['type']) ? $options['type'] : 'simple';
		unset($options['value'], $options['type']);
		$options['class'] = ['frame', $type];
		return '<div ' . self::generate_attributes($options) . '>' . $value . '</div>';
	}

	/**
	 * Create an element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function element($options = []) {
		$element = isset($options['element']) ? $options['element'] : 'input';
		if (in_array($element, array('select', 'multiselect')) && isset($options['options_model'])) {
			$options_model_class =  $options['options_model'];
			$options_model = new $options_model_class();
			$options['options'] = call_user_func_array(array($options_model, 'options'), isset($options['options_paremeters']) ? $options['options_paremeters'] : []);
		}
		if ($element == 'input' && !empty($options['maxlength'])) {
			$options['size'] = $options['maxlength'];
		}
		if ($element == 'input' && !empty($options['align'])) {
			if (!isset($options['style'])) {
				$options['style'] = '';
			}
			$options['style'].= 'text-align: ' . $options['align'] . ';';
			unset($options['align']);
		}
		if ($element=='checkbox') {
			$options['checked'] = empty($options['value']) ? false : true;
			unset($options['value']);
		}
		// unsettings arrays
		unset($options['options_paremeters'], $options['format_paremeters'], $options['sequence']);
		return call_user_func(['h', $element], $options);
	}
}