<?php

class numbers_frontend_html_list_filter {

	/**
	 * Render
	 *
	 * @param object $object
	 * @return string
	 */
	public static function render($object) {
		$input = $object->options['input'];
		$filter = $object->filter;
		$full_text_search = $filter['full_text_search'] ?? null;
		unset($filter['full_text_search']);
		// generating form
		$table = [
			'header' => ['name' => 'Name', 'value' => 'Value', 'sep' => '&nbsp;', 'value2' => '&nbsp;'],
			'options' => []
		];
		// fields
		foreach ($filter as $k => $v) {
			if (!empty($v['range'])) {
				$table['options'][$k] = [
					'name' => ['value' => $v['name'], 'width' => '25%', 'class' => 'list_filter_name'],
					'value' => ['value' => self::render_column($v, $k, false, $input), 'width' => '30%'],
					'sep' => ['value' => '&mdash;', 'width' => '1%', 'class' => 'list_filter_value'],
					'value2' => ['value' => self::render_column($v, $k, true, $input), 'width' => '30%']
				];
			} else {
				$table['options'][$k] = [
					'name' => ['value' => $v['name'], 'width' => '25%', 'class' => 'list_filter_name'],
					'value' => ['value' => self::render_column($v, $k, false, $input), 'width' => '30%'],
				];
			}
		}
		// full text search last
		if (!empty($full_text_search)) {
			$names = [];
			foreach ($full_text_search as $v) {
				$names[] = $filter[$v]['name'];
			}
			$table['options']['full_text_search'] = [
				'name' => ['value' => 'Text Search', 'class' => 'list_filter_name'],
				'value' => ['value' => html::input(['name' => 'filter[full_text_search]', 'class' => 'list_filter_full_text_search', 'size' => 15, 'value' => $input['filter']['full_text_search'] ?? null])],
				'value2' => ['value' => implode(', ', $names), 'class' => 'list_filter_value']
			];
		}
		$body = html::table($table);
		$footer = html::submit(['name' => 'submit_filter', 'value' => 'Submit', 'type' => 'primary']);
		return html::modal(['id' => 'list_filter', 'class' => 'large', 'title' => 'Filter', 'body' => $body, 'footer' => $footer]);
	}

	/**
	 * Render field
	 *
	 * @param array $field
	 * @param string $key
	 * @param boolean $flag_second
	 * @param array $input
	 * @return string
	 */
	public static function render_column($field, $key, $flag_second = false, $input = []) {
		$field['method'] = $field['method'] ?? 'html::input';
		$options = [
			'id' => 'filter_' . $key . ($flag_second ? '2' : ''),
			'name' => 'filter[' . $key . ($flag_second ? '2' : '') . ']',
			'value' => $input['filter'][$key . ($flag_second ? '2' : '')] ?? null
		];
		if (!empty($field['options_model'])) {
			$class = $field['options_model'];
			$object = new $class();
			$options['options'] = $object->options();
		}
		return call_user_func_array(explode('::', $field['method']), [$options]);
	}

	/**
	 * Where SQL
	 *
	 * @param object $object
	 * @return string
	 */
	public static function where($object) {
		
	}
}