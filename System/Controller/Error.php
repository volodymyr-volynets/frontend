<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\System\Controller;

use Object\Controller;
use Object\Error\Base;

class Error extends Controller
{
    public $title = 'Js Error Handler';

    /**
     * This would process error message sent from frontend
     */
    public function actionIndex()
    {
        $input = \Request::input();
        if (!empty($input['token']) && ($input['token'] != 'null')) {
            $crypt = new \Crypt();
            $token_data = $crypt->tokenValidate($input['token'], ['skip_time_validation' => true]);
            if (!($token_data === false || $token_data['id'] !== 'general')) {
                $input['data'] = json_decode($input['data'], true);
                Base::errorHandler('javascript', $input['data']['message'], $input['data']['file'], $input['data']['line']);
            }
        }
        // rendering
        \Layout::renderAs(file_get_contents(__DIR__ . '/error.png'), 'image/png');
    }

    /**
     * Error action
     */
    public function actionError()
    {
        $result = '';
        // show human readable messages first
        if (count(Base::$errors) > 0) {
            $messages = [];
            foreach (Base::$errors as $k => $v) {
                if ($v['errno'] == -1) {
                    foreach ($v['error'] as $k2 => $v2) {
                        $messages[] = i18n(null, $v2);
                    }
                }
            }
            if (empty($messages)) {
                $messages[] = i18n(null, 'Internal Server Error: 500');
            }
            $result .= \HTML::message(['type' => 'danger', 'options' => $messages]);
        }
        // show full description second
        if (\Application::get('flag.error.show_full') && count(Base::$errors) > 0) {
            foreach (Base::$errors as $k => $v) {
                $result .= '<h3>' . Base::$error_codes[$v['errno']] . ' (' . $v['errno'] . ') - ' . implode('<br/>', $v['error']) . '</h3>';
                $result .= '<br />';
                $result .= '<div>File: ' . $v['file'] . ', Line: ' . $v['line'] . '</div>';
                $result .= '<br />';
                // showing code only when we debug
                if (\Debug::$debug) {
                    $result .= '<div><pre>' . $v['code'] . '</pre></div>';
                    $result .= '<br />';
                    $result .= '<div><pre>' . implode("\n", $v['backtrace']) . '</pre></div>';
                }
                $result .= '<hr />';
            }
        }
        echo $result;
    }
}
