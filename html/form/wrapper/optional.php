<?php

class numbers_frontend_html_form_wrapper_optional {

	/**
	 * Validate
	 *
	 * @param object $form
	 */
	public function validate(& $form) {
		$key = $form->optional_fields['model'];
		$model_code = $form->optional_fields['optional_fields_model_code'];
		$fields = factory::model('numbers_data_optional_model_fields')->options(['where' => ['of_field_model_code' => $model_code]]);
		$model = factory::model($form->optional_fields['model']);
		$types = factory::model('object_data_types')->get();
		foreach ($form->values[$key] as $k => $v) {
			// process data types
			$temp = [
				'options' => [
					'type' => $fields[$v[$model->column_prefix . 'field_code']]['type'],
					'php_type' => $types[$fields[$v[$model->column_prefix . 'field_code']]['type']]['php_type']
				]
			];
			$name = $key . "[{$k}][" . ($model->column_prefix . 'value') . "]";
			$temp_value = $v[$model->column_prefix . 'value'] ?? null;
			$data = $form->validate_data_types_single_value($key, $temp, $temp_value, $k, $name, true);
			// check if values are set
			if (!empty($data['flag_error'])) {
				$form->values[$key][$k][$model->column_prefix . 'value'] = $temp_value;
				$form->values[$key][$k][$model->column_prefix . 'mandatory'] = (int) $v[$model->column_prefix . 'mandatory'];
			} else {
				$form->values[$key][$k][$model->column_prefix . 'value'] = $data[$key] . ''; // must be string
				$form->values[$key][$k][$model->column_prefix . 'mandatory'] = (int) $v[$model->column_prefix . 'mandatory'];
			}
			// validate if we have value
			if (empty($form->values[$key][$k][$model->column_prefix . 'value'])) {
				$form->error('danger', i18n(null, object_content_messages::$required_field), $name);
				$form->values[$key][$k][$model->column_prefix . 'value'] = '';
			}
		}
	}

