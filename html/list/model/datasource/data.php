<?php

class numbers_frontend_html_list_model_datasource_data extends object_datasource {
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$where = null;
		if (!empty($options['where'])) {
			$model = factory::model($options['model']);
			$db = $model->db_object();
			$where = 'AND ' . $db->prepare_condition($options['where']);
		}
		if (!empty($options['orderby']['full_text_search']) && !empty($options['where']['full_text_search,fts'])) {
			$temp = [];
			foreach ($options['orderby'] as $k => $v) {
				if ($k != 'full_text_search') {
					$temp[$k] = $v;
				} else {
					$model = factory::model($options['model']);
					$db = $model->db_object();
					$temp2 = $db->full_text_search_query($options['where']['full_text_search,fts']['fields'], $options['where']['full_text_search,fts']['str']);
					$temp[$temp2['orderby']] = $v;
				}
			}
			$options['orderby'] = $temp;
		} else {
			unset($options['orderby']['full_text_search']);
		}
		$options['orderby'] = !empty($options['orderby']) ? ('ORDER BY ' . array_key_sort_prepare_keys($options['orderby'], true)) : '';
		return <<<TTT
			SELECT
				*
			FROM [table[{$options['model']}]] a
			WHERE 1=1
				{$where}
			{$options['orderby']}
			LIMIT {$options['limit']}
			OFFSET {$options['offset']}
TTT;
	}
}