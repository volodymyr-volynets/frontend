<?php

class numbers_frontend_media_cdn_semanticui implements numbers_frontend_media_cdn_interface {

	/**
	 * Add media to layout
	 */
	public static function add() {
		Layout::add_css('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.css');
		Layout::add_js('//oss.maxcdn.com/semantic-ui/2.1.8/semantic.min.js');
	}
}