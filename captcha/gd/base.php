<?php

class numbers_frontend_captcha_gd_base extends numbers_frontend_captcha_class_base implements numbers_frontend_captcha_interface_base {

	/**
	 * Draw captcha
	 *
	 * @param string $word
	 * @param array $options
	 */
	public function draw($word, $options = []) {
		$width = !empty($options['width']) ? $options['width'] : 150;
		$height = !empty($options['height']) ? $options['height'] : 50;
		$word = $word . '';
		// create new image
		$image = imagecreatetruecolor($width, $height);
		imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 255, 255, 255));
		// draw random lines
		for($i = 0; $i < 10; $i++) {
			$color = imagecolorallocate($image, rand() % 255, rand() % 255, rand() % 255);
			imageline($image, 0, rand() % $height, $width, rand() % $height, $color);
		}
		// draw random pixels
		for($i = 0; $i < 500; $i++) {
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
			imagettftext($image, $font_size, $angle, $x, $y, $color, __DIR__ . '/fonts/arial.ttf', $word[$i]);
		}
		// output image
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
		exit;
	}

	/**
	 * Captcha image tag
	 *
	 * @param string $id
	 * @return string
	 */
	public function captcha($options = []) {
		$options['id'] = isset($options['id']) ? ($options['id'] . '') : 'default';
		$options['style'] = isset($options['style']) ? $options['style'] : 'vertical-align: middle;';
		$crypt = new crypt();
		$token = $crypt->token_create('captcha', $options['id']);
		$options['src'] = '/numbers/frontend/captcha/gd/controller/captcha.png?token=' . $token;
		$options['id'].= '_gd_base';
		return html::img($options);
	}
}