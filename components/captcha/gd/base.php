<?php

class numbers_frontend_components_captcha_gd_base extends numbers_frontend_components_captcha_class_base implements numbers_frontend_components_captcha_interface_base {

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
		// returning image
		if (!empty($options['return_image'])) {
			ob_start();
			imagepng($image);
			imagedestroy($image);
			return ob_get_clean();
		} else {
			// output image, important to set content type in application
			application::set('flag.global.__content_type', 'image/png');
			header("Content-type: image/png");
			imagepng($image);
			imagedestroy($image);
			exit;
		}
	}

	/**
	 * Captcha
	 *
	 * @param array $options
	 * @return string
	 */
	public static function captcha($options = []) {
		$captcha_link = $options['id'] ?? 'default';
		// validation
		if (!empty($options['validate'])) {
			return self::validate($captcha_link, $options['password']);
		}
		// generating password
		$password = self::generate_password($captcha_link, $options['password_letters'] ?? null, $options['password_length'] ?? 5);
		array_key_unset($options, ['password_letters', 'password_length']);
		$image_options = [
			'src' => 'data:image/png;base64,' . base64_encode(self::draw($password, ['return_image' => true])),
			'style' => $options['img_style'] ?? 'vertical-align: middle;',
		];
		if (!empty($options['only_image'])) {
			return html::img($image_options);
		} else {
			return '<table width="100%"><tr><td>' . html::input($options) . '</td><td width="1%">&nbsp;</td><td width="1%">' . html::img($image_options) . '</td></tr></table>';
		}
	}
}