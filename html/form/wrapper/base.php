<?php

class numbers_frontend_html_form_wrapper_base extends numbers_frontend_html_form_wrapper_parent {

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
	 * Collection
	 *
	 * @var mixed
	 */
	public $collection;

	/**
	 * Optional fields
	 *
	 * @var array
	 */
	public $optional_fields;

	/**
	 * A list of wraper methods
	 *
	 * @var array
	 */
	public $wrapper_methods = [];

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
		// step 0: apply data fixes
		if (method_exists($this, 'overrides')) {
			$this->overrides();
		}
		// step 1: create form object
		$this->form_object = new numbers_frontend_html_form_base($this->form_link, array_merge_hard($this->options, $options));
		// add collection & optional fields
		$this->form_object->collection = $this->collection;
		$this->form_object->optional_fields = $this->optional_fields;
		// step 2: create all containers
		foreach ($this->containers as $k => $v) {
			if ($v === null) {
				continue;
			}
			$this->form_object->container($k, $v);
		}
		// step 3: create all rows
		foreach ($this->rows as $k => $v) {
			foreach ($v as $k2 => $v2) {
				if ($v2 === null) {
					continue;
				}
				$this->form_object->row($k, $k2, $v2);
			}
		}
		// step 3: create all elements
		foreach ($this->elements as $k => $v) {
			foreach ($v as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if ($v3 === null) {
						continue;
					}
					$this->form_object->element($k, $k2, $k3, $v3);
				}
			}
		}
		// step 3: methods
		foreach (['save', 'validate'] as $v) {
			if (method_exists($this, $v)) {
				$this->form_object->wrapper_methods[$v]['main'] = [$this, $v];
			}
		}
		// extensions can have their own verify methods
		if (!empty($this->wrapper_methods)) {
			foreach ($this->wrapper_methods as $k => $v) {
				$index = 1;
				foreach ($v as $k2 => $v2) {
					$this->form_object->wrapper_methods[$k][$index] = [new $k2, $v2];
					$index++;
				}
			}
		}
		// last step: process form
		$this->form_object->process();
	}

	/**
	 * Render form
	 *
	 * @param string $format
	 * @return string
	 */
	public function render($format = 'text/html') {
		return $this->form_object->render($format);
	}
}