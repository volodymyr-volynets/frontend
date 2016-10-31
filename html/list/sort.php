<?php

class numbers_frontend_html_list_sort {

	/**
	 * Render
	 *
	 * @param object $object
	 * @return string
	 */
	public static function render($object) {
		$input = $object->options['input'];
		if (empty($input['sort'])) {
			$i = 0;
			foreach ($object->orderby as $k => $v) {
				$input['sort'][$i]['column'] = $k;
				$input['sort'][$i]['order'] = $v;
				$i++;
			}
		}
		// generating form
		$table = [
			'header' => ['row_number' => '&nbsp;', 'column' => i18n(null, 'Column'), 'order' => i18n(null, 'Order')],
			'options' => []
		];
		$order_model = new object_data_model_order();
		$columns = [];
		// we need to skip certain columns
		foreach ($object->columns as $k => $v) {
			if (!in_array($k, ['row_number', 'offset_number', 'action'])) {
				$v['name'] = i18n(null, $v['name']);
				$columns[$k] = $v;
			}
		}
		// full text search goes last
		if (!empty($object->filter['full_text_search'])) {
			$columns['full_text_search'] = ['name' => i18n(null, 'Text Search')];
		}
		// render 5 rows
		for ($i = 0; $i < 5; $i++) {
			if (empty($input['sort'][$i]['column'])) {
				$input['sort'][$i]['order'] = SORT_ASC;
			}
			$column = html::select(['id' => 'sort_' . $i . '_column', 'name' => 'sort[' . $i . '][column]', 'options' => $columns, 'value' => $input['sort'][$i]['column'] ?? null]);
			$order = html::select(['id' => 'sort_' . $i . '_order', 'name' => 'sort[' . $i . '][order]', 'no_choose' => true, 'options' => $order_model->options(['i18n' => true]), 'value' => $input['sort'][$i]['order'] ?? null]);
			$table['options'][$i] = [
				'row_number' => ['value' => ($i + 1) . '.', 'width' => '1%', 'align' => 'right'],
				'column' => ['value' => $column, 'width' => '25%', 'class' => 'list_sort_name'],
				'order' => ['value' => $order, 'width' => '30%'],
			];
		}
		$body = html::table($table);
		$footer = html::button2([
			'name' => 'submit_sort',
			'value' => i18n(null, 'Submit'),
			'type' => 'primary',
			'onclick' => "numbers.modal.hide('list_{$object->list_link}_sort'); $('#list_{$object->list_link}_form').attr('target', '_self'); $('#list_{$object->list_link}_form').attr('no_ajax', ''); return true;"]);
		return html::modal(['id' => "list_{$object->list_link}_sort", 'class' => 'large', 'title' => i18n(null, 'Sort'), 'body' => $body, 'footer' => $footer]);
	}
}