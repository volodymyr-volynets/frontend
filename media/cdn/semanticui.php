<?php

class numbers_frontend_media_cdn_semanticui implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		layout::add_css('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.css');
		layout::add_js('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.js');
	}
}