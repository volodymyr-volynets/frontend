<?php

class numbers_frontend_html_form_flow {

	/**
	 * Form flow link
	 *
	 * @var string
	 */
	public $form_flow_link;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Constructor
	 *
	 * @param string $form_flow_link
	 * @param array $options
	 */
	public function __constructor($form_flow_link, $options = []) {
		$this->form_flow_link = $form_flow_link;
		$this->options = $options;
	}

	/**
	 * Add step to the flow
	 *
	 * @param string $step_link
	 * @param array $options
	 */
	public function step($step_link, $options = []) {
		if (!isset($this->data[$step_link])) {
			$this->data[$step_link] = [
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
		} else {
			$this->data[$step_link]['options'] = array_merge_hard($this->data[$step_link]['options'], $options);
			if (isset($options['order'])) {
				$this->data[$step_link]['order'] = $options['order'];
			}
		}
	}
}