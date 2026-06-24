<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\Renderers\Common\Helper;

class Colors
{
    /**
     * Get colors and initials
     *
     * @param string $name
     * @param string $type - user | org | role | team
     * @return string
     */
    public static function getColorsAndInitials(string $name, string $type = 'user', bool $is_small = false): string
    {
        $initials = new \String2($name)->upperInitials()->toString();
        if ($type == 'circle') {
            return '#ffffff' . ',' . '#000000' . ',' . $initials . ',' . $type . ',' . ($is_small ? 1 : 0);
        }
        $color = self::colorFromString($name);
        return $color . ',' . self::determineTextColor($color) . ',' . $initials . ',' . $type . ',' . ($is_small ? 1 : 0);
    }

    /**
     * Color from string
     *
     * @param string $string
     * @return string
     */
    public static function colorFromString(string $string): string
    {
        return '#' . substr(md5($string), 0, 6);
    }

    /**
     * Determine text color
     *
     * @param string $color
     * @return string
     */
    public static function determineTextColor(string $color): string
    {
        $color = hex2rgb($color);
        $luma = ($color[0] + $color[1] + $color[2]) / 3;
        if ($luma < 128) {
            return '#FFFFFF';
        } else {
            return '#000000';
        }
    }

    /**
     * Render avatar
     *
     * @param string $name
     * @return string
     */
    public static function renderAvatar(string $name, string $type = 'user', bool $is_small = false, string $title = ''): string
    {
        $color = explode(',', self::getColorsAndInitials($name, $type, $is_small));
        $class = 'nf_render_avatar_';
        if (in_array($type, ['organization', 'role', 'team', 'realm', 'domain', 'circle'])) {
            $class .= 'ort';
        } else {
            $class .= 'holder';
        }
        $class .= '_' . ($is_small ? 'small' : 'regular');
        if ($type == 'circle') {
            $class .= ' nf_render_avatar_border';
        }
        return <<<TTT
            <span class="{$class}" title="{$title}" style="background-color: {$color[0]}; color: {$color[1]};" title="{$name}">
                {$color[2]}
            </span>
TTT;
    }

    /**
     * Render photo
     *
     * @param string $name
     * @param string $type
     * @param string $url
     * @return string
     */
    public static function renderPhoto(string $name, string $url, string $type = 'square'): string
    {
        $class = 'nf_render_photo_holder_' . $type;
        if (!empty(getenv('NF_IS_CONTAINER'))) {
            $url = str_replace(['http://localhost/', 'https://localhost/'], \Request::host(), $url);
        }
        return \HTML::img(['class' => $class, 'src' => $url, 'alt' => $name, 'height' => 50, 'width' => 50]);
    }
}
