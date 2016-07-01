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
		// we need to fix keys
		$data = [];
		if (!empty($form->values[$key])) {
			foreach ($form->values[$key] as $k => $v) {
				$data[$model_code . '::' . $v[$model->column_prefix . 'field_code']] = $v;
			}
		}
		$form->values[$key] = $data;
		//pk([$model->column_prefix . 'field_code'], $form->values[$key]);
		$types = factory::model('object_data_types')->get();
		foreach ($form->values[$key] as $k => $v) {
			$k2 = $v[$model->column_prefix . 'field_code'];
			// process data types
			$temp = [
				'options' => [
					'type' => $fields[$k2]['type'],
					'php_type' => $types[$fields[$k2]['type']]['php_type']
				]
			];
			$name = $key . "[{$k2}][" . ($model->column_prefix . 'value') . "]";
			$data = $form->validate_data_types_single_value($key, $temp, $v[$model->column_prefix . 'value'] ?? null, $k, $name, true);
			// check if values are set
			if (!empty($data['flag_error'])) {
				continue;
			}
			// put new value into values
			$form->values[$key][$k][$model->column_prefix . 'value'] = $data[$key] . ''; // must be string
			$form->values[$key][$k][$model->column_prefix . 'mandatory'] = (int) $v[$model->column_prefix . 'mandatory'];
			// validate if we have value
			if (empty($form->values[$key][$k][$model->column_prefix . 'value'])) {
				$form->error('danger', i18n(null, object_content_messages::$required_field), $name);
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
		// we would assemble everyting into $data variable
		$data = [
			'options' => []
		];
		// header row
		$data['options']['__header_row']['row_number']['row_number'] = [
			'label' => '&nbsp;',
			'options' => [
				'percent' => 1
			],
			'class' => 'grid_counter_row'
		];
		$data['options']['__header_row']['field_code']['field_code'] = [
			'label' => i18n(null, 'Field'),
			'options' => [
				'percent' => 25
			]
		];
		$data['options']['__header_row']['mandatory']['mandatory'] = [
			'label' => i18n(null, 'Mandatory'),
			'options' => [
				'percent' => 10
			]
		];
		$data['options']['__header_row']['value']['value'] = [
			'label' => i18n(null, 'Value'),
			'options' => [
				'percent' => 60
			]
		];
		// we need to add mandatory fields
		pk([$model->column_prefix . 'field_code'], $values);
		foreach ($fields as $k => $v) {
			if (!empty($v['mandatory']) && empty($values[$k])) {
				$values[$k] = [
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
				$name = $key . '[' . $row_number . ']';
				$error_name = $key . "[{$k}][" . ($model->column_prefix . 'value') . "]";
				$data['options'][$row_number]['row_number']['row_number'] = [
					'value' => $row_number . '.' . html::hidden(['name' => $name . '[' . $model->column_prefix . 'model_code]', 'value' => $model_code]) . html::hidden(['name' => $name . '[' . $model->column_prefix . 'mandatory]', 'value' => $fields[$k]['mandatory']]),
					'options' => [
						'percent' => 1
					],
					'class' => 'grid_counter_row',
					'row_class' => $row_number % 2 ? 'grid_row_even' : 'grid_row_odd'
				];
				$data['options'][$row_number]['field_code']['field_code'] = [
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
						'percent' => 25
					]
				];
				$data['options'][$row_number]['mandatory']['mandatory'] = [
					'value' => $form->render_element_value([
						'type' => 'field',
						'options' => [
							'id' => 'optional_fields_mandatory_' . $row_number,
							'name' => $name . '[' . $model->column_prefix . 'mandatory]',
							'method' => 'html::checkbox',
							'checked' => $fields[$k]['mandatory'],
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
				$data['options'][$row_number]['value']['value'] = [
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
						'percent' => 60
					]
				];
				$row_number+= 1;
			}
		}
		// adding empty rows
		$max = $row_number + 5;
		for ($row_number = $row_number; $row_number <= $max; $row_number++) {
			$name = $key . '[' . $row_number . ']';
			$data['options'][$row_number]['row_number']['row_number'] = [
				'value' => $row_number . '.' . html::hidden(['name' => $name . '[' . $model->column_prefix . 'model_code]', 'value' => $model_code]),
				'options' => [
					'percent' => 1
				],
				'class' => 'grid_counter_row',
				'row_class' => $row_number % 2 ? 'grid_row_even' : 'grid_row_odd'
			];
			$data['options'][$row_number]['field_code']['field_code'] = [
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
					'percent' => 25
				]
			];
			$data['options'][$row_number]['mandatory']['mandatory'] = [
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
			$data['options'][$row_number]['value']['value'] = [
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
					'percent' => 60
				]
			];
		}
		$result['data']['html'] = html::grid($data);
		$result['success'] = true;
		return $result;
	}
}