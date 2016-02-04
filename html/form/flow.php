<?php

class numbers_frontend_html_form_flow {
	public $form_flow_link;
	public $options = [];
	public $steps = [];
	public function __constructor($form_flow_link, $steps, $options = []) {
		$this->form_flow_link = $form_flow_link;
		$this->steps = $steps;
		$this->options = $options;
	}
	public function process_steps() {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		return $result;
	}
}