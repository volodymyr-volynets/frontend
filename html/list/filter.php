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
			'header' => ['name' => i18n(null, 'Column'), 'value' => i18n(null, 'Value'), 'sep' => '&nbsp;', 'value2' => '&nbsp;'],
			'options' => []
		];
		// fields
		foreach ($filter as $k => $v) {
			if (!empty($v['range'])) {
				$table['options'][$k] = [
					'name' => ['value' => i18n(null, $v['name']) . ':', 'width' => '25%', 'class' => 'list_filter_name'],
					'value' => ['value' => self::render_column($v, $k, false, $input), 'width' => '30%'],
					'sep' => ['value' => '&mdash;', 'width' => '1%', 'class' => 'list_filter_value'],
					'value2' => ['value' => self::render_column($v, $k, true, $input), 'width' => '30%']
				];
			} else {
				$table['options'][$k] = [
					'name' => ['value' => i18n(null, $v['name']) . ':', 'width' => '25%', 'class' => 'list_filter_name'],
					'value' => ['value' => self::render_column($v, $k, false, $input), 'width' => '30%'],
				];
			}
		}
		// full text search last
		if (!empty($full_text_search)) {
			$names = [];
			foreach ($full_text_search as $v) {
				$names[] = i18n(null, $filter[$v]['name']);
			}
			$table['options']['full_text_search'] = [
				'name' => ['value' => i18n(null, 'Text Search') . ':', 'class' => 'list_filter_name'],
				'value' => ['value' => html::input(['name' => 'filter[full_text_search]', 'class' => 'list_filter_full_text_search', 'size' => 15, 'value' => $input['filter']['full_text_search'] ?? null])],
				'value2' => ['value' => implode(', ', $names), 'class' => 'list_filter_value']
			];
		}
		$body = html::table($table);
		$footer = html::button2([
			'name' => 'submit_filter',
			'value' => i18n(null, 'Submit'),
			'type' => 'primary',
			'onclick' => "numbers.modal.hide('list_{$object->list_link}_filter'); $('#list_{$object->list_link}_form').attr('target', '_self'); $('#list_{$object->list_link}_form').attr('no_ajax', ''); return true;"
		]);
		return html::modal(['id' => "list_{$object->list_link}_filter", 'class' => 'large', 'title' => i18n(null, 'Filter'), 'body' => $body, 'footer' => $footer]);
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
		$options = array_merge_hard($field, $options);
		if (!empty($field['options_model'])) {
			$params = $field['options_params'] ?? [];
			$options['options'] = factory::model($field['options_model'])->options(['where' => $params, 'i18n' => true]);
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
		$input = $object->options['input'];
		$filter = $object->filter;
		$full_text_search = $filter['full_text_search'] ?? null;
		unset($filter['full_text_search']);
		// generate values
		$result = [];
		foreach ($filter as $k => $v) {
			if (!empty($v['range'])) {
				$start = object_table_columns::process_single_column($k, $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				if (array_key_exists($k, $start) && $start[$k] !== null && (is_string($start[$k]) && $start[$k] != '')) {
					$result[$k . ',>='] = $start[$k];
				}
				$end = object_table_columns::process_single_column($k . '2', $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				if (array_key_exists($k . '2', $end) && $end[$k . '2'] !== null && (is_string($start[$k]) && $start[$k] != '')) {
					$result[$k . ',<='] = $end[$k . '2'];
				}
			} else {
				$start = object_table_columns::process_single_column($k, $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				if (array_key_exists($k, $start) && $start[$k] !== null && (is_string($start[$k]) && $start[$k] != '')) {
					$operator = $v['operator'] ?? '=';
					if ($operator == '=' && is_array($start[$k])) {
						$operator = 'in';
					}
					$result[$k . ',' . $operator] = $start[$k];
				}
			}
		}
		// full text search
		if (!empty($full_text_search) && ($input['filter']['full_text_search'] ?? null) . '' != '') {
			$result['full_text_search,fts'] = [
				'str' => $input['filter']['full_text_search'] . '',
				'fields' => $full_text_search
			];
		}
		return $result;
	}

	/**
	 * Format filter string as human readable
	 *
	 * @param object $object
	 * @return array
	 */
	public static function human($object) {
		$input = $object->options['input'];
		$filter = $object->filter;
		$full_text_search = $filter['full_text_search'] ?? null;
		unset($filter['full_text_search']);
		// generate values
		$result = [];
		foreach ($filter as $k => $v) {
			if (!empty($v['range'])) {
				$start = object_table_columns::process_single_column($k, $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				$end = object_table_columns::process_single_column($k . '2', $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				$result[i18n(null, $v['name'])] = '(' . ($start[$k] ?? null) . ') - (' . ($end[$k . '2'] ?? null) . ')';
			} else {
				$start = object_table_columns::process_single_column($k, $v, $input['filter'] ?? [], ['process_domains' => true, 'ignore_defaults' => true, 'ignore_not_set_fields' => true]);
				// we need to process arrays
				if (isset($start[$k]) && is_array($start[$k])) {
					if (!empty($v['options_model'])) {
						$params = $v['options_params'] ?? [];
						$start[$k] = array_options_to_string(factory::model($v['options_model'])->options(['where' => $params, 'i18n' => true]), $start[$k]);
					} else {
						$start[$k] = implode(', ', $start[$k]);
					}
				}
				$result[i18n(null, $v['name'])] = $start[$k] ?? null;
			}
		}
		// full text search
		if (!empty($full_text_search)) {
			$names = [];
			foreach ($full_text_search as $v) {
				$names[] = i18n(null, $filter[$v]['name']);
			}
			$result[i18n(null, 'Text Search')] = ($input['filter']['full_text_search'] ?? null)  . ' (' . implode(', ', $names) . ')';
		}
		return $result;
	}
}