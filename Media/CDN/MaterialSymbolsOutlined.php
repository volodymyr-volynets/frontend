<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\Media\CDN;

class MaterialSymbolsOutlined implements Interface2
{
    /**
     * @var bool
     */
    public static bool $already_loaded = false;

    /**
     * Add media to layout
     */
    public static function add()
    {
        if (!self::$already_loaded) {
            self::$already_loaded = true;
            \Layout::addCss('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&', 3000);
            \Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_MaterialSymbolsOutlined_Media_CSS_Base.css', 3010);
        }
    }
}
