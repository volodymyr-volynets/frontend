<?php

namespace Numbers\Frontend\HTML\Renderers\Bootstrap;
class Base extends \Numbers\Frontend\HTML\Renderers\Common\Base implements \Numbers\Frontend\HTML\Renderers\Common\Interface2\Base {

	/**
	 * @see \HTML::segment()
	 */
	public static function segment(array $options = []) : string {
		$value = $options['value'] ?? '';
		$type = $options['type'] ?? '';
		$header = $options['header'] ?? null;
		$footer = $options['footer'] ?? null;
		$result = '<div class="card">';
			if ($header != null) {
				$bg_class = $type ? ('bg-' . $type . ' text-white') : '';
				if (is_array($header)) {
					$icon = !empty($header['icon']) ? (\HTML::icon($header['icon']) . ' ') : null;
					$result.= '<div class="card-header ' . $bg_class . '">' . $icon . $header['title'] . '</div>';
				} else {
					$result.= '<div class="card-header ' . $bg_class . '">' . $header . '</div>';
				}
			}
			$result.= '<div class="card-body">';
				$result.= '<p class="card-text">' . $value . '</p>';
				if (!empty($options['bottom'])) {
					$result.= '<p class="card-text"><small class="text-muted">' . $options['bottom'] . '</small></p>';
				}
			$result.= '</div>';
			if ($footer != null) {
				if (is_array($footer)) {
					$icon = !empty($footer['icon']) ? (\HTML::icon($footer['icon']) . ' ') : null;
					$result.= '<div class="card-footer">' . $icon . $footer['title'] . '</div>';
				} else {
					$result.= '<div class="card-footer">' . $footer . '</div>';
				}
			}
		$result.= '</div>';
		return $result;
	}

	/**
	 * @see \HTML::input()
	 */
	public static function input(array $options = []) : string {
		if (!in_array($options['type'] ?? 'text', ['button', 'submit']) && empty($options['skip_form_control'])) {
			$options['class'] = array_add_token($options['class'] ?? [], 'form-control', ' ');
		}
		return parent::input($options);
	}

	/**
	 * @see \HTML::textarea()
	 */
	public static function textarea(array $options = []) : string {
		$options['class'] = array_add_token($options['class'] ?? [], 'form-control', ' ');
		return parent::textarea($options);
	}

	/**
	 * @see \HTML::inputGroup()
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
					$temp2 = [];
					foreach ($options[$k0] as $k => $v) {
						$temp2[] = \HTML::span(['value' => $v, 'class' => 'input-group-text']);
					}
					$temp[] = \HTML::div(['value' => implode('', $temp2), 'class' => 'input-group-' . str_replace(['left', 'right'], ['prepend', 'append'], $k0)]);
				}
			}
		}
		unset($options['left'], $options['right']);
		$options['value'] = implode('', $temp);
		$options['class'] = 'input-group mb-3';
		return \HTML::div($options);
	}

	/**
	 * @see \HTML::select()
	 */
	public static function select(array $options = []) : string {
		$options['class'] = array_add_token($options['class'] ?? [], 'form-control', ' ');
		return parent::select($options);
	}

	/**
	 * @see \HTML::form()
	 */
	public static function form(array $options = []) : string {
		$options['role'] = 'form';
		return parent::form($options);
	}

	/**
	 * @see \HTML::button()
	 */
	public static function button(array $options = []) : string {
		$type = $options['type'] ?? 'secondary';
		if ($type == 'default') $type = 'secondary';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::button($options);
	}

	/**
	 * @see \HTML::button2()
	 */
	public static function button2(array $options = []) : string {
		$type = $options['type'] ?? 'secondary';
		if ($type == 'default') $type = 'secondary';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::button2($options);
	}

	/**
	 * @see \HTML::a()
	 */
	public static function a(array $options = []) : string {
		if (isset($options['type'])) {
			$type = $options['type'] ?? 'default';
			$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		}
		return parent::a($options);
	}

