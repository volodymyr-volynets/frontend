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

class FontAwesome implements Interface2
{
    /**
     * Add media to layout
     */
    public static function add()
    {
        \Layout::addCss('https://use.fontawesome.com/releases/v5.6.3/css/all.css');
    }
}
