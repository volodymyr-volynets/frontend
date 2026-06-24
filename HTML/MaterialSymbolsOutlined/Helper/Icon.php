<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\MaterialSymbolsOutlined\Helper;

use Numbers\Frontend\Media\CDN\MaterialSymbolsOutlined;

class Icon
{
    /**
     * Icon
     *
     * @param array $options
     * @return string
     */
    public static function icon(array $options): string
    {
        // add cdn
        MaterialSymbolsOutlined::add();
        // perform extraction
        $value = str_replace(
            [
                'material-symbols-outlined',
                'light',
                'regular',
                'bold',
                'fill',
                'animated',
                'dark',
                'inactive',
            ],
            '',
            $options['type']
        );
        $options['value'] = trim($value);
        $options['class'] ??= '';
        $options['class'] .= str_replace($options['value'], '', $options['type']);
        return \HTML::tag($options);
    }
}
