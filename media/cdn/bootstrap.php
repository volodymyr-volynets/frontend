<?php

class numbers_frontend_media_cdn_bootstrap implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_css('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
		layout::add_js('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
	}
}