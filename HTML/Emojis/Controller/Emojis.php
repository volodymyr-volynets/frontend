<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\Emojis\Controller;

use Object\Controller;

class Emojis extends Controller
{
    public $title = 'Emojis';

    public function actionIndex()
    {
        $data = \Numbers\Frontend\HTML\Emojis\Model\Emojis::getStatic();
        foreach ($data as $k => $v) {
            echo \Numbers\Frontend\HTML\Emojis\Model\Emojis::u2entity($k) . ' ' . $v['name'] . ' - ' . $v['category'] . '</i>';
            echo '<hr/>';
        }
    }

    public function actionGetNewEmojis()
    {
        // categories
        $categories = [];
        $dom = new \DOMDocument();
        $dom->loadHTML(file_get_contents('https://apps.timwhitlock.info/emoji/tables/unicode'));

        //<h3 id="block-2-dingbats" class="category">
        foreach ($dom->getElementsByTagName('h3') as $node) {
            $temp = explode("\n", trim($node->textContent))[1] ?? '';
            $categories[] = trim(explode('.', $temp ?? '')[1] ?? '');
        }

        // tables
        $tables = \DataFrame2::readHTMLFile('https://apps.timwhitlock.info/emoji/tables/unicode', -1);

        $result = [];
        foreach ($tables as $k => $v) {
            $temp = $v->toArray2()->toArray();
            foreach ($temp as $k2 => $v2) {
                if ($v2[8] == 'Description') {
                    continue;
                }
                $v2[6] = trim($v2[6]);
                $v2[8] = trim($v2[8]);
                $v2_items = explode(' ', $v2[6]);
                $v2_descriptions = explode(' + ', $v2[8]);
                foreach ($v2_items as $k3 => $v3) {
                    //$key = str_replace('U+', '&#x', $v3) . ';';
                    $result[$v3] = [
                        'name' => $v2_descriptions[$k3],
                        'category' => $categories[$k],
                        'emoji' => trim($v2[0]),
                    ];
                }
            }
        }

        $result2 = var_export_condensed($result, ['format_first_level' => true]);
        print_r2($result2);
    }
}
