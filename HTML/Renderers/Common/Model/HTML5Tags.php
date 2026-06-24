<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\Renderers\Common\Model;

use Object\Data;
use Numbers\Frontend\HTML\Renderers\Common\HTML5;

class HTML5Tags extends Data
{
    public $column_key = 'code';
    public $column_prefix = '';
    public $columns = [
        'code' => ['name' => 'Code', 'type' => 'text'],
        'name' => ['name' => 'Name', 'type' => 'text'],
    ];
    public $options_map = [
        'name' => 'name',
        'code' => 'icon_class'
    ];
    public $orderby = [
        'name' => SORT_ASC
    ];
    public const selectOptions = '\Numbers\Frontend\HTML\FontAwesome\Model\Icons::options';
    public $options_skip_i18n = true;
    public $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $temp = array_keys(HTML5::$tag_specific);
        sort($temp);
        foreach ($temp as $v) {
            $this->data[$v] = ['name' => $v];
        }
    }
}
