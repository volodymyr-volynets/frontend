<?php

namespace Numbers\Frontend\Media\CDN;
class SemanticUI implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addCss('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.css');
		\Layout::addJs('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.js');
	}
}