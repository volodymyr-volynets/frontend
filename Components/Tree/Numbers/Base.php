<?php

namespace Numbers\Frontend\Components\Tree\Numbers;
class Base implements \Numbers\Frontend\Components\Tree\Interface2\Base {

	/**
	 * see \HTML::tree();
	 */
	public static function tree(array $options = []) : string {
		// include js & css files
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Tree_Numbers_Media_JS_Base.js', -10001);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Tree_Numbers_Media_CSS_Base.css', -10001);
		$options['id'] = ($options['id'] ?? 'numbers_tree_id_' . rand(1000, 9999));
		$options['class'] = ($options['class'] ?? '') . ' numbers_tree_holder';
		// process items
		$items = $options['options'] ?? [];
		unset($options['options']);
		// generate data array
		$result = $temp = [];
		$options['name_field'] = 'name';
		$options['prepend_parent_keys'] = true;
		\Helper\Tree::convertTreeToOptionsMulti($items, 0, $options, $temp);
		$data_max_level = 0;
		$counter = 0;
		foreach ($temp as $k => $v) {
			if ($v['level'] > $data_max_level) {
				$data_max_level = $v['level'];
			}
			$v['__key'] = $k;
			$result[$counter] = $v;
			$counter++;
		}
		$icon_key = $options['icon_key'] ?? 'icon_class';
		// render
		$hash = $hash2 = [];
		$items_count = count($result);
		$html = '<table id="' . $options['id'] .  '_tree_table" class="numbers_tree_option_table" width="100%" cellpadding="0" cellspacing="0" border="0">';
			$i = 0;
			foreach ($result as $i => $v) {
				// inactive
				$inactive_class = '';
				if (!empty($v['inactive'])) {
					$inactive_class = ' numbers_inactive ';
				}
				// selected
				$selected_class = '';
				if (!empty($v['selected'])) {
					$selected_class = ' numbers_tree_row_selected numbers_selected ';
				}
				// title
				$title = $v['title'] ?? '';
				// if disabled
				if (!empty($v['disabled'])) {
					$html.= '<tr class="numbers_tree_option_table_tr ' . $inactive_class . ' numbers_disabled" search-id="' . $i . '" title="' . $title . '">';
				} else {
					$html.= '<tr class="numbers_tree_option_table_tr' . $selected_class . $inactive_class . ' numbers_tree_option_table_tr_hover" search-id="' . $i . '" title="' . $title . '">';
				}
					if ($v['level'] == 0) {
						$hash2 = [];
					}
					if ($v['level'] > 0) {
						for ($j = 0; $j < $v['level']; $j++) {
							// reset hash
							foreach ($hash2 as $hk => $hv) {
								if ($hk >= $v['level']) {
									$hash2[$hk] = 0;
								}
							}
							$status = '';
							if ($j < $v['level']) {
								for ($k = $i + 1; $k < $items_count; $k++) {
									if ($result[$k]['level'] == $j) {
										$status = 'next';
										break;
									}
								}
							}
							if ($status == 'next' && !empty($hash2[$j])) {
								$status = 'blank';
							}
							if ($status == 'next' && $j == $v['level'] - 1) {
								$status = 'nextchild';
							}
							if ($status == 'nextchild' && $i + 1 < $items_count) {
								if ($result[$i + 1]['level'] < $v['level']) {
									if ($j == 0) {
										$hash2[$j] = 1;
									}
									$status = 'last';
								} else {
									for ($k = $i + 1; $k < $items_count; $k++) {
										if ($result[$k]['level'] == $v['level']) {
											break;
										}
										if ($result[$k]['level'] < $v['level']) {
											$hash2[$j] = 1;
											$status = 'last';
											break;
										}
									}
								}
							}
							if ($status == 'next') {
								for ($k = $i + 1; $k < $items_count; $k++) {
									if ($result[$k]['level'] >= $j) {
										continue;
									} else {
										$status = 'next';
										break;
									}
								}
							}
							if (!$status) {
								if ($j < $v['level']) {
									for ($k = $i + 1; $k < $items_count; $k++) {
										if ($result[$k]['level'] == $j + 1) {
											$status = 'next';
											break;
										}
									}
								}
								if (!$status) {
									if (empty($hash[$j])) {
										$hash[$j] = 1;
										$status = 'last';
									} else {
										$status = 'blank';
									}
								}
								if ($status == 'next' && $j == $v['level'] - 1) {
									$status = 'nextchild';
								}
								if ($status == 'nextchild' && $i + 1 < $items_count) {
									if ($result[$i + 1]['level'] < $v['level']) {
										$status = 'last';
									}
								}
								if ($status == 'next') {
									for ($k = $i + 1; $k < $items_count; $k++) {
										if ($result[$k]['level'] >= $j) {
											continue;
										} else {
											$status = 'blank';
											break;
										}
									}
								}
							}
							switch ($status) {
								case 'next':
									$html.= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_nextchild" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table></td>';
									break;
								case 'last':
									$html.= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_last" cellpadding="0" cellspacing="0"><tr><td class="numbers_tree_option_table_level_last_left">&nbsp;</td></tr><tr><td class="numbers_tree_option_table_level_last_sep">&nbsp;</td></tr></table></td>';
									break;
								case 'nextchild':
									$html.= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_nextchild" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td></tr><tr><td class="numbers_tree_option_table_level_nextchild_sep">&nbsp;</td></tr></table></td>';
									break;
								case 'blank':
									$html.= '<td class="numbers_tree_option_table_level"></td>';
									break;
								default:
									$html.= '<td class="numbers_tree_option_table_level">1</td>';
							}
						}
					}
					$colspan = $data_max_level - $v['level'] + 1;
					$html.= '<td colspan="' . $colspan . '" valign="middle" class="numbers_tree_option_table_td">';
						// if we have toolbar
						if (!empty($v['toolbar'])) {
							$icon = '';
							if (!empty($result[$i][$icon_key])) {
								$icon = '<i class="numbers_tree_option_table_icon ' . $result[$i][$icon_key] . '"></i> ';
							}
							$html.= '<table width="100%" border="0">';
								$html.= '<tr><td>&nbsp;</td></tr>';
								$html.= '<tr><td>' . $icon . $v['name'] . '</td></tr>';
								$html.= '<tr><td class="numbers_tree_mini_toolbar">' . implode(' | ', $v['toolbar']) . '</td></tr>';
							$html.= '</table>';
						} else {
							$name = '';
							if (!empty($result[$i][$icon_key])) {
								$name.= '<i class="numbers_tree_option_table_icon ' . $result[$i][$icon_key] . '"></i> ';
							}
							if (!empty($result[$i]['photo_id'])) {
								$name.= '<img class="navbar-menu-item-avatar-img" src="' . $result[$i]['photo_id'] . '" width="24" height="24" /> ';
							}
							$name.= $v['name'];
							// if we have url
							if (!empty($result[$i]['url'])) {
								if (!empty($result[$i]['__menu_id'])) {
									$temp_url = http_append_to_url($result[$i]['url'], ['__menu_id' => $result[$i]['__menu_id'] ?? '']);
								} else {
									$temp_url = $result[$i]['url'];
								}
								$name = \HTML::a(['href' => $temp_url, 'value' => $name]);
							}
							$html.= $name;
						}
					$html.= '</td>';
					//$html.= '<td width="1%">&nbsp;</td>';
				$html.= '</tr>';
			}
			// no rows found
			if ($items_count == 0) {
				$html.= '<tr>';
					$html.= '<td class="numbers_tree_option_table_tr" colspan="' . $data_max_level . '">';
						$html.= \HTML::message(['type' => 'warning', 'options' => [i18n(null, \Object\Content\Messages::NO_ROWS_FOUND)]]);
					$html.= '</td>';
				$html.= '</tr>';
			} else {
				\Layout::onLoad('numbers_tree_update_lines(\'' . $options['id'] .  '_tree_table\');');
			}
		$html.= '</table>';
		$options['value'] = $html;
		return \HTML::div($options);
	}
}