	/**
	 * @see \HTML::submit()
	 */
	public static function submit(array $options = []) : string {
		$type = $options['type'] ?? 'default';
		$options['class'] = array_add_token($options['class'] ?? [], 'btn btn-' . $type, ' ');
		return parent::submit($options);
	}

	/**
	 * @see \HTML::table()
	 */
	public static function table(array $options = []) : string {
		if (!isset($options['class'])) {
			$options['class'] = array_add_token($options['class'] ?? [], 'table table-striped', ' ');
		}
		//'<div class="table-responsive">' . parent::table($options) . '</div>';
		return parent::table($options);
	}

	/**
	 * @see \HTML::grid()
	 */
	public static function grid(array $options = []) : string {
		$rtl = \I18n::rtl();
		$grid_columns = \Application::get('flag.numbers.framework.html.options.grid_columns') ?? 12;
		$rows = isset($options['options']) ? $options['options'] : [];
		unset($options['options']);
		$options['cell_class'] = rtrim($options['cell_class'] ?? 'col-sm-', '-') . '-';
		$result = '';
		foreach ($rows as $k => $v) {
			// we need to determine field sizes
			$field_sizes = [];
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					$field_sizes[] = $v3['options']['percent'] ?? null;
				}
			}
			$field_new_sizes = \HTML::percentageToGridColumns($field_sizes);
			// count number of fields
			$count_fields = count($v);
			//$count_class = \HTML::number_to_word($count_fields);
			// find all row classes
			$row_class = '';
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if (!empty($v3['row_class'])) {
						$row_class.= ' ' . $v3['row_class'];
					}
				}
			}
			$result.= '<div class="row' . $row_class . '">';
			// we need to determine if we have label in the row
			$flag_have_label = false;
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if (($v3['label'] ?? '') . '' != '') {
						$flag_have_label = true;
					}
				}
			}
			// we need to fill up empty columns if rtl
			if ($rtl) {
				$index = 0;
				$current_grid_columns = 0;
				foreach ($v as $k2 => $v2) {
					foreach ($v2 as $k3 => $v3) {
						// if we are mannually set field sizes we skip
						if (!empty($v3['options']['field_size'])) {
							$current_grid_columns = 12;
							break;
						}
						$current_grid_columns+= $field_new_sizes['data'][$index];
						$v[$k2][$k3]['options']['field_size'] = $options['cell_class'] . $field_new_sizes['data'][$index]; // a must
						$index++;
					}
				}
				if ($current_grid_columns != $grid_columns) {
					$v['__empty_column_fill__']['__empty_column_fill__'] = [
						'value' => ' ',
						'options' => [
							'field_size' => $options['cell_class'] . ($grid_columns - $current_grid_columns) // a must
						]
					];
					$field_new_sizes['data'][$index] = $grid_columns - $current_grid_columns;
				}
				$v = array_reverse($v, true);
			}
			// loop though each field and render it
			$index = 0;
			foreach ($v as $k2 => $v2) {
				$flag_first_field = true;
				if ($rtl) {
					$v2 = array_reverse($v2, true);
				}
				foreach ($v2 as $k3 => $v3) {
					$error_class = '';
					if (!empty($v3['error']['type'])) {
						if ($v3['error']['type'] == 'danger') {
							$v3['error']['type'] = 'error';
						}
						$error_class = 'has-' . $v3['error']['type'];
					}
					// style
					$style = '';
					if (isset($v3['options']['style'])) {
						$style = ' style="' . $v3['options']['style'] . '"';
					}
					$field_size = $v3['options']['field_size'] ?? ($options['cell_class'] . $field_new_sizes['data'][$index]);
					$class = $v3['class'] ?? '';
					$result.= '<div class="' . $field_size . ' form-group ' . $error_class . ' ' . $class . '"' . $style . '>';
						// label
						if ($flag_first_field) {
							if (($v3['label'] ?? '') . '' != '') {
								// if label is not wrapped into label we autowrap
								if (strpos($v3['label'], '<label') === false) {
									$v3['label'] = \HTML::label(['value' => $v3['label']]);
								}
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
						// error messages
						if (!empty($v3['error']['message'])) {
							$result.= $v3['error']['message'];
						}
						// description after error message
						if (!empty($v3['description'])) {
							$result.= \HTML::text(['type' => 'muted', 'value' => $v3['description']]);
						}
					$result.= '</div>';
					$index++;
				}
			}
			$result.= '</div>';
		}
		$class = ['container-fluid'];
		if (!empty($options['class'])) $class[] = $options['class'];
		return '<div class="' . implode(' ', $class) . '">' . $result . '</div>';
	}

	/**
	 * @see \HTML::breadcrumbs()
	 */
	public static function breadcrumbs(array $options = []) : string {
		$result = '';
		$result.= '<ul class="breadcrumbs">';
		$options = array_values($options);
		$last = count($options) - 1;
		foreach ($options as $k => $v) {
			$result.= '<li' . ($k == $last ? ' class="last"' : '') . '>' . i18n(null, $v) . '</li>';
			if ($k != $last) {
				$result.= '<li> \ </li>';
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
	private static function menuSubmenu($item, $level) {
		$level++;
		$caret = $level == 1 ? ' <b class="caret"></b>' : '';
		$caret = '';
		// create name
		if (empty($item['i18n_done'])) {
			$name = i18n(null, $item['name']);
		} else {
			$name = $item['name'];
		}
		// name generator
		if (!empty($item['name_generator'])) {
			\Layout::onLoad('Numbers.Menu.name_generator[' . $item['menu_id'] . '] = "' . $item['name_generator'] . '";');
		}
		// add icon
		if (!empty($item['icon'])) {
			$name = \HTML::icon(['type' => $item['icon']]) . ' ' . $name;
		}
		//'data-toggle' => 'dropdown'
		$result = \HTML::a(['href' => $item['url'] ?? 'javascript:void(0);', 'class' => 'nav-link dropdown-toggle', 'id' => 'menu_item_id_' . $item['menu_id'], 'value' => $name, 'role' => 'button', 'data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'aria-expanded' => 'false']);
		$result.= '<ul class="dropdown-menu" aria-labelledby="menu_item_id_' . $item['menu_id'] . '">';
			// sort
			foreach ($item['options'] as $k2 => $v2) {
				$item['options'][$k2]['name'] = i18n(null, $v2['name']);
				$item['options'][$k2]['i18n_done'] = true;
			}
			// if we are sorting by order field
			if (!empty($item['child_ordered'])) {
				array_key_sort($item['options'], ['order' => SORT_ASC], ['name' => SORT_NUMERIC]);
			} else {
				array_key_sort($item['options'], ['name' => SORT_ASC], ['name' => SORT_NATURAL]);
			}
			// go though all options
			foreach ($item['options'] as $k2 => $v2) {
				$result.= '<li>';
					$class = !empty($v2['options']) ? 'nav-link dropdown-item' : 'nav-link';
					// separator
					if (!empty($v2['separator'])) {
						$result.= '<hr class="navbar-nav-hr-separator"/>';
					}
					// render options
					if (!empty($v2['options'])) {
						$result.= self::menuSubmenu($v2, $level);
					} else {
						// create name
						$name = $v2['name'];
						// name generator
						if (!empty($v2['name_generator'])) {
							\Layout::onLoad('Numbers.Menu.name_generator[' . $v2['menu_id'] . '] = "' . $v2['name_generator'] . '";');
						}
						// icon
						if (!empty($v2['icon'])) {
							$name = \HTML::icon(['type' => $v2['icon']]) . ' ' . $name;
						}
						if (!empty($v2['url'])) {
							$result.= \HTML::a(['href' => $v2['url'], 'class' => $class, 'id' => 'menu_item_id_' . $v2['menu_id'], 'title' => $v2['title'] ?? null, 'value' => $name]);
						} else {
							$result.= \HTML::div(['title' => $v2['title'] ?? null, 'value' => $name]);
						}
					}
				$result.= '</li>';
			}
		$result.= '</ul>';
		return $result;
	}

	/**
	 * @see \HTML::menu()
	 */
	public static function menu(array $options = []) : string {
		$items = $options['options'] ?? [];
		$items_right = $options['options_right'] ?? [];
		$brand_name = $options['brand_name'] ?? null;
		$brand_url = $options['brand_url'] ?? '/';
		$brand_logo = $options['brand_logo'] ?? '';
		array_key_unset($options, ['options', 'brand']);
		$result = '<div class="navbar navbar-expand-lg navbar-light bg-light" role="navigation">';
			$result.= '<div class="container">';
				$result.= '<div class="navbar-header">';
					$result.= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
						$result.= '<span class="navbar-toggler-icon"></span>';
					$result.= '</button>';
					if (!empty($brand_logo)) {
						$result.= '<a href="' . $brand_url . '"><img src="' . $brand_logo . '" /></a>';
					} else {
						$result.= '<a class="navbar-brand" href="' . $brand_url . '">' . $brand_name . '</a>';
					}
				$result.= '</div>';
				$result.= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
					$result.= '<ul class="navbar-nav mr-auto">';
						$index = 1;
						foreach ($items as $k => $v) {
							$class = !empty($v['options']) ? 'nav-item dropdown' : 'nav-item';
							$result.= '<li class="navbar-nav-li-level1 ' . $class . '" search-id="' . $index . '">';
								// if we have options
								if (!empty($v['options'])) {
									$result.= self::menuSubmenu($v, 0);
								} else {
									// create name
									$name = i18n(null, $v['name']);
									if (!empty($v['icon'])) {
										$name = \HTML::icon(['type' => $v['icon']]) . ' ' . $name;
									}
									if (!empty($v['url'])) {
										$result.= \HTML::a(['href' => $v['url'], 'class' => 'nav-link', 'value' => $name]);
									} else {
										$result.= $name;
									}
								}
							$result.= '</li>';
							$items[$k]['index'] = $index;
							$index++;
						}
						// and we need to add all tabs again into others tab
						/*
						$result.= '<li search-id="0" class="navbar-nav-others">';
							$result.= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">More <b class="caret"></b></a>';
							$result.= '<ul class="dropdown-menu navbar-nav-others-ul">'; //multi-level
								foreach ($items as $k => $v) {
									$class = !empty($v['options']) ? ' dropdown-submenu' : '';
									$result.= '<li class="navbar-nav-li-level1-others' . $class . '" search-id="' . $v['index'] . '">';
										// if we have options
										if (!empty($v['options'])) {
											$result.= self::menuSubmenu($v, 1);
										} else {
											// create name
											$name = i18n(null, $v['name']);
											if (!empty($v['icon'])) {
												$name = \HTML::icon(['type' => $v['icon']]) . ' ' . $name;
											}
											if (!empty($v['url'])) {
												$result.= \HTML::a(['href' => $v['url'], 'value' => $name]);
											} else {
												$result.= $name;
											}
										}
									$result.= '</li>';
								}
							$result.= '</ul>';
						$result.= '</li>';
						*/
					$result.= '</ul>';
					// right menu
					if (!empty($items_right)) {
						$result.= '<ul class="navbar-nav ml-auto">';
							foreach ($items_right as $k => $v) {
								$class = !empty($v['options']) ? 'nav-item dropdown' : 'nav-item';
								$result.= '<li class="' . $class . '">';
									// if we have options
									if (!empty($v['options'])) {
										$result.= self::menuSubmenu($v, 0);
									} else {
										// create name
										$name = i18n(null, $v['name']);
										// name generator
										if (!empty($v['name_generator'])) {
											\Layout::onLoad('Numbers.Menu.name_generator[' . $v['menu_id'] . '] = "' . $v['name_generator'] . '";');
										}
										if (!empty($v['icon'])) {
											$name = \HTML::icon(['type' => $v['icon']]) . ' ' . $name;
										}
										if (!empty($v['url'])) {
											$result.= \HTML::a(['href' => $v['url'], 'class' => 'nav-link', 'id' => 'menu_item_id_' . $v['menu_id'], 'value' => $name]);
										} else {
											$result.= $name;
										}
									}
								$result.= '</li>';
							}
						$result.= '</ul>';
					}
				$result.= '</div>';
			$result.= '</div>';
		$result.= '</div>';
		// we must update menu items
		\Layout::onload('Numbers.Menu.updateItems();');
		return $result;
	}

	/**
	 * @see \HTML::message()
	 */
	public static function message(array $options = []) : string {
		$value = isset($options['options']) ? $options['options'] : [];
		$type = isset($options['type']) ? $options['type'] : 'other';
		unset($options['options'], $options['type']);
		$options['class'] = ['alert', 'alert-' . $type];
		if (!is_array($value)) {
			$value = [$value];
		}
		return '<div role="alert" ' . self::generateAttributes($options) . '>' . self::ul(['options' => $value, 'type' => 'ul']) . '</div>';
	}

	/**
	 * @see \HTML::modal()
	 */
	public static function modal(array $options = []) : string {
		$options['class'] = $options['class'] ?? '';
		if ($options['class'] == 'large' || empty($options['class'])) {
			$options['class'] = 'modal-lg';
		}
		$closeable = '';
		if (!empty($options['close_by_click_disabled'])) {
			$closeable = ' data-backdrop="static" data-keyboard="false"';
		}
		// assembling
		$result = '';
		$result.= '<div class="modal fade" id="' . $options['id'] . '" tabindex="-1" role="dialog"' . $closeable . '>';
			$result.= '<div class="modal-dialog ' . $options['class'] . '">';
				$result.= '<div class="modal-content">';
					$result.= '<div class="modal-header">';
						$result.= '<h4 class="modal-title">' . ($options['title'] ?? '') . '</h4>';
						if (empty($options['no_header_close'])) {
							$result.= '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>';
						}
					$result.= '</div>';
					$result.= '<div class="modal-body">';
						$result.= $options['body'] ?? '';
					$result.= '</div>';
					if (!empty($options['footer'])) {
						$result.= '<div class="modal-footer">';
							$result.= $options['footer'];
						$result.= '</div>';
					}
				$result.= '</div>';
			$result.= '</div>';
		$result.= '</div>';
		return $result;
	}

	/**
	 * @see \HTML::text();
	 */
	public static function text(array $options = []) : string {
		$options['tag'] = $options['tag'] ?? 'p';
		$options['type'] = 'text-' . ($options['type'] ?? 'primary');
		$options['class'] = array_add_token($options['class'] ?? [], [$options['type']], ' ');
		return \HTML::tag($options);
	}

	/**
	 * @see \HTML::label2()
	 */
	public static function label2(array $options = []) : string {
		$options['tag'] = $options['tag'] ?? 'span';
		$options['type'] = 'badge badge-' . ($options['type'] ?? 'primary');
		$options['class'] = array_add_token($options['class'] ?? [], [$options['type'], 'label'], ' ');
		return \HTML::tag($options);
	}

	/**
	 * @see \HTML::tabs();
	 */
	public static function tabs(array $options = []) : string {
		$vertical = $options['vertical'] ?? false;
		$header = $options['header'] ?? [];
		$values = $options['options'] ?? [];
		$id = $options['id'] ?? 'tabs_default';
		// determine active tab
		$active_id = $id . '_active_hidden';
		$active_tab = $options['active_tab'] ?? \Request::input($active_id);
		if (!empty($options['tab_options'][$active_tab]['hidden'])) {
			$active_tab = null;
		}
		if (empty($active_tab)) {
			foreach ($header as $k => $v) {
				if (!empty($options['tab_options'][$k]['hidden'])) continue;
				$active_tab = $k;
				break;
			}
		} else if (empty($header[$active_tab])) { // if active tab is not present
			$active_tab = key($header);
		}
		$result = '';
		$result.= '<div id="' . $id . '" class="' . ($options['class'] ?? '') . '">';
			$result.= \HTML::hidden(['name' => $active_id, 'id' => $active_id, 'value' => $active_tab]);
			$tabs = [];
			$panels = [];
			$class = $li_class = $id . '_tab_li';
			foreach ($header as $k => $v) {
				$li_id = $id . '_tab_li_' . $k;
				$content_id = $id . '_tab_content_' . $k;
				$class2 = $class . ' nav-link numbers_prevent_selection';
				if ($k == $active_tab) {
					$class2.= ' active';
				}
				if (!empty($options['tab_options'][$k]['hidden'])) {
					$class2.= ' hidden';
				}
				$tabindex = '';
				if (!empty($options['tab_options'][$k]['tabindex'])) {
					$tabindex = ' tabindex="' . $options['tab_options'][$k]['tabindex'] . '" ';
				}
				$tabs[$k] = '<li id="' . $li_id . '" class="nav-item numbers_prevent_selection"' . $tabindex . ' role="presentation"><a href="#' . $content_id . '" class="' . $class2 . '" tab-data-id="' . $k . '" aria-controls="' . $content_id .'" role="tab" data-toggle="tab">' . $v . '</a></li>';
				$panels[$k] = '<div role="tabpanel" class="tab-pane ' . ($k == $active_tab ? 'active' : '') . ' ' . $k . '" id="' . $content_id . '">' . $values[$k] . '</div>';
			}
			// regular tab
			if (!$vertical) {
				$result.= '<ul class="nav nav-tabs" role="tablist" id="' . $id . '_links' . '">';
					$result.= implode('', $tabs);
				$result.= '</ul>';
				$result.= '<div class="tab-content">';
					$result.= implode('', $panels);
				$result.= '</div>';
			} else { // vertical tab
				$result.= '<div class="row">';
					$result.= '<div class="col-md-3">';
						$result.= '<ul class="nav nav-pills nav-stacked" role="tablist" id="' . $id . '_links' . '">';
							$result.= implode('', $tabs);
						$result.= '</ul>';
					$result.= '</div>';
					$result.= '<div class="col-md-9">';
						$result.= '<div class="tab-content tab-vertical-content">';
							$result.= implode('', $panels);
						$result.= '</div>';
					$result.= '</div>';
				$result.= '</div>';
			}
		$result.= '</div>';
		$js = <<<TTT
			$('#{$id}_links a').click(function(e) {
				e.preventDefault();
				$(this).tab('show');
				$('#{$active_id}').val($(this).attr('tab-data-id'));
			});
			$('.{$li_class}').mousedown(function(e) {
				var that = $(this);
				if (!that.is(':focus')) {
					that.data('mousedown', true);
				}
			});
			$('.{$li_class}').focus(function(e) {
				e.preventDefault();
				var mousedown = $(this).data('mousedown'), tabindex = parseInt($(this).attr('tabindex'));
				$(this).removeData('mousedown');
				$(this).find('a:first').click();
				if (!mousedown && tabindex > 0) {
					$("[tabindex='" + (tabindex + 1) + "']").focus();
				} else if (mousedown) {
					$(this).blur();
				}
				e.preventDefault();
			});
TTT;
		\Layout::onload($js);
		return $result;
	}

	/**
	 * @see \HTML::pills();
	 */
	public static function pills(array $options = []) : string {
		Throw new Exception('Pills?');
	}

	/**
	 * @see \HTML::popover
	 */
	public static function popover(array $options = []) : string {
		$options['title'] = $options['title'] ?? '';
		// if we do not have tags we wrap it
		if (strpos($options['value'], '<a') === false) {
			$options['value'] = '<a href="javascript:void(0);" id="' . $options['id'] . '" title="' . $options['title'] . '">' . $options['value'] . '</a>';
		}
		$js = <<<TTT
			$('#{$options['id']}').popover({
				trigger : 'click',
				placement : 'top',
				html: true,
				content: function () {
					return '{$options['content']}';
				},
				template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
			});
TTT;
		\Layout::onLoad($js);
		return $options['value'];
	}
}