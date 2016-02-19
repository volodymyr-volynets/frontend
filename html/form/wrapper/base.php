<?php

class numbers_frontend_html_form_wrapper_base extends object_override_data {

	/**
	 * Form link
	 *
	 * @var string
	 */
	public $form_link;

	/**
	 * Form object
	 *
	 * @var object
	 */
	public $form_object;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Containers
	 *
	 * @var array
	 */
	public $containers = [];

	/**
	 * Rows
	 *
	 * @var array
	 */
	public $rows = [];

	/**
	 * Elements
	 *
	 * @var array
	 */
	public $elements = [];

	/**
	 * Constructor
	 *
	 * @param array $options
	 *		input - form input
	 *		form - form options
	 *		segment - segment options
	 *			type
	 *			header
	 *			footer
	 */
	public function __construct($options = []) {
		// we need to handle overrrides
		parent::override_handle($this);
		// step 1: create form object
		$this->form_object = new numbers_frontend_html_form_base($this->form_link, array_merge_hard($this->options, $options));
		// step 2: create all containers
		foreach ($this->containers as $k => $v) {
			$this->form_object->container($k, $v);
		}
		// step 3: create all rows
		foreach ($this->rows as $k => $v) {
			foreach ($v as $k2 => $v2) {
				$this->form_object->row($k, $k2, $v2);
			}
		}
		// step 3: create all elements
		foreach ($this->elements as $k => $v) {
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					$this->form_object->element($k, $k2, $k3, $v3);
				}
			}
		}
	}

	/**
	 * Render form
	 *
	 * @return string
	 */
	public function render() {
		$temp = $this->form_object->render();
		return $temp['data'];
	}
}