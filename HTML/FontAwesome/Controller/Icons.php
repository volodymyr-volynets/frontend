<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\FontAwesome\Controller;

use Object\Controller;

class Icons extends Controller
{
    public $title = 'Icons';

    public function actionIndex()
    {
        $data = \Numbers\Frontend\HTML\FontAwesome\Model\Icons::getStatic();
        foreach ($data as $k => $v) {
            echo '<i class="' . $k . '"> ' . $v['name'] . '</i>';
            echo '<hr/>';
        }
    }
}
