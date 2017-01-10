<?php

class numbers_frontend_html_form_model_datasource_navigation extends object_datasource {
	public $db_link;
	public $db_link_flag;
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$model = factory::model($options['model'], true);
		$this->db_object = $model->db_object;
		$column = $options['where']['column_name'];
		// adjust type based on value
		$where = null;
		if (empty($options['where']['column_value'])) {
			if ($options['type'] == 'previous') {
				$options['type'] = 'first';
			}
			if ($options['type'] == 'next') {
				$options['type'] = 'first';
			}
		} else {
			if ($options['type'] == 'previous') {
				$where = ' AND ' . $this->db_object->prepare_condition([
					"{$column},<" => $options['where']['column_value']
				]);
			} else if ($options['type'] == 'next') {
				$where = ' AND ' . $this->db_object->prepare_condition([
					"{$column},>" => $options['where']['column_value']
				]);
			} else if ($options['type'] == 'refresh') {
				$where = ' AND ' . $this->db_object->prepare_condition([
					"{$column}" => $options['where']['column_value']
				]);
			}
		}
		$depends = null;
		if (!empty($options['where']['depends'])) {
			$depends = ' AND (' . $this->db_object->prepare_condition($options['where']['depends']) . ')';
		}
		$pk = implode(', ', $options['pk']);
		// generate query based on type
		switch ($options['type']) {
			case 'first':
				return "SELECT {$pk} FROM {$model->name} WHERE {$column} = (SELECT MIN({$column}) new_value FROM {$model->name} WHERE {$column} IS NOT NULL {$depends}) {$depends}";
			case 'previous':
				return "SELECT {$pk} FROM {$model->name} WHERE 1=1 {$where} {$depends} ORDER BY {$column} DESC LIMIT 1";
			case 'next':
				return "SELECT {$pk} FROM {$model->name} WHERE 1=1 {$where} {$depends} ORDER BY {$column} ASC LIMIT 1";
			case 'last':
				return "SELECT {$pk} FROM {$model->name} WHERE {$column} = (SELECT MAX({$column}) new_value FROM {$model->name} WHERE {$column} IS NOT NULL {$depends}) {$depends}";
			case 'refresh':
			default:
				return "SELECT {$pk} FROM {$model->name} WHERE 1=1 {$where} {$depends}";
		}
	}
}