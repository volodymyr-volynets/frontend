<?php

class numbers_frontend_html_list_export {

	/**
	 * Render
	 *
	 * @param object $object
	 * @return string
	 */
	public static function render($object) {
		$input = $object->options['input'];
		// generating form
		$table = [
			'header' => ['column' => i18n(null, 'Column'), 'value' => i18n(null, 'Value')],
			'options' => [],
			'skip_header' => 1
		];
		$model = new object_content_exports();
		$table['options'][0]['column'] = ['value' => i18n(null, 'Format') . ':', 'width' => '1%', 'nowrap' => true, 'class' => 'list_filter_name'];
		$table['options'][0]['value']['value'] = html::select(['id' => 'export_format', 'name' => 'export[format]', 'options' => $model->options(['i18n' => true]), 'value' => $input['export']['format'] ?? null]);
		$body = html::table($table);
		$footer = html::button2(['name' => 'submit_export', 'value' => i18n(null, 'Submit'), 'type' => 'primary', 'onclick' => "if ($('#export_format').val() == 'html2') { $('#list_{$object->list_link}_form').attr('target', '_blank'); } else { $('#list_{$object->list_link}_form').attr('target', '_self'); } $('#list_{$object->list_link}_form').attr('no_ajax', 1); numbers.modal.hide('list_{$object->list_link}_export'); return true;"]);
		return html::modal(['id' => "list_{$object->list_link}_export", 'class' => '', 'title' => i18n(null, 'Export/Print'), 'body' => $body, 'footer' => $footer]);
	}

	/**
	 * Export
	 *
	 * @param object $object
	 * @param string $format
	 * @return string
	 */
	public static function export($object, $format = 'html') {
		$header = [
			'name' => i18n(null, object_controller::title()),
			'filter' => numbers_frontend_html_list_filter::human($object)
		];
		// sort
		if (!empty($object->orderby)) {
			$temp = [];
			foreach ($object->orderby as $k => $v) {
				if ($k == 'full_text_search') {
					$temp[] = i18n(null, 'Text Search') . ' ' . ($v == SORT_ASC ? 'Asc.' : 'Desc.');
				} else {
					$temp[] = i18n(null, $object->columns[$k]['name']) . ' ' . ($v == SORT_ASC ? 'Asc.' : 'Desc.');
				}
			}
			$header['filter'][i18n(null, 'Sort')] = implode(', ', $temp);
		}
		// report object
		$report = new numbers_frontend_html_report_base($header);
		// adding columns
		$columns = [];
		foreach ($object->columns as $k => $v) {
			if ($k == 'action') {
				continue;
			}
			$columns[] = ['value' => i18n(null, $v['name']), 'bold' => true, 'width' => 10];
		}
		$report->add($columns, 'columns');
		// adding data to report
		if (empty($object->rows)) {
			$report->add([['value' => i18n(null, object_content_messages::no_rows_found)]]);
		} else {
			$counter = 1;
			foreach ($object->rows as $k => $v) {
				$data = [];
				foreach ($object->columns as $k2 => $v2) {
					// we skip action column when exporting
					if ($k2 == 'action') {
						continue;
					}
					$value = [];
					// process rows
					if ($k2 == 'row_number') {
						$value['value'] = $counter . '.';
					} else if ($k2 == 'offset_number') {
						$value['value'] = ($object->offset + $counter) . '.';
					} else if (!empty($v2['options']) && !is_array($v[$k2])) {
						$value['value'] = $v2['options'][$v[$k2]]['name'];
					} else if (isset($v[$k2])) {
						$value['value'] = $v[$k2];
					} else {
						$value['value'] = null;
					}
					// put value into row
					$data[] = $value;
				}
				$report->add($data);
				$counter++;
			}
		}
		// render report
		return $report->render($format);
	}
}