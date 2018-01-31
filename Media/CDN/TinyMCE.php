<?php

namespace Numbers\Frontend\Media\CDN;
class TinyMCE implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('//cdn.tinymce.com/4/tinymce.min.js', 5000);
	}
}