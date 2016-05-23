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
				if (is_array($header)) {
					$icon = !empty($header['icon']) ? (html::icon($header['icon']) . ' ') : null;
					$result.= '<div class="panel-heading">' . $icon . i18n(null, $header['title']) . '</div>';
				} else {
					$result.= '<div class="panel-heading">' . i18n(null, $header) . '</div>';
				}
			}
			$result.= '<div class="panel-body">' . $value . '</div>';
			if ($footer != null) {
				if (is_array($footer)) {
					$icon = !empty($footer['icon']) ? (html::icon($footer['icon']) . ' ') : null;
					$result.= '<div class="panel-footer">' . $icon . i18n(null, $footer['title']) . '</div>';
				} else {
					$result.= '<div class="panel-footer">' . i18n(null, $footer) . '</div>';
				}
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
		$options['class'] = array_add_token($options['class'] ?? [], 'table table-striped', ' ');
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
					$error_class = '';
					if (!empty($v3['error']['type'])) {
						if ($v3['error']['type'] == 'danger') {
							$v3['error']['type'] = 'error';
						}
						$error_class = 'has-' . $v3['error']['type'];
					}
					$result.= '<div class="col-sm-' . $field_new_sizes['data'][$index] . ' form-group ' . $error_class . '">';
						// label
						if ($flag_first_field) {
							if (($v3['label'] ?? '') . '' != '') {
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
						// error messages
						if (!empty($v3['error']['message'])) {
							$result.= $v3['error']['message'];
						}
					$result.= '</div>';
					$index++;
				}
			}
			$result.= '</div>';
		}
		return $result;
	}

	/**
	 * @see html::breadcrumbs()
	 */
	public static function breadcrumbs($options) {
		$result = '';
		$result.= '<ul class="breadcrumbs">';
		$options = array_values($options);
		$last = count($options) - 1;
		foreach ($options as $k => $v) {
			$result.= '<li' . ($k == $last ? ' class="last"' : '') . '>' . i18n(null, $v) . '</li>';
			if ($k != $last) {
				$result.= '<li> / </li>';
			}
		}
		$result.= '</ul>';
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
		// create name
		$name = i18n(null, $item['name']);
		if (!empty($item['icon'])) {
			$name = html::icon(['type' => $item['icon']]) . ' ' . $name;
		}
		$result = html::a(['href' => $item['url'] ?? 'javascript:void(0);', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'value' => $name . $caret]);
		$result.= '<ul class="dropdown-menu">';
			foreach ($item['options'] as $k2 => $v2) {
				$class = !empty($v2['options']) ? ' class="dropdown-submenu"' : '';
				$result.= '<li' . $class . '>';
					if (!empty($v2['options'])) {
						$result.= self::menu_submenu($v2, $level);
					} else {
						// create name
						$name = i18n(null, $v2['name']);
						if (!empty($v2['icon'])) {
							$name = html::icon(['type' => $v2['icon']]) . ' ' . $name;
						}
						if (!empty($v2['url'])) {
							$result.= html::a(['href' => $v2['url'], 'value' => $name]);
						} else {
							$result.= $name;
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
		$items_right = $options['options_right'] ?? [];
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
									// create name
									$name = i18n(null, $v['name']);
									if (!empty($v['icon'])) {
										$name = html::icon(['type' => $v['icon']]) . ' ' . $name;
									}
									if (!empty($v['url'])) {
										$result.= html::a(['href' => $v['url'], 'value' => $name]);
									} else {
										$result.= $name;
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
											// create name
											$name = i18n(null, $v['name']);
											if (!empty($v['icon'])) {
												$name = html::icon(['type' => $v['icon']]) . ' ' . $name;
											}
											if (!empty($v['url'])) {
												$result.= html::a(['href' => $v['url'], 'value' => $name]);
											} else {
												$result.= $name;
											}
										}
									$result.= '</li>';
								}
							$result.= '</ul>';
						$result.= '</li>';
					$result.= '</ul>';
					// right menu
					if (!empty($items_right)) {
						$result.= '<ul class="nav navbar-nav navbar-right">';
							foreach ($items_right as $k => $v) {
								$result.= '<li class="navbar-nav-li-level-right">';
									// create name
									$name = i18n(null, $v['name']);
									if (!empty($v['icon'])) {
										$name = html::icon(['type' => $v['icon']]) . ' ' . $name;
									}
									if (!empty($v['url'])) {
										$result.= html::a(['href' => $v['url'], 'value' => $name]);
									} else {
										$result.= $name;
									}
								$result.= '</li>';
							}
						$result.= '</ul>';
					}
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
		$options['class'] = ['alert', 'alert-' . $type];
		if (!is_array($value)) {
			$value = [$value];
		}
		$error_type_addon = '';
		/*
		if ($type == 'error') {
			$error_type_addon = '<b>There was some errors with your submission:</b></br/>';
		}
		*/
		return '<div role="alert" ' . self::generate_attributes($options) . '>' . $error_type_addon . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}

	/**
	 * @see html::modal()
	 */
	public static function modal($options = []) {
		$options['class'] = $options['class'] ?? '';
		if ($options['class'] == 'large') {
			$options['class'] = 'modal-lg';
		}
		$result = '';
		$result.= '<div class="modal fade" id="' . $options['id'] . '" tabindex="-1" role="dialog">';
			$result.= '<div class="modal-dialog ' . $options['class'] . '">';
				$result.= '<div class="modal-content">';
					$result.= '<div class="modal-header">';
						$result.= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
						$result.= '<h4 class="modal-title">' . ($options['title'] ?? '') . '</h4>';
					$result.= '</div>';
					$result.= '<div class="modal-body">';
						$result.= $options['body'] ?? '';
					$result.= '</div>';
					$result.= '<div class="modal-footer">';
						$result.= $options['footer'] ?? '';
						//<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						//<button type="button" class="btn btn-primary">Save changes</button>
					$result.= '</div>';
				$result.= '</div>';
			$result.= '</div>';
		$result.= '</div>';
		return $result;
	}

	/**
	 * @see html::text();
	 */
	public static function text($options = []) {
		$options['tag'] = $options['tag'] ?? 'p';
		$options['type'] = 'text-' . ($options['type'] ?? 'primary');
		$options['class'] = array_add_token($options['class'] ?? [], [$options['type']], ' ');
		return html::tag($options);
	}
}