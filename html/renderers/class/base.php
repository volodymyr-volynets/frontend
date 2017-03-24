<?php

/**
 * html class is designed to help generate HTML 5 code
 */
class numbers_frontend_html_renderers_class_base implements numbers_frontend_html_renderers_interface_base {

	/**
	 * Generate html based on value in options
	 *
	 * @param mixed $value
	 * @param array $data
	 * @return string
	 */
	public static function render_value_from_options($value, $data) {
		$result = [];
		if (is_array($value)) {
			$common = array_intersect($value, array_keys($data));
			foreach ($common as $k => $v) {
				$i18n = isset($data[$v]['i18n']) ? $data[$v]['i18n'] : null;
				$result[]= i18n($i18n, $data[$v]['name']);
			}
		} else {
			if (isset($data[$value])) {
				$i18n = isset($data[$value]['i18n']) ? $data[$value]['i18n'] : null;
				$result[]= i18n($i18n, $data[$value]['name']);
			}
		}
		return implode(', ', $result);
	}

	/**
	 * Generate HTML tag
	 *
	 * @param string $tag
	 * @param array $options
	 * @return string
	 */
	public static function tag($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		$tag = $options['tag'] ?? 'div';
		unset($options['value'], $options['tag']);
		return '<' . $tag . ' ' . self::generate_attributes($options, $tag) . '>' . $value . '</' . $tag . '>';
	}

	/**
	 * @see Html::div()
	 */
	public static function div($options = []) {
		$options['tag'] = 'div';
		return Html::tag($options);
	}

	/**
	 * Label
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label($options = []) {
		$options['tag'] = 'label';
		return Html::tag($options);
	}

	/**
	 * @see Html::span()
	 */
	public static function span($options = []) {
		$options['tag'] = 'span';
		return Html::tag($options);
	}

	/**
	 * Generate attributes
	 *
	 * @param array $options
	 * @param string $tag
	 * @return string
	 */
	protected static function generate_attributes($options, $tag = null) {
		$result = [];
		foreach ($options as $k => $v) {
			// validate HTML 5 attribute
			if (!numbers_frontend_html_renderers_class_html5::is_valid_html5_attribute($k, $tag)) continue;
			if (in_array($k, numbers_frontend_html_renderers_class_html5::$strip_tags)) {
				$v = strip_tags($v);
			}
			if (is_array($v)) {
				if (array_values($v) !== $v) {
					continue;
				}
				$v = implode(' ', $v);
			}
			$result[] = $k . '="' . htmlentities($v) . '"';
		}
		return implode(' ', $result);
	}

	/**
	 * Generate selects options
	 *
	 * @param array $data
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	private static function generate_select_options($data, $value, $options = []) {
		$result = '';
		foreach($data as $k => $v) {
			$k = (string) $k;
			$text = $v['name'];
			// selected
			$selected = '';
			if (is_array($value) && in_array($k, $value)) {
				$selected = ' selected="selected" ';
			} else if (!is_array($value) && ($value . '') === $k) {
				$selected = ' selected="selected" ';
			}
			// we need to skip certain options
			if (!empty($options['readonly']) && !empty($options['filter_only_selected_options_if_readonly']) && empty($selected)) continue;
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
					$temp.= ' ' . $k2 . '="' . htmlentities($v2) . '"';
				}
			}
			$result.= '<option value="' . htmlentities($k) . '"'. $selected . $temp . '>' . $text . '</option>';
		}
		return $result;
	}

	/**
	 * @see Html::a()
	 */
	public static function a($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value'], $options['options']);
		// HTML5 does not support name, we need to convert it to id
		if (!empty($options['name'])) {
			$options['id'] = $options['name'];
		}
		return '<a ' . self::generate_attributes($options, 'a') . '>' . $value . '</a>';
	}

	/**
	 * @see Html::img()
	 */
	public static function img($options = []) {
		$options['border'] = isset($options['border']) ? $options['border'] : 0;
		return '<img ' . self::generate_attributes($options, 'img') . ' />';
	}

	/**
	 * @see Html::script()
	 */
	public static function script($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/javascript';
		return '<script ' . self::generate_attributes($options, 'script') . '>' . $value . '</script>';
	}

