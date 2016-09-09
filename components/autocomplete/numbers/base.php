<?php

class numbers_frontend_components_autocomplete_numbers_base implements numbers_frontend_components_autocomplete_interface_base {

	/**
	 * see html::autocomplete()
	 */
	public static function autocomplete($options = []) {
		// prepare params
		$params = [
			'model' => $options['options_model'],
			'where' => $options['options_params'] ?? [],
			'search_text' => $options['__ajax_autocomplete']['text'] ?? '',
			'fields' => $options['options_autocomplete_fields'],
			'pk' => $options['options_autocomplete_pk'],
		];
		// if we are making ajax request
		if (!empty($options['__ajax'])) {
			// we need to make sure we are calling the right autocomplete
			if (empty($options['__ajax_autocomplete'])) return;
			if (!empty($options['name']) && $options['name'] != $options['__ajax_autocomplete']['name'] ?? '') return;
			if (!empty($options['form_name']) && $options['form_name'] != $options['__ajax_autocomplete']['form_name'] ?? '') return;
			// generate
			$result = [
				'success' => false,
				'error' => [],
				'html' => null
			];
			// see if model has a method
			if (strpos($options['options_model'], '::') !== false) {
				$temp = explode('::');
				$params['model'] = $temp[0];
				$result['html'] = factory::model($temp[0])->{$temp[1]}($params, $options);
			} else {
				// query database
				$datasource = new object_data_autocomplete_datasource();
				$data = $datasource->get($params);
				$result['html'] = $this->render($data, $params, $options);
			}
			$result['success'] = true;
			layout::render_as($result, 'application/json');
		}
		// include js & css files
		layout::add_js('/numbers/media_submodules/numbers_frontend_components_autocomplete_numbers_autocomplete.js');
		layout::add_css('/numbers/media_submodules/numbers_frontend_components_autocomplete_numbers_autocomplete.css');
		// font awesome icons
		library::add('fontawesome');
		$result = '';
		// load values from database
		$values = [];
		$temp_model = explode('::', $options['options_model']);
		$params['model'] = $temp_model[0];
		$model = factory::model($temp_model[0]);
		// we need to generate hidden elements
		if (!empty($options['multiple'])) {
			if (!empty($options['value']) && is_array($options['value'])) {
				foreach ($options['value'] as $v) {
					if (!empty($v)) {
						// we need to conver datatype so we can query the database
						$temp = object_table_columns::process_single_column_type($options['options_autocomplete_pk'], $model->columns[$options['options_autocomplete_pk']], $v);
						if (!empty($temp[$options['options_autocomplete_pk']])) {
							$v = $temp[$options['options_autocomplete_pk']];
							$values[$v] = '';
						}
					} else {
						$v = null;
					}
					$result.= html::hidden([
						'name' => $options['name'] . '[]',
						'class' => $options['id'] . '_hidden_class',
						'value' => $v
					]);
				}
			}
		} else {
			if (!empty($options['value'])) {
				// we need to conver datatype so we can query the database
				$temp = object_table_columns::process_single_column_type($options['options_autocomplete_pk'], $model->columns[$options['options_autocomplete_pk']], $options['value']);
				if (!empty($temp[$options['options_autocomplete_pk']])) {
					$options['value'] = $temp[$options['options_autocomplete_pk']];
					$values[$options['value']] = '';
				}
			} else {
				$options['value'] = null;
			}
			$result.= html::hidden([
				'name' => $options['name'],
				'class' => $options['id'] . '_hidden_class',
				'value' => $options['value']
			]);
		}
		// if we have values
		if (!empty($values)) {
			if (!empty($temp_model[1])) {
				factory::model($temp[0])->{$temp_model[1] . '_values'}($values, $params, $options);
			} else {
				$values = $this->render_values($values, $params, $options);
			}
		}
		// need to generate autocomplete itself
		$div = html::div([
			'class' => 'form-control numbers_autocomplete',
			'id' => $options['id'],
			'value' => '',
			'contenteditable' => 'true',
			'onkeydown' => 'window[\'numbers_autocomplete_var_' . $options['id'] . '\'].onkeydown(event);'
		]);
		$icon_onclick = 'window[\'numbers_autocomplete_var_' . $options['id'] . '\'].onfocus();';
		$cancel_onclick = 'window[\'numbers_autocomplete_var_' . $options['id'] . '\'].onfocus(true, true);';
		$empty_onclick = 'window[\'numbers_autocomplete_var_' . $options['id'] . '\'].empty();';
		$icon_value = html::span(['onclick' => $icon_onclick, 'class' => 'numbers_autocomplete_icon numbers_autocomplete_prevent_selection', 'value' => html::icon(['type' => 'search'])]);
		$result.= html::input_group(['value' => $div, 'right' => $icon_value]);
		// texts
		$cancel_text = i18n(null, 'Cancel');
		$empty_text = i18n(null, 'Empty');
		// wrapper for results
		$result.= <<<TTT
			<div style="position: relative;">
				<div id="{$options['id']}_div" class="numbers_autocomplete_div numbers_autocomplete_prevent_selection" tabindex="-1" style="display: none;">
					<div id="{$options['id']}_div_content"></div>
					<div id="{$options['id']}_div_footer">
						<hr class="small" />
						<a href="javascript:void(0);" onclick="{$cancel_onclick}">{$cancel_text}</a>
						&nbsp;
						<a href="javascript:void(0);" onclick="{$empty_onclick}">{$empty_text}</a>
					</div>
				</div>
			</div>
TTT;
		// initialize
		layout::onload('numbers_autocomplete(' . json_encode(['id' => $options['id'], 'multiple' => !empty($options['multiple']), 'form_id' => $options['form_id'] ?? null, 'values' => $values, 'name' => $options['name']]) . ');');
		return $result;
	}

