<?php

class numbers_frontend_html_form_model_datasource_navigation extends object_datasource {
	public $pk;
	public $cache = false;
	public $cache_tags = [];
	public $cache_memory = false;
	public function query($options = []) {
		$db = factory::model($options['model'])->db_object();
		$where = null;
		if (!empty($options['where'])) {
			if ($options['type'] == 'previous') {
				$options['where'][$options['column'] . ',<'] = $options['where'][$options['column']];
				unset($options['where'][$options['column']]);
			} else if ($options['type'] == 'next') {
				$options['where'][$options['column'] . ',>'] = $options['where'][$options['column']];
				unset($options['where'][$options['column']]);
			}
			$where = 'AND ' . $db->prepare_condition($options['where']);
		}
		switch ($options['type']) {
			case 'first':
				return "SELECT {$options['pk']} FROM [table[{$options['model']}]] WHERE {$options['column']} = (SELECT MIN({$options['column']}) new_value FROM [table[{$options['model']}]] WHERE {$options['column']} IS NOT NULL)";
				break;
			case 'previous':
				return "SELECT {$options['pk']} FROM [table[{$options['model']}]] WHERE 1=1 {$where} ORDER BY {$options['column']} DESC LIMIT 1";
				break;
			case 'next':
				return "SELECT {$options['pk']} FROM [table[{$options['model']}]] WHERE 1=1 {$where} ORDER BY {$options['column']} ASC LIMIT 1";
				break;
			case 'last':
				return "SELECT {$options['pk']} FROM [table[{$options['model']}]] WHERE {$options['column']} = (SELECT MAX({$options['column']}) new_value FROM [table[{$options['model']}]] WHERE {$options['column']} IS NOT NULL)";
				break;
			case 'refresh':
			default:
				return "SELECT {$options['pk']} FROM [table[{$options['model']}]] WHERE 1=1 {$where}";
		}
	}
}