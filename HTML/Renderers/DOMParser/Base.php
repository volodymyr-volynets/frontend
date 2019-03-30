<?php

namespace Numbers\Frontend\HTML\Renderers\DOMParser;
class Base {

	/**
	 * Data
	 *
	 * @var array
	 */
	public static $data = [];

	/**
	 * load
	 *
	 * @param string $link
	 * @param string $html
	 */
	public static function load(string $link, string $html) {
		// load css
		if (preg_match_all('/"([^"]+?\.css)"/', $html, $matches)) {
			$css = '';
			foreach ($matches[1] as $v) {
				if (strpos($v, '/numbers/') === 0 && \Application::get('environment') == 'development') {
					$css.= \System\Media::serveMediaIfExists($v, \Application::get(['application', 'path']), true);
				} else if ($v[0] == '/') {
					$css.= file_get_contents(\Application::get(['application', 'path_full']) . '../public_html' . $v);
				} else {
					$css.= file_get_contents($v);
				}
			}
			self::$data[$link]['css'] = self::parseCSS($css, true);
		}
	}

	/**
	 * Get style
	 *
	 * @param string $link
	 * @param string $tag
	 * @param string $id
	 * @param string $class
	 * @return string
	 */
	public static function getStyle(string $link, string $tag, string $id, string $class) : string {
		$result = [];
		foreach (self::$data[$link]['css'] ?? [] as $k => $v) {
			if (!empty($v[$tag])) {
				$result = array_merge_hard($result, $v[$tag]);
			}
			if (!empty($class)) {
				$classes = explode(' ', $class);
				foreach ($classes as $v2) {
					if (!empty($v['.' . $v2])) {
						$result = array_merge_hard($result, $v['.' . $v2]);
					}
				}
			}
		}
		$temp = [];
		foreach ($result as $k => $v) {
			if ($link == 'email') {
				$found = false;
				if ($k == 'color') $found = true;
				if (strpos($k, 'background') !== false) $found = true;
				if (strpos($k, 'border') !== false) $found = true;
				if (strpos($k, 'padding') !== false) $found = true;
				if (strpos($k, 'display') !== false && strpos($v, 'none') !== false) $found = true;
				if ($found) {
					$temp[] = $k . ': ' . $v . ';';
				}
			} else {
				$temp[] = $k . ': ' . $v . ';';
			}
		}
		return implode(' ', $temp);
	}

	/**
	 * Parse CSS
	 *
	 * @param string $css
	 * @return array
	 */
	private static function parseMedia(string $css) : array {
		$result = [];
		$start = 0;
		while (($start = strpos($css, "@media", $start)) !== false) {
			$stack = [];
			$i = strpos($css, "{", $start);
			if ($i !== false) {
				array_push($stack, $css[$i]);
				$i++;
				while (!empty($stack)){
					if ($css[$i] == "{") {
						array_push($stack, "{");
					} elseif ($css[$i] == "}") {
						array_pop($stack);
					}
					$i++;
				}
				$result[] = substr($css, $start, ($i + 1) - $start);
				$start = $i;
			}
		}
		return $result;
	}

	/**
	 * Parse CSS
	 *
	 * @param string $css
	 * @param bool $parse_media
	 * @return array
	 */
	public static function parseCSS(string $css, bool $parse_media = true) : array {
		$result = $medias = [];
		if ($parse_media) {
			$medias = self::parseMedia($css);
		}
		$index = 0;
		if (!empty($medias)) {
			$temp = $css;
			foreach ($medias as $v) {
				$temp = str_ireplace($v, '~£&#' . $v . '~£&#', $temp);
			}
			$temp = explode('~£&#', $temp);
			foreach ($temp as $v){
				preg_match('/(\@media[^\{]+)\{(.*)\}\s+/ims', $v, $matches);
				if (isset($matches[2])&&!empty($matches[2])) {
					$result[$matches[1]] = self::parseCSS($matches[2], false);
				} else {
					$result[$index] = self::parseCSS($v, false);
				}
				++$index;
			}
		} else {
			$css = preg_replace('/(data\:[^;]+);/i','$1~£&#', $css);
			preg_match_all('/([^\{\}(\*\/)]+)\{([^\}]*)\}/ims', $css, $matches);
			foreach ($matches[0] as $k => $v) {
				$selectors = explode(',', trim($matches[1][$k]));
				$rules = explode(';', trim($matches[2][$k]));
				$temp = [];
				foreach ($rules as $v2) {
					if(!empty($v2)) {
						$rule = explode(":", $v2, 2);
						if (isset($rule[1])) {
							$temp[trim($rule[0])] = str_replace('~£&#', ';', trim($rule[1]));
						}
					}
				}
				foreach ($selectors as $v2){
					if ($parse_media) {
						$result[$index][$v2] = $temp;
					} else {
						$result[$v2] = $temp;
					}
				}
			}
		}
		return $result;
	}
}