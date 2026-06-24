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
use Numbers\Frontend\HTML\FontAwesome\Model\Icons7;

class Icons extends Controller
{
    public $title = 'Icons';

    public function actionIndex()
    {
        $data = Icons7::getStatic();
        $result = '<table>';
        $result .= '<tr><th>Icon</th><th>Name</th><th>Label</th><th>Styles</th></tr>';
        foreach ($data as $k => $v) {
            $result .= '<tr><td><i class="' . $k . ' fa-lg"></i></td><td>' . $v['name'] . '</td><td>' . $v['label'] . '</td><td>' . $v['styles'] . '</td></tr>';
        }
        $result .= '</table>';
        echo $result;
    }
}
