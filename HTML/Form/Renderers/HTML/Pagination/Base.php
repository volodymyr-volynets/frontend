<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\Form\Renderers\HTML\Pagination;

class Base
{
    /**
     * Render
     *
     * @param array $options
     * @return string
     */
    public function render(& $options)
    {
        $type = $options['pagination_type'] ?? 'top';
        $form_submit = $options['form_submit'] ?? '__submit_button';
        // fetched
        if (!empty($options['total'])) {
            $fetched = i18n(null, 'Fetched [num_rows] of [total]', [
                'replace' => [
                    '[num_rows]' => i18n(null, $options['num_rows']),
                    '[total]' => i18n(null, $options['total'])
                ]
            ]);
        } else {
            $fetched = i18n(null, 'Fetched [num_rows]', [
                'replace' => [
                    '[num_rows]' => i18n(null, $options['num_rows'])
                ]
            ]);
        }
        // sorting
        $sort = '';
        if (!empty($options['sort'])) {
            $sort .= '<table style="min-height: 40px;"><tr><td valign="middle">';
            $sort .= i18n(null, 'Sort') . ': ';
            $temp = [];
            foreach ($options['sort'] as $k => $v) {
                $temp[] = i18n(null, $k) . ' ' . \HTML::icon(['type' => 'fas fa-sort-alpha-' . ($v == SORT_ASC ? 'up' : 'down')]);
            }
            $sort .= implode(', ', $temp);
            $sort .= '</td></tr></table>';
        }
        // sorting select
        if (!empty($options['sort_options'])) {
            $sort .= '<table width="100%" style="min-height: 40px;"><tr><td valign="middle">' . i18n(null, 'Sort') . ': ' .'</td><td valign="middle">';
            $sort .= \HTML::select(['id' => $options['form_link'] . '_sort_select_' . $type, 'options' => $options['sort_options'], 'value' => $options['sort_value'], 'no_choose' => true, 'onchange' => "Numbers.Form.setValue(this.form, '__sort', this.value); Numbers.Form.triggerSubmit(this.form, '{$form_submit}');"]);
            $sort .= '</td></tr></table>';
        }
        // displaying
        $displaying = '<table>';
        $displaying .= '<tr>';
        // preview button
        if (!empty($options['preview'])) {
            if ($options['preview'] == 1) {
                if (empty($options['preview_as_line'])) {
                    goto previewLabel;
                }
                $preview_icon = 'fas fa-grip-lines';
                $preview_value = 2;
                $preview_title = i18n(null, 'Line');
            } elseif ($options['preview'] == 2) {
                previewLabel:
                                        $preview_icon = 'fas fa-list';
                $preview_value = 0;
                $preview_title = i18n(null, 'List');
            }
        } else {
            $preview_icon = 'fas fa-th-list';
            $preview_value = 1;
            $preview_title = i18n(null, 'Preview');
        }
        $displaying .= '<td>' . \HTML::button2(['type' => 'default', 'title' => $preview_title, 'value' => \HTML::icon(['type' => $preview_icon]), 'onclick' => "Numbers.Form.setValue(this.form, '__preview', {$preview_value}); Numbers.Form.triggerSubmit(this.form, '{$form_submit}'); return false;"]) . '</td>';
        $displaying .= '<td>&nbsp;</td>';
        $displaying .= '<td><div style="width: 80px;">' . \HTML::select(['id' => $options['form_link'] . '_page_sizes_' . $type, 'title' => i18n(null, 'Displaying rows'), 'options' => \Factory::model('\Numbers\Framework\Object\Form\Model\PageSizes', true)->options(['i18n' => 'skip_sorting']), 'value' => $options['limit'], 'no_choose' => true, 'onchange' => "Numbers.Form.setValue(this.form, '__offset', 0); Numbers.Form.setValue(this.form, '__limit', this.value); Numbers.Form.triggerSubmit(this.form, '{$form_submit}');"]) . '</div></td>';
        $displaying .= '</tr>';
        $displaying .= '</table>';
        // navigation
        $navigation = [];
        $flag_next_row_exists = false;
        $flag_last_row_exists = false;
        $current_page = intval($options['offset'] / $options['limit']);
        if ($current_page >= 1) {
            $navigation[] = \HTML::button2(['value' => i18n(null, 'First'), 'onclick' => "Numbers.Form.setValue(this.form, '__offset', 0); Numbers.Form.triggerSubmit(this.form, '{$form_submit}'); return false;"]);
        }
        if ($current_page >= 2) {
            $previous = (($current_page - 1) * $options['limit']);
            $navigation[] = \HTML::button2(['value' => i18n(null, 'Previous'), 'onclick' => "Numbers.Form.setValue(this.form, '__offset', {$previous}); Numbers.Form.triggerSubmit(this.form, '{$form_submit}'); return false;"]);
        }
        // select with number of pages
        $pages = ceil($options['total'] / $options['limit']);
        if ($options['num_rows']) {
            $temp = [];
            for ($i = 0; $i < $pages; $i++) {
                $temp[($i * $options['limit'])] = ['name' => i18n(null, $i + 1)];
            }
            $navigation2 = i18n(null, 'Page') . ': ';
            $previous = (($current_page - 1) * $options['limit']);
            $navigation2 .= '<div style="width: 100px; display: inline-block; vertical-align: middle;">' . \HTML::select(['id' => $options['form_link'] . '_pages_' . $type, 'options' => $temp, 'value' => $options['offset'], 'no_choose' => true, 'onchange' => "Numbers.Form.setValue(this.form, '__offset', this.value); Numbers.Form.triggerSubmit(this.form, '{$form_submit}');"]) . '</div>';
            $navigation[] = $navigation2;
            // checking for next and last pages
            $flag_next_row_exists = ($pages - $current_page - 2 > 0) ? true : false;
            $flag_last_row_exists = ($pages - $current_page - 1 > 0) ? true : false;
        } else {
            $navigation[] = i18n(null, 'Page') . ': ' . ($current_page + 1);
        }
        if ($flag_next_row_exists) {
            $next = (($current_page + 1) * $options['limit']);
            $navigation[] = \HTML::button2(['value' => i18n(null, 'Next'), 'onclick' => "Numbers.Form.setValue(this.form, '__offset', {$next}); Numbers.Form.triggerSubmit(this.form, '{$form_submit}'); return false;"]);
        }
        if ($flag_last_row_exists) {
            $last = (($pages - 1) * $options['limit']);
            $navigation[] = \HTML::button2(['value' => i18n(null, 'Last'), 'onclick' => "Numbers.Form.setValue(this.form, '__offset', {$last}); Numbers.Form.triggerSubmit(this.form, '{$form_submit}'); return false;"]);
        }
        // generating grid
        $grid = [
            'options' => [
                0 => [
                    'Displaying' => [
                        'Displaying' => [
                            'value' => $displaying,
                            'options' => [
                                'field_size' => 'col-xs-6 col-sm-6 col-lg-2',
                                'percent' => 15,
                                'style' => 'height: 40px; line-height: 40px;',
                            ]
                        ]
                    ],
                    'Fetched' => [
                        'Fetched' => [
                            'value' => $fetched,
                            'options' => [
                                'field_size' => 'col-xs-6 col-sm-6 col-lg-2',
                                'percent' => 15,
                                'style' => 'height: 40px; line-height: 40px;',
                            ]
                        ]
                    ],
                    'Sort' => [
                        'Sort' => [
                            'class' => 'numbers_frontend_form_list_sort',
                            'value' => $sort,
                            'options' => [
                                'field_size' => 'col-xs-12 col-sm-12 col-lg-3',
                                'percent' => 15,
                                'style' => 'min-height: 40px;'
                            ]
                        ]
                    ],
                    'Navigation' => [
                        'Navigation' => [
                            'class' => 'numbers_frontend_form_list_pagination_navigation',
                            'value' => implode(' ', $navigation),
                            'options' => [
                                'field_size' => 'col-xs-12 col-sm-12 col-lg-5',
                                'percent' => 50,
                                'style' => 'height: 40px; line-height: 40px;',
                            ]
                        ]
                    ],
                ]
            ]
        ];
        return '<div class="numbers_frontend_form_list_pagination_container">' . \HTML::grid($grid) . '</div>';
    }
}
