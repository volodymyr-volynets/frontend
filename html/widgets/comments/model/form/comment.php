<?php

class numbers_frontend_html_widgets_comments_model_form_comment extends numbers_frontend_html_form_wrapper_base {
	public $form_link = 'numbers_frontend_html_widgets_comments_model_form_comment';
	public $options = [];
	public $containers = [
		'default' => ['default_row_type' => 'grid', 'order' => 1]
	];
	public $rows = [
		'default' => [
			'comment' => ['order' => 100],
			'important' => ['order' => 200]
		]
	];
	public $elements = [
		'default' => [
			'comment' => [
				'comment' => ['order' => 1, 'label_name' => 'Comment', 'type' => 'text', 'percent' => 100, 'required' => true, 'method' => 'textarea', 'rows' => 8]
			],
			'important' => [
				'important' => ['order' => 1, 'label_name' => 'Important', 'type' => 'boolean', 'percent' => 25, 'required' => false, 'method' => 'select', 'no_choose' => true, 'options_model' => 'object_data_model_inactive']
			]
		]
	];
	public $collection = [];

	public function overrides() {
		// todo: handle overrides here
	}

	public function validate(& $form) {
		// validation
	}

	public function save(& $form) {
		$model = Factory::model($form->options['other']['model']);
		$save = [
			$model->column_prefix . 'important' => !empty($form->values['important']) ? 1 : 0,
			$model->column_prefix . 'comment_value' => $form->values['comment'] . '',
			$model->column_prefix . 'who_entity_id' => Session::get('numbers.entity.em_entity_id'),
			$model->column_prefix . 'inserted' => Format::now('timestamp')
		];
		foreach ($form->options['other']['map'] as $k => $v) {
			$save[$v] = $form->options['other']['pk'][$k];
		}
		$save_result = $model->save($save, ['ignore_not_set_fields' => true]);
		if ($save_result['success']) {
			$form->error('success', 'Comment has been added successfully!');
		} else {
			$form->error('danger', 'Could not add comment!');
		}
	}
}