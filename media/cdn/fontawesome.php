<?php

class numbers_frontend_media_cdn_fontawesome implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_css('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
	}
}