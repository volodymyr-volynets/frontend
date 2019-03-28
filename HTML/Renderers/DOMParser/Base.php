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
		self::$data[$link]['dom'] = new \DOMDocument();
		self::$data[$link]['dom']->loadHTML($html);
		self::$data[$link]['parser'] = new \Parser(self::$data[$link]['dom']);
		self::$data[$link]['parser']->loadRulesFromDom();
	}

	/**
	 * Get style
	 *
	 * @param string $link
	 * @param mixed $element
	 * @return string
	 */
	public static function getStyle(string $link, $element) : string {
		return self::$data[$link]['parser']->getStylesFromCssRules($element);
	}
}