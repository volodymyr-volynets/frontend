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
	 * @see html::input_group()
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
						$temp[] = html::span(['value' => $v, 'class' => 'input-group-addon']);
					}
				}
			}
		}
		unset($options['left'], $options['right']);
		$options['value'] = implode('', $temp);
		$options['class'] = 'input-group';
		return html::div($options);
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

	/**
	 * Generate submenu
	 *
	 * @param array $item
	 * @return string
	 */
	private static function menu_submenu($item, $level) {
		$level++;
		$caret = $level == 1 ? ' <b class="caret"></b>' : '';
		//todo: add translation
		$result = html::a(['href' => $item['url'], 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'value' => $item['name'] . $caret]);
		$result.= '<ul class="dropdown-menu">';
			foreach ($item['options'] as $k2 => $v2) {
				$class = !empty($v2['options']) ? ' class="dropdown-submenu"' : '';
				$result.= '<li' . $class . '>';
					if (!empty($v2['options'])) {
						$result.= self::menu_submenu($v2, $level);
					} else {
						//todo: add translation
						if (!empty($v2['url'])) {
							$result.= html::a(['href' => $v2['url'], 'value' => $v2['name']]);
						} else {
							$result.= $v2['name'];
						}
					}
				$result.= '</li>';
			}
		$result.= '</ul>';
		return $result;
	}

	/**
	 * @see html::menu()
	 */
	public static function menu($options = []) {
		$items = $options['options'] ?? [];
		$brand = $options['brand'] ?? null;
		array_key_unset($options, ['options', 'brand']);
		$result = '<div class="navbar navbar-default" role="navigation">';
			$result.= '<div class="container">';
				$result.= '<div class="navbar-header">';
					$result.= '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">';
						$result.= '<span class="sr-only">Toggle navigation</span>';
						$result.= '<span class="icon-bar"></span>';
						$result.= '<span class="icon-bar"></span>';
						$result.= '<span class="icon-bar"></span>';
					$result.= '</button>';
					$result.= '<a class="navbar-brand" href="/">' . $brand . '</a>';
				$result.= '</div>';
				$result.= '<div class="collapse navbar-collapse navbar-nav-fix">';
					$result.= '<ul class="nav navbar-nav">';
						$index = 1;
						foreach ($items as $k => $v) {
							$result.= '<li class="navbar-nav-li-level1" search-id="' . $index . '">';
								// if we have options
								if (!empty($v['options'])) {
									$result.= self::menu_submenu($v, 0);
								} else {
									//todo: add translation
									if (!empty($v['url'])) {
										$result.= html::a(['href' => $v['url'], 'value' => $v['name']]);
									} else {
										$result.= $v['name'];
									}
								}
							$result.= '</li>';
							$items[$k]['index'] = $index;
							$index++;
						}
						// and we need to add all tabs again into others tab
						$result.= '<li search-id="0" class="navbar-nav-others">';
							$result.= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">More <b class="caret"></b></a>';
							$result.= '<ul class="dropdown-menu navbar-nav-others-ul">'; //multi-level
								foreach ($items as $k => $v) {
									$class = !empty($v['options']) ? ' dropdown-submenu' : '';
									$result.= '<li class="navbar-nav-li-level1-others' . $class . '" search-id="' . $v['index'] . '">';
										// if we have options
										if (!empty($v['options'])) {
											$result.= self::menu_submenu($v, 1);
										} else {
											// todo: add translation
											if (!empty($v['url'])) {
												$result.= html::a(['href' => $v['url'], 'value' => $v['name']]);
											} else {
												$result.= $v['name'];
											}
										}
									$result.= '</li>';
								}
							$result.= '</ul>';
						$result.= '</li>';
					$result.= '</ul>';
				$result.= '</div>';
			$result.= '</div>';
		$result.= '</div>';
		return $result;
	}

	/**
	 * @see html::message()
	 */
	public static function message($options = []) {
		$value = isset($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'other';
		unset($options['options'], $options['type']);
		// we need to do replaces
		$type2 = $type;
		if ($type == 'error') $type2 = 'danger';
		$options['class'] = ['alert', 'alert-' . $type2];
		if (!is_array($value)) {
			$value = [$value];
		}
		$error_type_addon = '';
		if ($type == 'error') {
			$error_type_addon = '<b>There was some errors with your submission:</b></br/>';
		}
		return '<div role="alert" ' . self::generate_attributes($options) . '>' . $error_type_addon . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}
}