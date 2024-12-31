<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\Components\Captcha\GD;

class Base implements \Numbers\Frontend\Components\Captcha\Interface2\Base
{
    /**
     * Draw
     *
     * @param string $word
     * @param array $options
     */
    public function draw($word, $options = [])
    {
        $width = !empty($options['width']) ? $options['width'] : 150;
        $height = !empty($options['height']) ? $options['height'] : 50;
        $word = $word . '';
        // create new image
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 255, 255, 255));
        // draw random lines
        for ($i = 0; $i < 10; $i++) {
            $color = imagecolorallocate($image, rand() % 255, rand() % 255, rand() % 255);
            imageline($image, 0, rand() % $height, $width, rand() % $height, $color);
        }
        // draw random pixels
        for ($i = 0; $i < 500; $i++) {
            $color = imagecolorallocate($image, rand() % 255, rand() % 255, rand() % 255);
            imagesetpixel($image, rand() % $width, rand() % $height, $color);
        }
        // drawing text
        $len = strlen($word);
        $x_start = 5;
        $x_len = ($width - $x_start * 2) / $len;
        $y_start = 5;
        $y_end = $height - 5;
        for ($i = 0; $i < $len; $i++) {
            $color = imagecolorallocate($image, rand() % 100, rand() % 100, rand() % 100);
            $font_size = rand(16, 24);
            $x = $x_start + ($x_len * $i);
            $y = rand($y_start + $font_size, $y_end);
            $angle = rand(-30, 30);
            imagettftext($image, $font_size, $angle, $x, $y, $color, __DIR__ . '/Fonts/arial.ttf', $word[$i]);
        }
        // returning image
        if (!empty($options['return_image'])) {
            ob_start();
            imagepng($image);
            imagedestroy($image);
            return ob_get_clean();
        } else {
            // output image, important to set content type in application
            Application::set('flag.global.__content_type', 'image/png');
            header("Content-type: image/png");
            imagepng($image);
            imagedestroy($image);
            exit;
        }
    }

    /**
     * Generate random string
     *
     * @param string $captcha_link
     * @param string $letters
     * @param int $length
     * @return string
     */
    public static function generatePassword($captcha_link, $letters = null, $length = 5)
    {
        $letters = isset($letters) ? $letters : '123456789';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $letters[mt_rand(0, strlen($letters) - 1)];
        }
        // setting value in session
        \Session::set(str_replace('\\', '.', get_called_class()) . '.' . $captcha_link . '.password', $result);
        return $result;
    }

    /**
     * Captcha
     *
     * @param array $options
     * @return string
     */
    public static function captcha(array $options = []): string
    {
        $captcha_link = $options['id'] ?? 'default';
        // generating password
        $password = self::generatePassword($captcha_link, $options['password_letters'] ?? null, $options['password_length'] ?? 5);
        array_key_unset($options, ['password_letters', 'password_length']);
        $image_options = [
            'src' => 'data:image/png;base64,' . base64_encode(self::draw($password, ['return_image' => true])),
            'style' => $options['img_style'] ?? 'vertical-align: middle;',
        ];
        if (!empty($options['only_image'])) {
            return \HTML::img($image_options);
        } else {
            return '<table width="100%"><tr><td>' . \HTML::input($options) . '</td><td width="1%">&nbsp;</td><td width="1%">' . \HTML::img($image_options) . '</td></tr></table>';
        }
    }

    /**
     * @see \Object\Validator\Base::validate()
     */
    public function validate(string $value, array $options = []): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'placeholder' => ''
        ];
        $password = \Session::get("Numbers.Frontend.Components.Captcha.GD.Base.{$options['options']['id']}.password") . '';
        $result['placeholder'] = 'Enter text here';
        if (empty($value) || $value !== $password) {
            $result['error'][] = 'Invalid captcha!';
        } else {
            $result['success'] = true;
            $result['data'] = $value;
        }
        return $result;
    }
}
