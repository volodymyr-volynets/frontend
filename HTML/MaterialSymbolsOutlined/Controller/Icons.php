<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\MaterialSymbolsOutlined\Controller;

use Object\Controller;
use Numbers\Frontend\Media\CDN\MaterialSymbolsOutlined;

class Icons extends Controller
{
    public $title = 'Icons';

    public function actionIndex()
    {
        MaterialSymbolsOutlined::add();
        $data = \Numbers\Frontend\HTML\MaterialSymbolsOutlined\Model\Icons::getStatic();
        foreach ($data as $k => $v) {
            $k = '&#x' . $v['hash'];
            echo '<i class="material-symbols-outlined light">' . $k . '</i>';
            echo '<i class="material-symbols-outlined regular">' . $k . '</i>';
            echo '<i class="material-symbols-outlined bold">' . $k . '</i>';
            echo '<i class="material-symbols-outlined fill">' . $k . '</i>';
            echo '<i class="material-symbols-outlined animated">' . $k . '</i>';
            echo '<i class="material-symbols-outlined dark">' . $k . '</i>';
            echo '<i class="material-symbols-outlined inactive">' . $k . '</i>';
            echo $v['name'] . ' (' . $v['hash'] . ')';
            echo ' material-symbols-outlined [light|regular|bold|fill|animated|dark|inactive] ' . $v['hash'];
            echo '<hr/>';
        }
    }
}
