<?php

class numbers_frontend_html_list_model_datasource_count extends object_datasource {
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
		return <<<TTT
			SELECT
				COUNT(*) count
			FROM [table[{$options['model']}]] a
			WHERE 1=1
				{$where}
TTT;
	}
}