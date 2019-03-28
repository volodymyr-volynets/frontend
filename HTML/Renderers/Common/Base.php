<?php

/**
 * html class is designed to help generate HTML 5 code
 */
namespace Numbers\Frontend\HTML\Renderers\Common;
class Base implements \Numbers\Frontend\HTML\Renderers\Common\Interface2\Base {

	/**
	 * Is email
	 *
	 * @var boolean
	 */
	protected static $is_email = false;

	/**
	 * Email
	 *
	 * @param bool $status
	 */
	public static function setMode(bool $email) {
		if (\Can::submoduleExists('Numbers.Frontend.HTML.Renderers.DOMParser')) {
			self::$is_email = $status;
		}
	}

	/**
	 * Generate html based on value in options
	 *
	 * @param mixed $value
	 * @param array $data
	 * @return string
	 */
	public static function renderValueFromOptions($value, array $data) : string {
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
	public static function tag(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		$tag = $options['tag'] ?? 'div';
		if (!empty($options['align'])) {
			$options['style'] = ($options['style'] ?? '') . 'text-align: ' . $options['align'] . ';';
		}
		unset($options['value'], $options['tag']);
		return '<' . $tag . ' ' . self::generateAttributes($options, $tag) . '>' . $value . '</' . $tag . '>';
	}

	/**
	 * @see \HTML::div()
	 */
	public static function div(array $options = []) : string {
		$options['tag'] = 'div';
		return \HTML::tag($options);
	}

	/**
	 * Label
	 *
	 * @param array $options
	 * @return string
	 */
	public static function label(array $options = []) : string {
		$options['tag'] = 'label';
		return \HTML::tag($options);
	}

	/**
	 * @see \HTML::span()
	 */
	public static function span(array $options = []) : string {
		$options['tag'] = 'span';
		return \HTML::tag($options);
	}

	/**
	 * Generate attributes
	 *
	 * @param array $options
	 * @param string $tag
	 * @return string
	 */
	protected static function generateAttributes($options, $tag = null) {
		$result = [];
		foreach ($options as $k => $v) {
			// validate HTML 5 attribute
			if (!\Numbers\Frontend\HTML\Renderers\Common\HTML5::isValidHTML5Attribute($k, $tag)) continue;
			if (in_array($k, \Numbers\Frontend\HTML\Renderers\Common\HTML5::$strip_tags)) {
				$v = strip_tags($v);
			}
			if (is_array($v)) {
				if (array_values($v) !== $v) {
					continue;
				}
				$v = implode(' ', $v);
			}
			if ($k == 'src') {
				$result[] = $k . '="' . $v . '"';
			} else {
				$result[] = $k . '="' . htmlentities($v) . '"';
			}
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
	private static function generateSelectOptions($data, $value, $options = []) {
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
	 * @see \HTML::a()
	 */
	public static function a(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value'], $options['options']);
		// HTML5 does not support name, we need to convert it to id
		if (!empty($options['name'])) {
			$options['id'] = $options['name'];
		}
		return '<a ' . self::generateAttributes($options, 'a') . '>' . $value . '</a>';
	}

	/**
	 * @see \HTML::img()
	 */
	public static function img(array $options = []) : string {
		$options['border'] = isset($options['border']) ? $options['border'] : 0;
		return '<img ' . self::generateAttributes($options, 'img') . ' />';
	}

	/**
	 * @see \HTML::script()
	 */
	public static function script(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/javascript';
		return '<script ' . self::generateAttributes($options, 'script') . '>' . $value . '</script>';
	}

	/**
	 * Style element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function style(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		$options['type'] = !empty($options['type']) ? $options['type'] : 'text/css';
		return '<style ' . self::generateAttributes($options, 'style') . '>' . $value . '</style>';
	}

	/**
	 * @see \HTML::input()
	 */
	public static function input(array $options = []) : string {
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
			unset($options['placeholder']);
		} else {
			unset($options['readonly']);
		}
		if (!empty($options['disabled'])) {
			$options['disabled'] = 'disabled';
			unset($options['placeholder']);
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
		$rtl = \I18n::rtl(false);
		if (\I18n::rtl()) {
			if (isset($options['style'])) $options['style'] = str_replace(['text-align:right;', 'text-align: right;'], 'text-align:left;', $options['style']);
			// let browser decide the direction based on content
			$rtl = ' dir="auto" ';
		}
		// generate html
		$options['value'] = isset($options['value']) ? htmlspecialchars($options['value'], ENT_COMPAT, 'UTF-8') : '';
		return '<input ' . self::generateAttributes($options, 'input') . $rtl . ' />';
	}

	/**
	 * @see \HTML::input_group()
	 */
	public static function inputGroup(array $options = []) : string {
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
						$temp[] = \HTML::span(['value' => $v, 'class' => 'input_group_' . $k0]);
					}
				}
			}
		}
		unset($options['left'], $options['right']);
		$options['value'] = implode('', $temp);
		$options['class'] = 'input_group';
		return \HTML::div($options);
	}

