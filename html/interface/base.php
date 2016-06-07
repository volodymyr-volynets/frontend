<?php

interface numbers_frontend_html_interface_base {
	// basic elements
	public static function a($options = []);
	public static function img($options = []);
	public static function script($options = []);
	public static function style($options = []);
	public static function table($options = []);
	public static function grid($options);
	public static function fieldset($options = []);
	public static function ul($options = []);
	public static function mandatory($options = []);
	public static function tooltip($options = []);
	public static function message($options = []);
	public static function element($options = []);
	// assemblies
	public static function segment($options = []);
	public static function menu($options = []);
	// simple tags
	public static function tag($options = []);
	public static function div($options = []);
	public static function span($options = []);
	public static function label($options = []);
	// form related elements
	public static function form($options = []);
	public static function input($options = []);
	public static function input_group($options = []);
	public static function radio($options = []);
	public static function checkbox($options = []);
	public static function password($options = []);
	public static function file($options = []);
	public static function hidden($options = []);
	public static function textarea($options = []);
	public static function select($options = []);
	public static function multiselect($options = []);
	public static function icon($options = []);
	// form assemblies
	public static function calendar($options = []);
	//public static function captcha($options = []);
	//public static function captcha_validate($captcha_id, $password);
	// form buttons
	public static function button($options = []);
	public static function button2($options = []);
	public static function submit($options = []);
	// special handling function for options
	public static function render_value_from_options($value, $options);
}