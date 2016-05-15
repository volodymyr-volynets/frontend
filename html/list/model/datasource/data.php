<?php

class numbers_frontend_html_list_model_datasource_data extends object_datasource {
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$options['orderby'] = !empty($options['orderby']) ? ('ORDER BY ' . array_key_sort_prepare_keys($options['orderby'], true)) : '';
		return <<<TTT
			SELECT
				*
			FROM [table[{$options['model']}]] a
			WHERE 1=1
			{$options['orderby']}
			OFFSET {$options['offset']}
			LIMIT {$options['limit']}
TTT;
	}
}