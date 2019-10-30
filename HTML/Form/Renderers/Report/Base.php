<?php

namespace Numbers\Frontend\HTML\Form\Renderers\Report;
class Base {

	/**
	 * Render
	 *
	 * @param \Object\Form\Builder\Report $object
	 * @return string
	 */
	public function render(\Object\Form\Builder\Report & $object) : string {
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_Form_Renderers_Report_Media_CSS_Base.css', -10000);
		$result = '';
		$report_counter = 1;
		foreach (array_keys($object->data) as $report_name) {
			// chart
			if ($object->data[$report_name]['options']['type'] == CHART) {
				$result.= \Numbers\Backend\IO\Chart\Base::render($object, $report_name);
				continue;
			}
			$outer_table = [
				'width' => '100%',
				'options' => [],
				'class' => 'numbers_frontend_form_report_screen_table_global'
			];
			// add headers
			$temp_inner = '';
			$counter = 1;
			// render headers
			$new_headers = [];
			foreach ($object->data[$report_name]['header'] as $header_name => $header_data) {
				if (!empty($object->data[$report_name]['header_options'][$header_name]['skip_rendering'])) continue;
				$new_headers[$header_name] = $header_data;
			}
			// loop though headers
			foreach ($new_headers as $header_name => $header_data) {
				$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_form_report_screen_table_global_row_header'];
				if ($counter == 1) {
					$inner_table['class'].= ' numbers_frontend_form_report_screen_table_global_row_header_first';
				}
				if ($counter == count($new_headers)) {
					$inner_table['class'].= ' numbers_frontend_form_report_screen_table_global_row_header_last';
				}
				foreach ($header_data as $k2 => $v2) {
					$width = $v2['width'] ?? ($v2['percent'] . '%');
					$inner_table['options'][1][$k2] = ['value' => $v2['label_name'], 'align' => $v2['align'] ?? 'left', 'nowrap' => true, 'width' => $width, 'tag' => 'th'];
				}
				$temp_inner.= \HTML::table($inner_table);
				$counter++;
			}
			$outer_table['options']['header'][2] = ['value' => $temp_inner, 'nowrap' => true, 'width' => '99%'];
			// summary
			if (!empty($object->data[$report_name]['header_summary'])) {
				$object->calculateSummary($report_name);
				$counter = 1;
				$temp_inner_summary = '';
				foreach ($new_headers as $header_name => $header_data) {
					if (empty($object->data[$report_name]['header_summary_calculated'][$header_name])) {
						continue;
					}
					$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_form_report_screen_table_global_row_summary'];
					if ($counter == 1) {
						$inner_table['class'].= ' numbers_frontend_form_report_screen_table_global_row_summary_first';
					}
					if ($counter == count($new_headers)) {
						$inner_table['class'].= ' numbers_frontend_form_report_screen_table_global_row_summary_last';
					}
					foreach ($header_data as $k2 => $v2) {
						$width = $v2['width'] ?? ($v2['percent'] . '%');
						if (isset($object->data[$report_name]['header_summary_calculated'][$header_name][$v2['__index']])) {
							$value = $object->data[$report_name]['header_summary_calculated'][$header_name][$v2['__index']]['final'];
							if (!empty($object->data[$report_name]['header_summary'][$header_name][$v2['__index']]['format'])) {
								$method = \Factory::method($object->data[$report_name]['header_summary'][$header_name][$v2['__index']]['format'], 'Format');
								$value = call_user_func_array([$method[0], $method[1]], [$value, $object->data[$report_name]['header_summary'][$header_name][$v2['__index']]['format_options'] ?? []]);
							}
						} else {
							$value = '';
						}
						$inner_table['options'][1][$k2] = ['value' => $value, 'align' => $v2['align'] ?? 'left', 'nowrap' => true, 'width' => $width, 'tag' => 'th'];
					}
					$temp_inner_summary.= \HTML::table($inner_table);
					$counter++;
				}
				$outer_table['options'][PHP_INT_MIN][2] = ['value' => $temp_inner_summary, 'nowrap' => true, 'width' => '99%'];
			}
			// render data
			$prev_odd_even = null;
			foreach ($object->data[$report_name]['data'] as $row_number => $row_data) {
				$temp_inner = '';
				$class = '';
				if (!empty($row_data[2])) { // separator
					$temp_inner.= '&nbsp;';
					$class = 'numbers_frontend_form_report_screen_table_global_separator';
				} else if (!empty($row_data[4])) { // legend
					$temp_inner.= $row_data[4];
				} else { // regular rows
					$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_report_data_inner'];
					$header = $object->data[$report_name]['header'][$row_data[3]];
					foreach ($header as $k2 => $v2) {
						$cell_class = 'numbers_frontend_form_report_screen_cell_data';
						$width = $v2['width'] ?? ($v2['percent'] . '%');
						$value = $row_data[0][$v2['__index']] ?? null;
						$align = $v2['data_align'] ?? $v2['align'] ?? 'left';
						$bold = $v2['data_bold'] ?? false;
						$total = $v2['data_total'] ?? false;
						$subtotal = $v2['data_subtotal'] ?? false;
						$underline = $v2['data_underline'] ?? false;
						$as_header = $v2['data_as_header'] ?? false;
						$topline = $v2['topline'] ?? false;
						if (is_array($value)) {
							$align = $value['align'] ?? $align;
							$bold = $value['bold'] ?? $bold;
							$underline = $value['underline'] ?? $underline;
							$as_header = $value['as_header'] ?? $as_header;
							$total = $value['total'] ?? $total;
							$subtotal = $value['subtotal'] ?? $subtotal;
							$alarm = $value['alarm'] ?? false;
							$topline = $value['topline'] ?? $topline;
							// url
							if (!empty($value['url'])) {
								$value = \HTML::a(['href' => $value['url'], 'target' => '_blank', 'value' => $value['value']]);
							} else {
								$value = $value['value'] ?? null;
							}
							// bold
							if ($bold) $cell_class.= ' bold';
							if ($underline) $cell_class.= ' underline';
							if ($as_header) $cell_class.= ' as_header';
							if ($total) $cell_class.= ' total';
							if ($subtotal) $cell_class.= ' subtotal';
							if ($alarm) $cell_class.= ' alarm';
						}
						if ($topline) $cell_class.= ' topline';
						if (isset($row_data[5]['cell_even']) && isset($value)) {
							if ($row_data[5]['cell_even'] == ODD) {
								$cell_class.= ' odd';
							} else if ($row_data[5]['cell_even'] == EVEN) {
								$cell_class.= ' even';
							}
						}
						if ($value . '' == '') $value = '&nbsp;';
						$inner_table['options'][1][$k2] = ['value' => $value, 'nowrap' => true, 'align' => $align, 'width' => $width, 'tag' => 'td', 'class' => $cell_class];
					}
					$temp_inner.= \HTML::table($inner_table);
					$class = 'numbers_frontend_form_report_screen_table_global_row_data';
					if ($row_data[1] == ODD) {
						$class.= ' numbers_frontend_form_report_screen_table_global_row_odd';
					} else if ($row_data[1] == EVEN && $prev_odd_even != EVEN) {
						$class.= ' numbers_frontend_form_report_screen_table_global_row_even';
					}
					if ($prev_odd_even != $row_data[1]) {
						$class.= ' numbers_frontend_form_report_screen_table_global_row_first_odd_even';
					}
				}
				$outer_table['options'][$row_number][2] = ['value' => $temp_inner, 'nowrap' => true, 'width' => '99%', 'class' => $class];
				$prev_odd_even = $row_data[1] ?? null;
			}
			// summary below
			if (!empty($object->data[$report_name]['header_summary'])) {
				$outer_table['options'][PHP_INT_MAX - 1001][2] = ['value' => $temp_inner_summary, 'nowrap' => true, 'width' => '99%'];
			}
			// legends after
			if (!empty($object->data[$report_name]['data_legend'])) {
				$row_number = PHP_INT_MAX - 1000;
				foreach ($object->data[$report_name]['data_legend'] as $row_number2 => $row_data) {
					$temp_inner = '';
					$class = '';
					if (!empty($row_data[2])) { // separator
						$temp_inner.= '&nbsp;';
						$class = 'numbers_frontend_form_report_screen_table_global_separator';
					} else if (!empty($row_data[4])) { // legend
						$temp_inner.= $row_data[4];
					}
					$outer_table['options'][$row_number][2] = ['value' => $temp_inner, 'nowrap' => true, 'width' => '99%', 'class' => $class];
					$row_number++;
				}
			}
			// generate a table
			$result.= \HTML::table($outer_table);
			// add separator
			if ($report_counter != 1) {
				$result.= '<hr/>';
			}
			$report_counter++;
		}
		return '<div class="numbers_frontend_form_report_screen_wrapper_outer"><div class="numbers_frontend_form_report_screen_wrapper_inner">' . $result . '</div></div>';
	}
}