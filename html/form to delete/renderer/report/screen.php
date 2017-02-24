<?php

class numbers_frontend_html_form_renderer_report_screen {

	/**
	 * Render
	 */
	public function render(& $report_object) {
		// table
		$table_global = [
			'header' => [
				'row_data' => ['value' => '', 'width' => '10%']
			],
			'skip_header' => true,
			'width' => '100%',
			'class' => 'numbers_frontend_form_report_screen_table_global'
		];
		// generate headers
		$headers = [];
		foreach (array_keys($report_object->data) as $link) {
			$row_ids = array_keys($report_object->data[$link]['header']);
			foreach ($row_ids as $row_id) {
				$headers[$link][$row_id] = [];
				foreach ($report_object->data[$link]['header'][$row_id]['data'] as $k => $v) {
					//unset($v['data']);
					$headers[$link][$row_id][$v['index']] = $v;
				}
			}
		}
		// render reports
		$row_number = 0;
		foreach (array_keys($headers) as $link) {
			// render headers
			$header_first_class = 'numbers_frontend_form_report_screen_table_global_row_header_first';
			end($headers[$link]);
			$last_key = key($headers[$link]);
			foreach ($headers[$link] as $row_id => $row_data) {
				if (!empty($report_object->data[$link]['header'][$row_id]['options']['do_not_render'])) continue;
				if ($last_key == $row_id) {
					$header_first_class.= ' numbers_frontend_form_report_screen_table_global_row_header_last';
				}
				$table_local = [
					'header' => $row_data,
					'options' => [],
					'width' => '100%',
					'class' => ' '
				];
				$table_global['options'][$row_number]['row_data'] = ['value' => html::table($table_local), 'tag' => 'th', 'class' => 'numbers_frontend_form_report_screen_table_global_row_header ' . $header_first_class];
				$row_number++;
				$header_first_class = '';
			}
			// render data
			$current_even_class = null;
			foreach ($report_object->data[$link]['rows'] as $data) {
				// merge data
				$global_class = '';
				$temp = [];
				$even_class = '';
				foreach ($headers[$link][$data['row_id']] as $k2 => $v2) {
					$data['data'][$k2] = array_merge_hard($v2['data'], $data['data'][$k2]);
					$data['data'][$k2]['width'] = $v2['width'];
					$data['data'][$k2]['class'] = 'numbers_frontend_form_report_screen_cell_data';
					// styles
					foreach (['bold', 'subtotal', 'total', 'underline', 'linethrough', 'blank'] as $style) {
						if (isset($data['data'][$k2][$style])) {
							$data['data'][$k2]['class'] = ($data['data'][$k2]['class'] ?? '') . ' numbers_frontend_form_report_screen_cell_' . $style;
						}
					}
					// row class
					if (isset($data['data'][$k2]['global_class'])) {
						$global_class.= ' ' . $data['data'][$k2]['global_class'];
					}
				}
				// process even
				if (isset($data['even'])) {
					if ($data['even'] == $report_object::odd) {
						$even_class.= ' numbers_frontend_form_report_screen_table_global_row_odd';
					} else if ($data['even'] == $report_object::even) {
						$even_class.= ' numbers_frontend_form_report_screen_table_global_row_even';
					} else if ($data['even'] == $report_object::blank) {
						$even_class.= ' numbers_frontend_form_report_screen_table_global_row_blank';
					}
				}
				if ($current_even_class !== $even_class) {
					$current_even_class = $even_class;
				} else {
					$even_class.= ' numbers_frontend_form_report_screen_table_global_row_same_even';
				}
				// build a row table
				$table_local = [
					'header' => $headers[$link][$data['row_id']],
					'options' => [
						$data['data']
					],
					'width' => '100%',
					'skip_header' => true,
					'class' => ' '
				];
				$table_global['options'][$row_number]['row_data'] = ['value' => html::table($table_local), 'class' => 'numbers_frontend_form_report_screen_table_global_row_data ' . $even_class . $global_class];
				$row_number++;
			}
		}
		// print button
		$print = '<hr/>' . html::button2(['onclick' => 'window.print(); return false;', 'value' => html::icon(['type' => 'print']) . ' ' . i18n(null, 'Print'), 'icon' => 'print']);
		$content = '<div class="numbers_frontend_form_report_screen">' . html::table($table_global) . '</div>';
		// printable
		if ($report_object->options['format'] == 'printable') {
			layout::render_as($content, 'text/html');
		} else {
			return $content;
		}
	}
}