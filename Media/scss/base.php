<?php

/**
 * library to work with scss files
 */
class numbers_frontend_media_scss_base {

	/**
	 * Process scss file
	 *
	 * @param string $filename
	 * @return array
	 */
	public static function serve($filename) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => ''
		];
		// try to get scss file
		$scss_string = file_get_contents($filename);
		if ($scss_string === false) {
			$result['error'][] = 'Scss file does not exists!';
		} else {
			$scss_compiler = new scssc();
			$scss_compiler->setFormatter('scss_formatter');
			$result['data'] = $scss_compiler->compile($scss_string);
			$result['success'] = true;
		}
		return $result;
	}
}