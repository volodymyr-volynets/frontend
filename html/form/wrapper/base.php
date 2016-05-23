<?php

class numbers_frontend_html_form_wrapper_base extends object_override_data {

	/**
	 * Separators
	 */
	const SEPARATOR_VERTICAL = '__separator_vertical';
	const SEPARATOR_HORISONTAL = '__separator_horizontal';

	/**
	 * Row for buttons
	 */
	const BUTTONS = '__submit_buttons';

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
	private $form_object;

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
		// step 0: apply data fixes
		if (method_exists($this, 'overrides')) {
			$this->overrides();
		}
		// step 1: create form object
		$this->form_object = new numbers_frontend_html_form_base($this->form_link, array_merge_hard($this->options, $options));
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
				$this->form_object->wrapper_methods[$v] = [$this, $v];
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