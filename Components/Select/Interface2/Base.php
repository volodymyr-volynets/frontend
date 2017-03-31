<?php

namespace Numbers\Frontend\Components\Select\Interface2;
interface Base {
	public static function select(array $options = []) : string;
	public static function multiselect(array $options = []) : string;
}