	/**
	 * Render
	 *
	 * @param array $data
	 * @param array $options
	 * @return string
	 */
	public function render($data, $params, $options) {
		$result = '';
		// if we have more than 10 rows
		$flag_more_rows = !empty($data[10]);
		unset($data[10]);
		// loop though data and generate a table
		$result.= '<table class="numbers_autocomplete_option_table">';
			foreach ($data as $k => $v) {
				$temp = [];
				foreach ($params['fields'] as $v2) {
					$temp[]= $v[$v2];
				}
				// adding pk if not present
				if (!in_array($params['pk'], $params['fields'])) {
					$temp[]= $v[$params['pk']];
				}
				$str = concat_ws_array(', ', $temp);
				$highlighted = regex_keywords::highlight($str, $params['search_text']);
				$onclick = 'window[\'numbers_autocomplete_var_' . $options['id'] . '\'].choose(\'' . $v[$params['pk']] . '\', \'' . addslashes($str) . '\');';
				$result.= '<tr onclick="' . $onclick . '" class="numbers_autocomplete_option_table_tr_hover">';
					$result.= '<td class="numbers_autocomplete_option_table_td">';
						$result.= $highlighted;
					$result.= '</td>';
				$result.= '</tr>';
			}
			// if we have more rows
			if ($flag_more_rows) {
				$result.= '<tr>';
					$result.= '<td style="text-align: right;"><i>' . i18n(null, 'More results available!') . '</i></td>';
				$result.= '</tr>';
			}
		$result.= '</table>';
		return $result;
	}

	/**
	 * Render values
	 *
	 * @param array $data
	 * @param array $params
	 * @param array $options
	 * @return array
	 */
	public function render_values($data, $params, $options) {
		$key = [];
		foreach ($data as $k => $v) {
			$key[] = $k;
		}
		$datasource = new object_data_autocomplete_values();
		$datasource->pk = $params['pk'];
		$params['where'] = [];
		$params['where'][$params['pk'] . ',IN'] = $key;
		$temp = $datasource->get($params);
		foreach ($temp as $k => $v) {
			$temp = [];
			foreach ($params['fields'] as $v2) {
				$temp[]= $v[$v2];
			}
			// adding pk if not present
			if (!in_array($params['pk'], $params['fields'])) {
				$temp[]= $v[$params['pk']];
			}
			$str = concat_ws_array(', ', $temp);
			$data[$k] = $str;
		}
		return $data;
	}
}