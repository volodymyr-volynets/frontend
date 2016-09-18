<?php

class numbers_frontend_media_cdn_tinymce implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_js('//cdn.tinymce.com/4/tinymce.min.js', 5000);
	}
}