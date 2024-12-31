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

class Bootstrap implements Interface2
{
    /**
     * Add media to layout
     */
    public static function add()
    {
        \Layout::addCss('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', -32000);
        \Layout::addJs('https://code.jquery.com/jquery-3.3.1.min.js', -31900);
        \Layout::addJs('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', -31800);
        \Layout::addJs('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', -31700);
        \Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_CSS_Base.css', -31600);
        \Layout::addJs('/numbers/media_submodules/Numbers_Frontend_HTML_Renderers_Bootstrap_Media_JS_Base.js', -31500);
    }
}
