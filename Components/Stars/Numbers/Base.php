<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\Components\Stars\Numbers;

class Base implements \Numbers\Frontend\Components\Stars\Interface2\Base
{
    /**
     * Initialize
     *
     * @param array $options
     * @return void
     */
    public static function initialize(array $options = []): void
    {
        // include js & css files
        \Layout::addJs('/numbers/media_submodules/Numbers_Frontend_Components_Stars_Numbers_Media_JS_Base.js', -10000);
        \Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Stars_Numbers_Media_CSS_Base.css', -10000);
    }

    /**
     * see \HTML::select();
     */
    public static function stars(array $options = []): string
    {
        self::initialize($options);
        $number_of_stars = $options['number_of_stars'] ?? 5;
        $options['value'] ??= 0;
        // id with name
        if (empty($options['id']) && !empty($options['name'])) {
            $options['id'] = $options['name'];
        }
        $result = '<table><tr>';
        for ($i = 1; $i <= $number_of_stars; $i++) {
            if ($i <= $options['value']) {
                $result .= '<td>' . \HTML::icon(['type' => 'fa-solid fa-star', 'id' => $options['id'] . '_star_' . $i, 'data-star' => $i, 'class' => 'numbers_stars_item numbers_stars_selected', 'onclick' => "numbers_start_item_click('" . $options['id'] . "', " . $i . "," . $number_of_stars . ");"]) . '</td>';
            } else {
                $result .= '<td>' . \HTML::icon(['type' => 'fa-solid fa-star', 'id' => $options['id'] . '_star_' . $i, 'data-star' => $i, 'class' => 'numbers_stars_item numbers_stars_active', 'onclick' => "numbers_start_item_click('" . $options['id'] . "', " . $i . "," . $number_of_stars . ");"]) . '</td>';
            }
        }
        $result .= '</tr></table>';
        return $result . \HTML::hidden($options);
    }
}
