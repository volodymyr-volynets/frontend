<?php

class numbers_frontend_html_list_model_datasource_count extends object_datasource {
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		return <<<TTT
			SELECT
				COUNT(*) count
			FROM [table[{$options['model']}]] a
			WHERE 1=1
TTT;
	}
}