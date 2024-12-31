<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\Components\Calendar\Numbers;

class Base implements \Numbers\Frontend\Components\Calendar\Interface2\Base
{
    /**
     * see \HTML::calendar()
     */
    public static function calendar(array $options = []): string
    {
        // include js & css files
        if (empty($options['readonly'])) {
            \Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Calendar_Numbers_Media_JS_Base.js');
            \Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Calendar_Numbers_Media_CSS_Base.css');
        }
        // font awesome icons
        \Library::add('FontAwesome');
        // widget parameters
        $type = $options['calendar_type'] ?? $options['type'] ?? 'date';
        $widget_options = [
            'id' => $options['id'],
            'type' => $type,
            'format' => $options['calendar_format'] ?? \Format::getDateFormat($type),
            'date_week_start_day' => $options['calendar_date_week_start_day'] ?? 1,
            'date_disable_week_days' => $options['calendar_date_disable_week_days'] ?? null,
            'master_id' => $options['calendar_master_id'] ?? null,
            'slave_id' => $options['calendar_slave_id'] ?? null,
            'show_presets' => $options['show_presets'] ?? true
        ];
        $options['type'] = 'text';
        // determine input size
        $placeholder = \Format::getDatePlaceholder($widget_options['format']);
        $options['size'] = strlen($placeholder);
        // set placeholder
        if (!empty($options['placeholder']) && strpos($options['placeholder'], 'Format::getDatePlaceholder') !== false) {
            $options['placeholder'] = $placeholder;
            $options['title'] = ($options['title'] ?? '') . ' (' . $placeholder . ')';
        }
        if (isset($options['calendar_icon']) && ($options['calendar_icon'] == 'left' || $options['calendar_icon'] == 'right')) {
            $position = \HTML::align($options['calendar_icon']);
            $icon_type = $type == 'time' ? 'far fa-clock' : 'fas fa-calendar-alt';
            unset($options['calendar_icon']);
            if (empty($options['readonly'])) {
                $icon_onclick = 'numbers_calendar_var_' . $options['id'] . '.show();';
            } else {
                $icon_onclick = null;
            }
            $icon_value = \HTML::span(['onclick' => $icon_onclick, 'class' => 'numbers_calendar_icon numbers_prevent_selection', 'value' => \HTML::icon(['type' => $icon_type])]);
            $result = \HTML::inputGroup(['value' => \HTML::input($options), $position => $icon_value, 'dir' => 'ltr']);
            $div_id = $options['id'] . '_div_holder';
            $result .= \HTML::div(['id' => $div_id, 'class' => 'numbers_calendar_div_holder']);
            $widget_options['holder_div_id'] = $div_id;
        } else {
            $result = \HTML::input($options);
        }
        // we do not render a widget if readonly
        if (empty($options['readonly'])) {
            \Layout::onload('Numbers_Calendar(' . json_encode($widget_options) . ');');
        }
        return $result;
    }
}
