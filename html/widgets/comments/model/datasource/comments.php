<?php

class numbers_frontend_html_widgets_comments_model_datasource_comments extends object_datasource {
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$model = Factory::model($options['model']);
		$db = $model->db_object();
		$where = null;
		if (!empty($options['where'])) {
			$where = 'AND ' . $db->prepare_condition($options['where']);
		}
		$columns = [];
		foreach ($model->columns as $k => $v) {
			$columns[] = 'a.' . $k . ' ' . str_replace($model->column_prefix, '', $k);
		}
		return "SELECT " . implode(', ', $columns) . ", b.em_entity_name FROM [table[{$options['model']}]] a LEFT JOIN [table[numbers_data_entities_entities_model_entities]] b ON a.{$model->column_prefix}who_entity_id = b.em_entity_id WHERE 1=1 {$where} ORDER BY {$model->column_prefix}inserted DESC";
	}
}