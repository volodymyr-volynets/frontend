<?php

namespace Numbers\Frontend\Media\CDN;
class FontAwesome implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addCss('https://use.fontawesome.com/releases/v5.6.3/css/all.css');
	}
}