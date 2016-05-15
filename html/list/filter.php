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
			'header' => ['name' => 'Name', 'value' => 'Value(s)'],
			'options' => []
		];
		if (!empty($full_text_search)) {
			$names = [];
			foreach ($full_text_search as $v) {
				$names[] = $filter[$v]['name'];
			}
			$table['options']['full_text_search'] = [
				'name' => ['value' => 'Text Search'],
				'value' => ['value' => html::input(['name' => 'filter[full_text_search]', 'class' => 'list_filter_full_text_search', 'size' => 15, 'value' => $input['filter']['full_text_search'] ?? null]) . ' ' . implode(', ', $names)]
			];
		}
		
		
		$body = html::table($table);
		$footer = '';
		return html::modal(['id' => 'list_filter', 'class' => 'large', 'title' => 'Filter', 'body' => $body, 'footer' => $footer]);
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