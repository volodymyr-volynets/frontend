<?php

namespace Numbers\Frontend\Media\CDN;
class jQuery implements \Numbers\Frontend\Media\CDN\Interface2 {

	/**
	 * Add media to layout
	 */
	public static function add() {
		\Layout::addJs('http://code.jquery.com/jquery-3.3.1.min.js', -31900);
	}
}