	/**
	 * Render
	 *
	 * @param object $form
	 */
	public function render(& $form) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		$key = $form->optional_fields['model'];
		$model = factory::model($form->optional_fields['model']);
		$model_code = $form->optional_fields['optional_fields_model_code'];
		$fields = factory::model('numbers_data_optional_model_fields')->options(['where' => ['of_field_model_code' => $model_code]]);
		// values
		$values = $form->values[$key] ?? [];
		// building table
		$table = [
			'header' => [
				'row_number' => '',
				'row_data' => '',
			],
			'options' => [],
			'skip_header' => true
		];
		// add column header
		$row_data = [];
		$row_data['options'][0]['field_code']['field_code'] = [
			'label' => html::label(['value' => i18n(null, 'Field')]),
			'options' => [
				'percent' => 30
			]
		];
		$row_data['options'][0]['mandatory']['mandatory'] = [
			'label' => html::label(['value' => i18n(null, 'Mandatory')]),
			'options' => [
				'percent' => 10
			]
		];
		$row_data['options'][0]['value']['value'] = [
			'label' => html::label(['value' => i18n(null, 'Value')]),
			'options' => [
				'percent' => 70
			]
		];
		$table['options']['__header'] = [
			'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
			'row_data' => html::grid($row_data)
		];
		// we need to add mandatory fields
		foreach ($fields as $k => $v) {
			if (!empty($v['mandatory']) && empty($values[$model_code . '::' . $k])) {
				$values[$model_code . '::' . $k] = [
					$model->column_prefix . 'model_code' => $model_code,
					$model->column_prefix . 'field_code' => $k,
					$model->column_prefix . 'mandatory' => $v['mandatory'],
					$model->column_prefix . 'value' => null
				];
			}
		}
		// add existing fields first
		$row_number = 1;
		if (!empty($values)) {
			foreach ($values as $k => $v) {
				$row_data = [];
				$name = $key . '[' . $k . ']';
				$error_name = $key . "[{$k}][" . ($model->column_prefix . 'value') . "]";
				$row_data['options'][$row_number]['field_code']['field_code'] = [
					'value' => $form->render_element_value([
						'type' => 'field',
						'options' => [
							'id' => 'optional_fields_field_code_' . $row_number,
							'name' => $name . '[' . $model->column_prefix . 'field_code]',
							'method' => 'html::select',
							'options' => $fields
						]
					], $v[$model->column_prefix . 'field_code']),
					'options' => [
						'percent' => 30
					]
				];
				$row_data['options'][$row_number]['mandatory']['mandatory'] = [
					'value' => $form->render_element_value([
						'type' => 'field',
						'options' => [
							'id' => 'optional_fields_mandatory_' . $row_number,
							'name' => $name . '[' . $model->column_prefix . 'mandatory]',
							'method' => 'html::checkbox',
							'checked' => $fields[$v[$model->column_prefix . 'field_code']]['mandatory'],
							'disabled' => true
						]
					], $v[$model->column_prefix . 'field_code']),
					'options' => [
						'percent' => 10
					]
				];
				// error
				$error = $form->get_field_errors([
					'options' => [
						'name' => $error_name
					]
				]);
				if ($error['counter'] > 0) {
					$form->error_in_tabs($error['counter']);
				}
				$form->error_in_tabs(1, true);
				$row_data['options'][$row_number]['value']['value'] = [
					'error' => $error,
					'value' => $form->render_element_value([
						'type' => 'field',
						'options' => [
							'id' => 'optional_fields_field_code_2sd34' . $row_number,
							'name' => $name . '[' . $model->column_prefix . 'value]',
							'method' => 'html::input',
							'options' => $fields
						]
					], $v[$model->column_prefix . 'value']),
					'options' => [
						'percent' => 70
					]
				];
				// add a row to a table
				$hidden = html::hidden(['name' => $name . '[' . $model->column_prefix . 'model_code]', 'value' => $model_code]) . html::hidden(['name' => $name . '[' . $model->column_prefix . 'mandatory]', 'value' => $fields[$v[$model->column_prefix . 'field_code']]['mandatory']]);
				$table['options'][$row_number] = [
					'row_number' => ['value' => $row_number . '.' . $hidden, 'width' => '1%'],
					'row_data' => html::grid($row_data)
				];
				$row_number+= 1;
			}
		}
		// adding empty rows
		$max = $row_number + 5;
		for ($row_number = $row_number; $row_number <= $max; $row_number++) {
			$row_data = [];
			$name = $key . '[' . $row_number . ']';
			$row_data['options'][$row_number]['field_code']['field_code'] = [
				'value' => $form->render_element_value([
					'type' => 'field',
					'options' => [
						'id' => 'optional_fields_field_code_' . $row_number,
						'name' => $name . '[' . $model->column_prefix . 'field_code]',
						'method' => 'html::select',
						'options' => $fields
					]
				], null),
				'options' => [
					'percent' => 30
				]
			];
			$row_data['options'][$row_number]['mandatory']['mandatory'] = [
				'value' => $form->render_element_value([
					'type' => 'field',
					'options' => [
						'id' => 'optional_fields_mandatory_' . $row_number,
						'name' => $name . '[' . $model->column_prefix . 'mandatory]',
						'method' => 'html::checkbox',
						'checked' => false,
						'disabled' => true
					]
				], null),
				'options' => [
					'percent' => 10
				]
			];
			$row_data['options'][$row_number]['value']['value'] = [
				'value' => $form->render_element_value([
					'type' => 'field',
					'options' => [
						'id' => 'optional_fields_field_code_2sd34' . $row_number,
						'name' => $name . '[' . $model->column_prefix . 'value]',
						'method' => 'html::input',
						'options' => $fields
					]
				], ''),
				'options' => [
					'percent' => 70
				]
			];
			// add a row to a table
			$hidden = html::hidden(['name' => $name . '[' . $model->column_prefix . 'model_code]', 'value' => $model_code]);
			$table['options'][$row_number] = [
				'row_number' => ['value' => $row_number . '.' . $hidden, 'width' => '1%'],
				'row_data' => html::grid($row_data)
			];
		}
		$result['data']['html'] = html::table($table);
		$result['success'] = true;
		return $result;
	}
}