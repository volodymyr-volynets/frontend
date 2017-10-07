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
					$inner_table['options'][1][$k2] = ['value' => $v2['label_name'], 'nowrap' => true, 'width' => $width, 'tag' => 'th'];
				}
				$temp_inner.= \HTML::table($inner_table);
				$counter++;
			}
			$outer_table['options']['header'][2] = ['value' => $temp_inner, 'nowrap' => true, 'width' => '99%'];
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
						$align = $v2['data_align'] ?? '';
						$bold = $v2['data_bold'] ?? false;
						$total = $v2['data_total'] ?? false;
						$underline = $v2['data_underline'] ?? false;
						$as_header = $v2['data_as_header'] ?? false;
						if (is_array($value)) {
							$align = $value['align'] ?? $align;
							$bold = $value['bold'] ?? $bold;
							$underline = $value['underline'] ?? $underline;
							$as_header = $value['as_header'] ?? $as_header;
							$total = $value['total'] ?? $total;
							$alarm = $value['alarm'] ?? false;
							// url
							if (!empty($value['url'])) {
								$value = \HTML::a(['href' => $value['url'], 'value' => $value['value']]);
							} else {
								$value = $value['value'] ?? null;
							}
							// bold
							if ($bold) $cell_class.= ' bold';
							if ($underline) $cell_class.= ' underline';
							if ($as_header) $cell_class.= ' as_header';
							if ($total) $cell_class.= ' total';
							if ($alarm) $cell_class.= ' alarm';
						}
						if (isset($row_data[5]['cell_even']) && $value . '' != '') {
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