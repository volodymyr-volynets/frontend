<?php

class numbers_frontend_media_cdn_jquery implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_js('https://code.jquery.com/jquery-2.2.0.min.js', -50000);
	}
}