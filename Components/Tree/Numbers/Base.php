<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\Components\Tree\Numbers;

use Helper\Tree;
use Object\Content\Messages;

class Base implements \Numbers\Frontend\Components\Tree\Interface2\Base
{
    /**
     * see \HTML::tree();
     */
    public static function tree(array $options = []): string
    {
        // include js & css files
        \Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Tree_Numbers_Media_JS_Base.js', -10001);
        \Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Tree_Numbers_Media_CSS_Base.css', -10001);
        $options['id'] = ($options['id'] ?? 'numbers_tree_id_' . rand(1000, 9999));
        $table_class = $options['class'] ?? '';
        $options['class'] = ($options['holder_class'] ?? '') . ' numbers_tree_holder';
        // process items
        $items = $options['options'] ?? [];
        unset($options['options']);
        // generate data array
        $result = $temp = [];
        $options['name_field'] = 'name';
        $options['prepend_parent_keys'] = true;
        Tree::convertTreeToOptionsMulti($items, 0, $options, $temp);
        $data_max_level = 0;
        $data_max_header = 0;
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
        $html = '<table id="' . $options['id'] .  '_tree_table" class="numbers_tree_option_table ' . $table_class . '" width="100%" cellpadding="0" cellspacing="0" border="0">';
        // header
        if (!empty($options['header'])) {
            $temp_header = $options['header'];
            unset($temp_header['name']);
            $html .= '<tr class="numbers_tree_option_table_header">';
            if (!empty($options['numerate'])) {
                $html .= '<th class="numbers_tree_sticky_numerate">&nbsp;</th>';
                $data_max_header++;
            }
            foreach ($options['header'] as $k => $v) {
                $width = '';
                if (isset($v['width'])) {
                    $width = 'width="' . $v['width'] . '"';
                }
                if ($k === 'name') {
                    $colspan = 1; // ($data_max_level + 1)
                    $html .= '<th class="numbers_tree_sticky_column" colspan="' . $colspan . '" ' . $width .  ' nowrap>' . i18n(null, $v['name']) . '</th>';
                    $data_max_header += $data_max_level + 1;
                } else {
                    $html .= '<th ' . $width .  ' nowrap>' . i18n(null, $v['name']) . '</th>';
                    $data_max_header++;
                }
            }
            $html .= '</tr>';
        }
        // other headers
        if (!empty($options['zero_header'])) {
            if (!is_array($options['zero_header'])) {
                $options['zero_header'] = [$options['zero_header']];
            }
            foreach ($options['zero_header'] as $v_header) {
                $html .= '<tr class="numbers_tree_option_table_header">' . $v_header . '</tr>';
            }
        }
        // render rows
        $i = 0;
        $index = 1;
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
                $html .= '<tr class="numbers_tree_option_table_tr ' . $inactive_class . ' numbers_disabled" search-id="' . $i . '" title="' . $title . '">';
            } else {
                $html .= '<tr class="numbers_tree_option_table_tr' . $selected_class . $inactive_class . ' numbers_tree_option_table_tr_hover" search-id="' . $i . '" title="' . $title . '">';
            }
            // numerate
            if (!empty($options['numerate'])) {
                $html .= '<td class="numbers_tree_numerate_td numbers_tree_sticky_numerate" width="1%">' . $index . '.</td>';
            }
            if ($v['level'] == 0) {
                $hash2 = [];
            }
            $inner_html = '';
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
                            $inner_html .= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_nextchild" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr></table></td>';
                            break;
                        case 'last':
                            $inner_html .= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_last" cellpadding="0" cellspacing="0"><tr><td class="numbers_tree_option_table_level_last_left">&nbsp;</td></tr><tr><td class="numbers_tree_option_table_level_last_sep">&nbsp;</td></tr></table></td>';
                            break;
                        case 'nextchild':
                            $inner_html .= '<td class="numbers_tree_option_table_level"><table class="numbers_tree_option_table_level_nextchild" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td></tr><tr><td class="numbers_tree_option_table_level_nextchild_sep">&nbsp;</td></tr></table></td>';
                            break;
                        case 'blank':
                            $inner_html .= '<td class="numbers_tree_option_table_level"></td>';
                            break;
                        default:
                            $inner_html .= '<td class="numbers_tree_option_table_level">&nbsp;</td>';
                    }
                }
            }
            $colspan = $data_max_level - $v['level'] + 1;
            $colspan = 1;
            $html .= '<td colspan="' . $colspan . '" valign="middle" class="numbers_tree_option_table_td numbers_tree_sticky_column_name" nowrap>';
            // if we have toolbar
            if (!empty($v['toolbar'])) {
                $icon = '';
                if (!empty($result[$i][$icon_key])) {
                    $icon = '<i class="numbers_tree_option_table_icon ' . $result[$i][$icon_key] . '"></i> ';
                }
                $temp_html = '<table width="100%" border="0">';
                $temp_html .= '<tr><td>&nbsp;</td></tr>';
                $temp_html .= '<tr><td>' . $icon . $v['name'] . '</td></tr>';
                $temp_html .= '<tr><td class="numbers_tree_mini_toolbar">' . implode(' | ', $v['toolbar']) . '</td></tr>';
                if (!empty($v['description'])) {
                    $temp_html .= '<tr><td>' . $v['description'] . '</td></tr>';
                }
                $temp_html .= '</table>';
                $html .= '<table class="numbers_tree_option_table_name_column_groupped"><tr>' . $inner_html . '<td>' . $temp_html . '</td></tr></table>';
            } else {
                $name = '';
                if (!empty($result[$i][$icon_key])) {
                    $name .= '<i class="numbers_tree_option_table_icon ' . $result[$i][$icon_key] . '"></i> ';
                }
                if (!empty($result[$i]['photo_id'])) {
                    $name .= '<img class="navbar-menu-item-avatar-img" src="' . $result[$i]['photo_id'] . '" width="24" height="24" /> ';
                }
                $name .= $v['name'];
                // if we have url
                if (!empty($result[$i]['url'])) {
                    if (!empty($result[$i]['__menu_id'])) {
                        $temp_url = http_append_to_url($result[$i]['url'], ['__menu_id' => $result[$i]['__menu_id'] ?? '']);
                    } else {
                        $temp_url = $result[$i]['url'];
                    }
                    $name = \HTML::a(['href' => \Request::fixUrl($temp_url, $result[$i]['template']), 'value' => $name]);
                }
                $html .= '<table class="numbers_tree_option_table_name_column_groupped"><tr>' . $inner_html . '<td>' . $name . '</td></tr></table>';
            }
            $html .= '</td>';
            //$html.= '<td width="1%">&nbsp;</td>';
            if (!empty($temp_header)) {
                foreach ($temp_header as $k2 => $v2) {
                    $html .= '<td align="' . ($v2['align'] ?? 'left') . '" class="' . ($v2['class'] ?? '') . '" nowrap>';
                    $html .= $result[$i]['columns'][$k2] ?? '&nbsp;';
                    $html .= '</td>';
                }
            }
            $html .= '</tr>';
            $index++;
        }
        // no rows found
        if ($items_count == 0) {
            $html .= '<tr>';
            $html .= '<td class="numbers_tree_option_table_tr" colspan="' . ($data_max_header) . '">';
            $html .= \HTML::message(['type' => 'warning', 'options' => [i18n(null, Messages::NO_ROWS_FOUND)]]);
            $html .= '</td>';
            $html .= '</tr>';
        } else {
            \Layout::onLoad('numbers_tree_update_lines(\'' . $options['id'] .  '_tree_table\');');
        }
        $html .= '</table>';
        $options['value'] = $html;
        return \HTML::div($options);
    }
}
