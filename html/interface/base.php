<?php

interface numbers_frontend_html_interface_base {
	public static function a($options = []);
	public static function img($options = []);
	public static function script($options = []);
	public static function style($options = []);
	public static function table($options = []);
	public static function fieldset($options = []);
	public static function ul($options = []);
	public static function mandatory();
	public static function tooltip($options = []);
	public static function message($options = []);
	public static function frame($options = []);
	public static function element($options = []);
	// form related elements
	public static function form($options = []);
	public static function input($options = []);
	public static function radio($options = []);
	public static function checkbox($options = []);
	public static function password($options = []);
	public static function file($options = []);
	public static function hidden($options = []);
	public static function textarea($options = []);
	public static function select($options = []);
	public static function multiselect($options = []);
	// form buttons
	public static function button($options = []);
	public static function button2($options = []);
	public static function submit($options = []);
}