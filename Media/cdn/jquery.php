<?php

namespace Numbers\Frontend\Media\CDN;
class jQuery implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('https://code.jquery.com/jquery-2.2.0.min.js', -50000);
	}
}