	/**
	 * @see \HTML::radio()
	 */
	public static function radio(array $options = []) : string {
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
		return \HTML::input($options);
	}

	/**
	 * @see \HTML::radio()
	 */
	public static function checkbox(array $options = []) : string {
		if (!empty($options['value'])) {
			$options['checked'] = 'checked';
		}
		$options['value'] = 1;
		unset($options['options']);
		$options['type'] = 'checkbox';
		return \HTML::input($options);
	}

	/**
	 * @see \HTML::password()
	 */
	public static function password(array $options = []) : string {
		$options['type'] = 'password';
		return \HTML::input($options);
	}

	/**
	 * File element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function file(array $options = []) : string {
		$options['type'] = 'file';
		unset($options['value']);
		if (!empty($options['multiple']) && strpos($options['name'] ?? '', '[]') === false) {
			$options['name'].= '[]';
		}
		return \HTML::input($options);
	}

	/**
	 * Hidden element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hidden(array $options = []) : string {
		$options['type'] = 'hidden';
		return \HTML::input($options);
	}

	/**
	 * Textarea element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function textarea(array $options = []) : string {
		$options['wrap'] = isset($options['wrap']) ? $options['wrap'] : 'off';
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value'], $options['maxlength']);
		if (empty($options['readonly'])) {
			unset($options['readonly']);
		} else {
			$options['readonly'] = 'readonly';
		}
		return '<textarea ' . self::generateAttributes($options, 'textarea') . '>' . htmlspecialchars($value) . '</textarea>';
	}

	/**
	 * @see \HTML::select()
	 */
	public static function select(array $options = []) : string {
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
			$options['onmousedown'] = 'return false;';
			$options['onkeydown'] = 'return false;';
			\Layout::onload("$('#{$options['id']} option:not(:selected)').prop('disabled', true);");
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
			$result.= self::generateSelectOptions($options_array, $value, $options);
		}
		// optgroups second
		if (!empty($optgroups_array)) {
			$options['optgroups'] = 'optgroups';
			foreach ($optgroups_array as $k2 => $v2) {
				$result.= '<optgroup label="' . $v2['name'] . '" id="' . $k2 . '">';
					$result.= self::generateSelectOptions($v2['options'], $value, $options);
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
		return '<select ' . self::generateAttributes($options, 'select') . '>' . $result . '</select>';
	}

	/**
	 * An alias for multi select
	 *
	 * @param unknown_type $options
	 * @return string
	 */
	public static function multiselect(array $options = []) : string {
		$options['multiple'] = 1;
		return \HTML::select($options);
	}

	/**
	 * @see \HTML::button()
	 */
	public static function button(array $options = []) : string {
		$options['type'] = $options['input_type'] ?? $options['type'] ?? 'button';
		$options['value'] = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		return \HTML::input($options);
	}

	/**
	 * @see \HTML::button2()
	 */
	public static function button2(array $options = []) : string {
		$options['type'] = $options['input_type'] ?? $options['type'] ?? 'submit';
		$value = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		$options['value'] = 1;
		return '<button ' . self::generateAttributes($options, 'button') . '>' . $value . '</button>';
	}

	/**
	 * @see \HTML::submit()
	 */
	public static function submit(array $options = []) : string {
		$options['type'] = 'submit';
		$options['value'] = $options['value'] ?? strip_tags(i18n(null, 'Submit'));
		$options['class'] = $options['class'] ?? 'button';
		return \HTML::input($options);
	}

	/**
	 * @see \HTML::form()
	 */
	public static function form(array $options = []) : string {
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
		return '<form ' . self::generateAttributes($options, 'form') . '>' . $value . '</form>';
	}

	/**
	 * @see \HTML::table()
	 */
	public static function table(array $options = []) : string {
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
								$v['align'] = \HTML::align($v['align']);
								$v['style'] = ($v['style'] ?? '') . 'text-align:' . $v['align'] . ';';
							}
							$temp2.= '<' . $tag . ' ' . self::generateAttributes($v, $tag) . '>' . $temp_value . '</' . $tag . '>';
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
			$temp2 = '<tr ' . self::generateAttributes($row_options, 'tr') . '>';
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
							$v[$k2]['align'] = \HTML::align($v[$k2]['align']);
							$v[$k2]['style'] = ($v[$k2]['style'] ?? '') . 'text-align:' . $v[$k2]['align'] . ';';
						}
						$temp2.= '<' . $tag . ' ' . self::generateAttributes($v[$k2], $tag) . '>' . $temp_value . '</' . $tag . '>';
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
		return '<table ' . self::generateAttributes($options, 'table') . '>' . implode('', $result) . '</table>';
	}

	/**
	 * @see \HTML::grid()
	 */
	public static function grid(array $options = []) : string {
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
						$data['options'][$k][$index] = \HTML::table($cell);
					}
					$data['header'][$index] = $index;
					$index++;
				}
			}
		}
		return \HTML::table($data);
	}

	/**
	 * Fieldset element
	 *
	 * @param array $options
	 * @return string
	 */
	public static function fieldset(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		$legend = isset($options['legend']) ? $options['legend'] : '';
		unset($options['value'], $options['legend']);
		return '<fieldset ' . self::generateAttributes($options, 'fieldset') . '>' . '<legend>' . $legend . '</legend>' . $value . '</fieldset>';
	}

	/**
	 * @see \HTML::ul()
	 */
	public static function ul(array $options = []) : string {
		$value = !empty($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'ul';
		unset($options['options'], $options['type']);
		$temp = [];
		foreach ($value as $v) {
			if (is_array($v)) {
				$temp3 = !empty($v['value']) ? $v['value'] : '';
				unset($v['value']);
				$temp[]= '<li ' . self::generateAttributes($v, 'li') . '>' . nl2br($temp3) . '</li>';
			} else {
				$temp[]= '<li>' . nl2br(str_replace("\t", '&nbsp;&nbsp;&nbsp;', $v)) . '</li>';
			}
		}
		return '<' . $type . ' ' . self::generateAttributes($options, $type) . '>' . implode('', $temp) . '</' . $type . '>';
	}

	/**
	 * @see \HTML::mandatory()
	 */
	public static function mandatory(array $options = []) : string {
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
			return \HTML::tag($options['value']);
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
	public static function tooltip(array $options = []) : string {
		$value = isset($options['value']) ? $options['value'] : '';
		unset($options['value']);
		return '<span ' . self::generateAttributes($options, 'span') . '>' . $value . '</span>';
	}

	/**
	 * @see \HTML::message()
	 */
	public static function message(array $options = []) : string {
		$value = isset($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'other';
		unset($options['options'], $options['type']);
		$options['class'] = ['message', $type];
		if (!is_array($value)) {
			$value = [$value];
		}
		return '<div ' . self::generateAttributes($options, 'div') . '>' . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}

	/**
	 * @see \HTML::segment()
	 */
	public static function segment(array $options = []) : string {
		$value = $options['value'] ?? '';
		$type = $options['type'] ?? 'simple';
		$header = $options['header'] ?? null;
		$footer = $options['footer'] ?? null;
		unset($options['value'], $options['type'], $options['header'], $options['footer']);
		$options['class'] = ['segment', $type];
		$result = '<div ' . self::generateAttributes($options, 'div') . '>';
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
	public static function element(array $options = []) : string {
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
	public static function calendar(array $options = []) : string {
		return \HTML::input($options);
	}

	/**
	 * @see \HTML::icon()
	 */
	public static function icon(array $options = []) : string {
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
			return \HTML::img($options);
		} else if (isset($options['type'])) {
			$options['class'] = array_add_token($options['class'] ?? [], 'icon ' . $options['type'], ' ');
			$options['tag'] = $options['tag'] ?? 'i';
			return \HTML::tag($options);
		}
	}

	/**
	 * @see \HTML::flag()
	 */
	public static function flag(array $options = []) : string {
		return '';
	}

	/**
	 * @see \HTML::menu();
	 */
	public static function menu(array $options = []) : string {
		return '';
	}

	/**
	 * @see \HTML::modal();
	 */
	public static function modal(array $options = []) : string {
		return '';
	}

	/**
	 * @see \HTML::tabs();
	 */
	public static function tabs(array $options = []) : string {
		return '';
	}

	/**
	 * @see \HTML::pills();
	 */
	public static function pills(array $options = []) : string {
		return '';
	}

	/**
	 * @see \HTML::separator()
	 */
	public static function separator(array $options = []) : string {
		$value = $options['value'] ?? null;
		$icon = $options['icon'] ?? null;
		$result = '';
		$result.= '<table width="100%">';
			$result.= '<tr><td width="50%"><hr/></td>';
				if (!empty($value) || !empty($icon)) {
					$result.= '<td width="1%" nowrap><b>' . \HTML::name($value, $icon) . '</b></td>';
				}
			$result.= '<td width="50%"><hr/></td></tr>';
		$result.= '</table>';
		return $result;
	}

	/**
	 * HR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function hr(array $options = []) : string {
		return '<hr ' . self::generateAttributes($options, 'hr') . ' />';
	}

	/**
	 * BR
	 *
	 * @param array $options
	 * @return string
	 */
	public static function br(array $options = []) : string {
		return '<br ' . self::generateAttributes($options, 'br') . ' />';
	}

	/**
	 * Bold
	 *
	 * @param array $options
	 * @return string
	 */
	public static function b(array $options = []) : string {
		$value = $options['value'];
		unset($options['value']);
		return '<b ' . self::generateAttributes($options, 'b') . '>' . $value . '</b>';
	}

	/**
	 * Audio
	 *
	 * @param array $options
	 * @return string
	 */
	public static function audio(array $options = []) : string {
		$result = '<audio controls>';
			$result.= '<source src="' . $options['src'] . '" type="' . $options['mime'] . '">';
			$result.= 'Your browser does not support the audio element.';
		$result.= '</audio>';
		return $result;
	}

	/**
	 * @see \HTML::iframe()
	 */
	public static function iframe(array $options = []) : string {
		$options['tag'] = 'iframe';
		return \HTML::tag($options);
	}
}