	/**
	 * Style element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function style($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/css';
		return '<style ' . self::generate_attributes($options, 'style') . '>' . $value . '</style>';
	}

	/**
	 * @see Html::input()
	 */
	public static function input($options = []) {
		$options['type'] = $options['input_type'] ?? $options['type'] ?? 'text';
		if (!empty($options['checked'])) {
			$options['checked'] = 'checked';
		} else {
			unset($options['checked']);
		}
		if (!empty($options['multiple'])) {
			$options['multiple'] = 'multiple';
		} else {
			unset($options['multiple']);
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
		if (!empty($options['autofocus'])) {
			$options['autofocus'] = 'autofocus';
		} else {
			unset($options['autofocus']);
		}
		// rtl
		$rtl = I18n::rtl(false);
		if (I18n::rtl()) {
			if (isset($options['style'])) $options['style'] = str_replace(['text-align:right;', 'text-align: right;'], 'text-align:left;', $options['style']);
			// let browser decide the direction based on content
			$rtl = ' dir="auto" ';
		}
		// generate html
		$options['value'] = isset($options['value']) ? htmlspecialchars($options['value']) : '';
		return '<input ' . self::generate_attributes($options, 'input') . $rtl . ' />';
	}

	/**
	 * @see Html::input_group()
	 */
	public static function input_group($options = []) {
		$temp = [];
		foreach (['left', 'center', 'right'] as $k0) {
			if ($k0 == 'center') {
				$temp[] = $options['value'];
			} else {
				if (!empty($options[$k0])) {
					if (!is_array($options[$k0])) {
						$options[$k0] = [$options[$k0]];
					}
					foreach ($options[$k0] as $k => $v) {
						$temp[] = Html::span(['value' => $v, 'class' => 'input_group_' . $k0]);
					}
				}
			}
		}
		unset($options['left'], $options['right']);
		$options['value'] = implode('', $temp);
		$options['class'] = 'input_group';
		return Html::div($options);
	}

	/**
	 * @see Html::radio()
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
		return Html::input($options);
	}

	/**
	 * @see Html::radio()
	 */
	public static function checkbox($options = []) {
		if (!empty($options['value'])) {
			$options['checked'] = 'checked';
		}
		$options['value'] = 1;
		// readonly
		if (!empty($options['readonly'])) {
			$options['disabled'] = 'disabled';
		}
		unset($options['options'], $options['readonly']);
		$options['type'] = 'checkbox';
		return Html::input($options);
	}

	/**
	 * @see Html::password()
	 */
	public static function password($options = []) {
		$options['type'] = 'password';
		return Html::input($options);
	}

	/**
	 * File element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function file($options = []) {
		$options['type'] = 'file';
		return Html::input($options);
	}

	/**
	 * Hidden element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hidden($options = []) {
		$options['type'] = 'hidden';
		return Html::input($options);
	}

	/**
	 * Textarea element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function textarea($options = []) {
		$options['wrap'] = isset($options['wrap']) ? $options['wrap'] : 'off';
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value'], $options['maxlength']);
		if (empty($options['readonly'])) {
			unset($options['readonly']);
		} else {
			$options['readonly'] = 'readonly';
		}
		return '<textarea ' . self::generate_attributes($options, 'textarea') . '>' . htmlspecialchars($value) . '</textarea>';
	}

	/**
	 * @see Html::select()
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
		// disabled
		if (!empty($options['disabled'])) {
			$options['disabled'] = 'disabled';
		} else {
			unset($options['disabled']);
		}
		// readonly
		if (!empty($options['readonly'])) {
			Layout::onload("$('#{$options['id']} option:not(:selected)').prop('disabled', true);");
		}
		// options & optgroups
		$optgroups_array = !empty($options['optgroups']) ? $options['optgroups'] : [];
		$options_array = !empty($options['options']) ? $options['options'] : [];
		$value = isset($options['value']) ? $options['value'] : null;
		unset($options['options'], $options['optgroups'], $options['value'], $options['no_choose']);
		// assembling
		$result = '';
		if (!$no_choose) {
			$result.= '<option value=""></option>';
		}
		// options first
		if (!empty($options_array)) {
			$result.= self::generate_select_options($options_array, $value, $options);
		}
		// optgroups second
		if (!empty($optgroups_array)) {
			$options['optgroups'] = 'optgroups';
			foreach ($optgroups_array as $k2 => $v2) {
				$result.= '<optgroup label="' . $v2['name'] . '" id="' . $k2 . '">';
					$result.= self::generate_select_options($v2['options'], $value, $options);
				$result.= '</optgroup>';
			}
		}
		// convert certain keys
		foreach (['preset', 'searchable', 'tree', 'color_picker', 'optgroups'] as $v) {
			if (isset($options[$v])) {
				$options['data-' . $v] = $options[$v];
				unset($options[$v]);
			}
		}
		return '<select ' . self::generate_attributes($options, 'select') . '>' . $result . '</select>';
	}

	/**
	 * An alias for multi select
	 *
	 * @param unknown_type $options
	 * @return string
	 */
	public static function multiselect($options = []) {
		$options['multiple'] = 1;
		return Html::select($options);
	}

	/**
	 * @see Html::button()
	 */
	public static function button($options = []) {
		$options['type'] = $options['input_type'] ?? $options['type'] ?? 'button';
		$options['value'] = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		return Html::input($options);
	}

	/**
	 * @see Html::button2()
	 */
	public static function button2($options = []) {
		$options['type'] = $options['input_type'] ?? $options['type'] ?? 'submit';
		$value = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		$options['value'] = 1;
		return '<button ' . self::generate_attributes($options, 'button') . '>' . $value . '</button>';
	}

	/**
	 * @see Html::submit()
	 */
	public static function submit($options = []) {
		$options['type'] = 'submit';
		$options['value'] = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		return Html::input($options);
	}

	/**
	 * @see Html::form()
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
		// we need to unset onsubmit if empty
		if (empty($options['onsubmit'])) {
			unset($options['onsubmit']);
		}
		// assembling form
		$value = $options['value'] ?? '';
		unset($options['value']);
		return '<form ' . self::generate_attributes($options, 'form') . '>' . $value . '</form>';
	}

	/**
	 * @see Html::table()
	 */
	public static function table($options = []) {
		$rows = isset($options['options']) ? $options['options'] : [];
		if (!empty($options['header']) && is_array($options['header'])) {
			$header = $options['header'];
		} else {
			// we need to grab header from first row
			$header = current($rows);
			$options['skip_header'] = true;
		}
		$result = [];
		$first_column = null;
		// header first
		if (!empty($header) && empty($options['skip_header'])) {
			$temp2 = '<thead>';
				$temp2.= '<tr>';
					foreach ($header as $k => $v) {
						// determine first column
						if ($first_column === null) {
							$first_column = $k;
						}
						if (is_array($v)) {
							$tag = $v['tag'] ?? 'th';
							$temp_value = isset($v['value']) ? $v['value'] : '';
							unset($v['value'], $v['tag']);
							// we add align to the style
							if (!empty($v['align'])) {
								$v['align'] = Html::align($v['align']);
								$v['style'] = ($v['style'] ?? '') . 'text-align:' . $v['align'] . ';';
							}
							$temp2.= '<' . $tag . ' ' . self::generate_attributes($v, $tag) . '>' . $temp_value . '</' . $tag . '>';
						} else {
							$temp2.= '<th nowrap>' . $v . '</th>';
						}
					}
				$temp2.= '</tr>';
			$temp2.= '</thead>';
			$result[] = $temp2;
		}
		// unsetting some values
		unset($options['options'], $options['header'], $options['skip_header']);
		// rows second
		foreach ($rows as $k => $v) {
			// we need to extract row attributes from first column
			$row_options = [];
			foreach ($header as $k2 => $v2) {
				if (isset($v[$k2]) && is_array($v[$k2])) {
					$row_options = array_merge_hard($row_options, array_key_extract_by_prefix($v[$k2], 'row_'));
				}
			}
			$temp2 = '<tr ' . self::generate_attributes($row_options, 'tr') . '>';
				// important we render based on header array and not on what is in rows
				$flag_colspan = 0;
				foreach ($header as $k2 => $v2) {
					if ($flag_colspan > 0) {
						$flag_colspan--;
						continue;
					}
					if (!isset($v[$k2])) {
						$v[$k2] = null;
					}
					if (is_array($v[$k2])) {
						$tag = $v[$k2]['tag'] ?? 'td';
						$temp_value = $v[$k2]['value'] ?? '';
						unset($v[$k2]['value'], $v[$k2]['tag']);
						// nowrap
						if (!empty($v[$k2]['nowrap'])) {
							$v[$k2]['nowrap'] = 'nowrap';
						}
						// colspan
						if (!empty($v[$k2]['colspan'])) {
							$flag_colspan = $v[$k2]['colspan'];
							$flag_colspan--;
						}
						// we add align to the style
						if (!empty($v[$k2]['align'])) {
							$v[$k2]['align'] = Html::align($v[$k2]['align']);
							$v[$k2]['style'] = ($v[$k2]['style'] ?? '') . 'text-align:' . $v[$k2]['align'] . ';';
						}
						$temp2.= '<' . $tag . ' ' . self::generate_attributes($v[$k2], $tag) . '>' . $temp_value . '</' . $tag . '>';
					} else {
						$temp2.= '<td nowrap>' . $v[$k2] . '</td>';
					}
				}
				// reset colspan
				$flag_colspan = 0;
			$temp2.= '</tr>';
			$result[] = $temp2;
		}
		// todo: add footer
		// todo: maybe use <thead>, <tfoot>, and a <tbody> tags
		return '<table ' . self::generate_attributes($options, 'table') . '>' . implode('', $result) . '</table>';
	}

	/**
	 * @see Html::grid()
	 */
	public static function grid($options = []) {
		$rows = isset($options['options']) ? $options['options'] : [];
		unset($options['options']);
		$data = [
			'header' => [],
			'options' => [],
			'skip_header' => true
		];
		foreach ($rows as $k => $v) {
			$index = 0;
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					$cell = [
						'header' => [0],
						'options' => [
							[$v3['label'] ?? ''],
							[$v3['value'] ?? ''],
							[$v3['description'] ?? '']
						],
						'skip_header' => true
					];
					if (!empty($v3['separator'])) {
						$data['options'][$k][$index] = [
							'value' => $v3['value'],
							'colspan' => 24 // maximum grid count
						];
					} else {
						$data['options'][$k][$index] = Html::table($cell);
					}
					$data['header'][$index] = $index;
					$index++;
				}
			}
		}
		return Html::table($data);
	}

	/**
	 * Fieldset element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function fieldset($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		$legend = isset($options['legend']) ? $options['legend'] : '';
		unset($options['value'], $options['legend']);
		return '<fieldset ' . self::generate_attributes($options, 'fieldset') . '>' . '<legend>' . $legend . '</legend>' . $value . '</fieldset>';
	}

	/**
	 * @see Html::ul()
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
				$temp[]= '<li ' . self::generate_attributes($v, 'li') . '>' . $temp3 . '</li>';
			} else {
				$temp[]= '<li>' . $v . '</li>';
			}
		}
		return '<' . $type . ' ' . self::generate_attributes($options, $type) . '>' . implode('', $temp) . '</' . $type . '>';
	}

	/**
	 * @see Html::mandatory()
	 */
	public static function mandatory($options = []) {
		$asterisk = '';
		switch ($options['type'] ?? '') {
			case 'mandatory':
				$asterisk = '<b style="color: red;" title="' . strip_tags(i18n(null, 'Mandatory')) . '">*</b>';
				$options['tag'] = 'b';
				break;
			case 'conditional':
				$asterisk = '<b style="color: green;" title="' . strip_tags(i18n(null, 'Conditional')) . '">*</b>';
				$options['tag'] = 'b';
				break;
			default:
				$options['tag'] = 'span';
		}
		// if we are formatting value
		if (isset($options['value'])) {
			if (is_array($options['value'])) {
				$options['value']['value'] = $options['value']['value'] . ' ' . $asterisk . ($options['prepend'] ?? '');
			} else {
				$options['value'] = [
					'value' => $options['value'] . ' ' . $asterisk . ($options['prepend'] ?? '')
				];
			}
			$options['value']['tag'] = $options['tag'];
			return Html::tag($options['value']);
		} else {
			return $asterisk;
		}
	}

	/**
	 * Tooltip element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function tooltip($options = []) {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		return '<span ' . self::generate_attributes($options, 'span') . '>' . $value . '</span>';
	}

	/**
	 * @see Html::message()
	 */
	public static function message($options = []) {
		$value = isset($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'other';
		unset($options['options'], $options['type']);
		$options['class'] = ['message', $type];
		if (!is_array($value)) {
			$value = [$value];
		}
		return '<div ' . self::generate_attributes($options, 'div') . '>' . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}

	/**
	 * @see Html::segment()
	 */
	public static function segment($options = []) {
		$value = $options['value'] ?? '';
		$type = $options['type'] ?? 'simple';
		$header = $options['header'] ?? null;
		$footer = $options['footer'] ?? null;
		unset($options['value'], $options['type'], $options['header'], $options['footer']);
		$options['class'] = ['segment', $type];
		$result = '<div ' . self::generate_attributes($options, 'div') . '>';
			if ($header) {
				$result.= '<div class="segment_header">' . $header . '</div>';
			}
			$result.= '<div class="segment_body">' . $value . '</div>';
			if ($footer) {
				$result.= '<div class="segment_footer">' . $footer . '</div>';
			}
		$result.= '</div>';
		return $result;
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
		return call_user_func(['html', $element], $options);
	}

	/**
	 * Calendar
	 *
	 * @param array $options
	 * @return string
	 */
	public static function calendar($options = []) {
		return Html::input($options);
	}

	/**
	 * @see Html::icon()
	 */
	public static function icon($options = []) {
		// if we are rendering image
		if (isset($options['file'])) {
			$name = $options['file'];
			if (isset($options['path'])) {
				$path = $options['path'];
			}
			$options['src'] = $path . $name;
			if (!isset($options['style'])) {
				$options['style'] = 'vertical-align: middle;';
			}
			array_key_unset($options, ['file', 'path']);
			// we need to get width and height of the image from the end of filename
			if (preg_match('/([0-9]+)(x([0-9]+))?./', $name, $matches)) {
				if (isset($matches[1])) {
					$options['width'] = $matches[1];
				}
				if (isset($matches[3])) {
					$options['height'] = $matches[1];
				}
			}
			return Html::img($options);
		} else if (isset($options['type'])) {
			$options['class'] = array_add_token($options['class'] ?? [], 'icon ' . $options['type'], ' ');
			$options['tag'] = $options['tag'] ?? 'i';
			return Html::tag($options);
		}
	}

	/**
	 * @see Html::menu()
	 */
	public static function menu($options = []) {
		//Throw new Exception('Menu?');
		return;
	}

	/**
	 * @see Html::modal()
	 */
	public static function modal($options = []) {
		//Throw new Exception('Modal?');
		return;
	}

	/**
	 * @see Html::tabs();
	 */
	public static function tabs($options = []) {
		Throw new Exception('Tabs?');
	}

	/**
	 * @see Html::pills();
	 */
	public static function pills($options = []) {
		Throw new Exception('Pills?');
	}

	/**
	 * @see Html::separator()
	 */
	public static function separator($options = []) {
		$value = $options['value'] ?? null;
		$icon = $options['icon'] ?? null;
		$result = '';
		$result.= '<table width="100%">';
			$result.= '<tr><td width="50%"><hr/></td><td width="1%" nowrap><b>' . Html::name($value, $icon) . '</b></td><td width="50%"><hr/></td></tr>';
		$result.= '</table>';
		return $result;
	}

	/**
	 * HR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hr($options = []) {
		return '<hr ' . self::generate_attributes($options, 'hr') . ' />';
	}

	/**
	 * BR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function br($options = []) {
		return '<br ' . self::generate_attributes($options, 'br') . ' />';
	}
}