<?php

class numbers_frontend_html_form_base {

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
	 * @param string $form_link
	 * @param array $options
	 */
	public function __construct($form_link, $options = []) {
		$form_link.= '';
		$this->form_link = $form_link;
		$this->options = $options;
	}

	/**
	 * Add container to the form
	 *
	 * @param string $container_link
	 * @param array $options
	 */
	public function container($container_link, $options = []) {
		if (!isset($this->data[$container_link])) {
			$this->data[$container_link] = [
				'rows' => [],
				'options' => $options,
				'order' => $options['order'] ?? 0,
				'flag_child' => !empty($options['flag_child']),
				'default_row_type' => $options['default_row_type'] ?? 'grid'
			];
		} else {
			$this->data[$container_link]['options'] = array_merge_hard($this->data[$container_link]['options'], $options);
			if (isset($options['order'])) {
				$this->data[$container_link]['order'] = $options['order'];
			}
		}
	}

	/**
	 * Add row to the container
	 *
	 * @param string $container_link
	 * @param string $row_link
	 * @param array $options
	 */
	public function row($container_link, $row_link, $options = []) {
		$this->container($container_link);
		if (!isset($this->data[$container_link]['rows'][$row_link])) {
			// validating row type
			$type_model = new object_type_form_row_type();
			if (!isset($options['type']) || !isset($type_model->data[$options['type']])) {
				$options['type'] = $this->data[$container_link]['default_row_type'];
			}
			// setting values
			$this->data[$container_link]['rows'][$row_link] = [
				'type' => $options['type'],
				'elements' => [],
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
		} else {
			$this->data[$container_link]['rows'][$row_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['options'], $options);
			if (isset($options['order'])) {
				$this->data[$container_link]['rows'][$row_link]['order'] = $options['order'];
			}
		}
	}

	/**
	 * Add lement to the row
	 *
	 * @param string $container_link
	 * @param string $row_link
	 * @param string $field_link
	 * @param array $options
	 */
	public function element($container_link, $row_link, $field_link, $options = []) {
		// presetting options for buttons, making them last
		if ($row_link == $this::BUTTONS) {
			$options['row_type'] = 'grid';
			if (!isset($options['row_order'])) {
				$options['row_order'] = PHP_INT_MAX;
			}
		}
		// processing row and container
		$this->container($container_link, array_key_extract_by_prefix($options, 'container_'));
		$this->row($container_link, $row_link, array_key_extract_by_prefix($options, 'row_'));
		// setting value
		if (!isset($this->data[$container_link]['rows'][$row_link]['elements'][$field_link])) {
			// name and id
			$options['name'] = $options['id'] = $this->form_link . '_' . $field_link;
			// child container
			$container = null;
			if ($this->data[$container_link]['rows'][$row_link]['type'] == 'tabs') {
				$type = 'tab';
				$container = $options['child_container_link'];
				// autosetting child value if type is tabs
				$this->data[$container_link]['flag_child'] = true;
			} else if (in_array($this->data[$container_link]['rows'][$row_link]['type'], ['grid', 'table', 'details'])) {
				$type = 'field';
			}
			// vertical separator
			if ($field_link == $this::SEPARATOR_VERTICAL) {
				$options['element_vertical_separator'] = true;
			}
			// setting data
			$this->data[$container_link]['rows'][$row_link]['elements'][$field_link] = [
				'type' => $type,
				'container' => $container,
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
		} else {
			$this->data[$container_link]['rows'][$row_link]['elements'][$field_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['elements'][$field_link], $options);
		}
	}

	/**
	 * Render form
	 */
	public function render($format = 'text/html') {
		$result = [
			'success' => false,
			'error' => [],
			'data' => []
		];
		// order containers based on order column
		array_key_sort($this->data, ['order' => SORT_ASC]);
		foreach ($this->data as $k => $v) {
			if (!$v['flag_child']) {
				$temp = $this->render_container($k);
				if ($temp['success']) {
					$result['data'][$k] = $temp['data'];
				}
			}
		}
		// formatting data
		if ($format == 'text/html') {
			$temp = [];
			foreach ($result['data'] as $k => $v) {
				$temp[] = $v['html'];
			}
			$result['data'] = implode('', $temp);
			// if we have form
			if (isset($this->options['form'])) {
				$temp = $this->options['form'];
				$temp['value'] = $result['data'];
				$result['data'] = html::form($temp);
			}
			// if we have segment
			if (isset($this->options['segment'])) {
				$temp = is_array($this->options['segment']) ? $this->options['segment'] : [];
				$temp['value'] = $result['data'];
				$result['data'] = html::segment($temp);
			}
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Render form component
	 *
	 * @param string $container_link
	 */
	public function render_container($container_link) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		// sorting rows
		array_key_sort($this->data[$container_link]['rows'], ['order' => SORT_ASC]);
		// grouping data by row type
		// todo: handle separator
		$grouped = [];
		$index = 0;
		$last_type = null;
		foreach ($this->data[$container_link]['rows'] as $k => $v) {
			if (!$last_type) {
				$grouped[$index][] = [
					'type' => $v['type'],
					'key' => $k,
					'value' => $v
				];
				$last_type = $v['type'];
			} else {
				// if row type is different
				if ($last_type != $v['type']) {
					$index++;
				}
				$grouped[$index][] = [
					'type' => $v['type'],
					'key' => $k,
					'value' => $v
				];
				$last_type = $v['type'];
			}
		}
		// rendering
		foreach ($grouped as $k => $v) {
			$first = current($v);
			$result['data']['html'].= $this->{'render_row_' . $first['type']}($v);
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Rander table rows
	 *
	 * @param array $rows
	 * @return string
	 */
	public function render_row_grid($rows) {
		$data = [
			'options' => []
		];
		foreach ($rows as $k => $v) {
			$index = 0;
			array_key_sort($v['value']['elements'], ['order' => SORT_ASC]);
			// group by
			$groupped = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
			}
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				if (!empty($first['options']['element_vertical_separator'])) {
					$data['options'][$k][$k2][0] = [
						// todo add separator element
						'value' => '&nbsp;',
						'separator' => true
					];
				} else {
					$first['prepend_to_field'] = ':';
					foreach ($v2 as $k3 => $v3) {
						$data['options'][$k][$k2][$k3] = [
							'label' => $this->render_element_name($first),
							'value' => $this->render_element_value($v3),
							'description' => null,
							'error' => [],
							'options' => $v3['options']
						];
					}
				}
			}
		}
		return html::grid($data);
	}

	/**
	 * Rander table rows
	 *
	 * @param array $rows
	 * @return type
	 */
	public function render_row_table($rows) {
		$data = [
			'header' => [],
			'options' => [],
			'skip_header' => true
		];
		foreach ($rows as $k => $v) {
			$index = 0;
			array_key_sort($v['value']['elements'], ['order' => SORT_ASC]);
			// group by
			$groupped = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
			}
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				if (!empty($first['options']['element_vertical_separator'])) {
					$data['options'][$k][0] = [
						// todo: add custom html and icon
						'value' => '&nbsp;',
						'colspan' => count($data['header'])
					];
				} else {
					$elements = [];
					foreach ($v2 as $k3 => $v3) {
						$elements[] = $this->render_element_value($v3, null);
					}
					$first['prepend_to_field'] = ':';
					$data['options'][$k][$index] = [
						'value' => $this->render_element_name($first),
						'width' => '1%',
						'nowrap' => 'nowrap'
					];
					$data['header'][$index] = $index;
					$index++;
					$data['options'][$k][$index] = implode(' ', $elements);
					$data['header'][$index] = $index;
					$index++;
				}
			}
		}
		return html::table($data);
	}

	/**
	 * Render elements name
	 *
	 * @param array $options
	 * @return string
	 */
	public function render_element_name($options) {
		if (isset($options['options']['label_name']) || isset($options['options']['label_i18n'])) {
			$value = i18n($options['options']['label_i18n'] ?? null, $options['options']['label_name']);
			$prepend = isset($options['prepend_to_field']) ? $options['prepend_to_field'] : null;
			// todo: preset for attribute label_for = id
			$label_options = array_key_extract_by_prefix($options['options'], 'label_');
			// prepending mandatory string
			if (!empty($options['options']['element_mandatory'])) {
				if ($options['options']['element_mandatory'] === true) {
					$options['options']['element_mandatory'] = 'mandatory';
				}
				$value = html::mandatory([
					'type' => $options['options']['element_mandatory'],
					'value' => $value,
					'prepend' => $prepend
				]);
			} else {
				$value.= $prepend;
			}
			$label_options['value'] = $value;
			return html::label($label_options);
		}
	}

	/**
	 * Render elements value
	 *
	 * @param array $options
	 * @param mixed $value
	 * @return string
	 * @throws Exception
	 */
	public function render_element_value($options, $value = null) {
		$result_options = $options['options'];
		$element_options = array_key_extract_by_prefix($result_options, 'element_');
		array_key_extract_by_prefix($result_options, 'label_');
		$element_expand = !empty($element_options['expand']);
		// unset certain keys
		unset($result_options['order']);

		// if we are in html mode
		/*
		if ($options['fm_container_mode'] == 'html' && $element_method != 'html::a') {
			if (empty($options['flag_multiple_fields'])) {
				$element_method = 'html::div';
			} else {
				$element_method = 'html::span';
			}
		}
		*/
		// processing options
		if (isset($element_options['options']) && is_array($element_options['options'])) {
			$result_options['options'] = $element_options['options'];
		} else if (!empty($element_options['options'])) {
			$result_options['options'] = html::process_options($element_options['options'], $this);
		}
		// different handling for different type
		switch ($options['type']) {
			case 'container';
				$options_container = $options;
				//$options_container['previous_data'] = $v;
				// todo: pass $form_data_key from parent
				$options_container['previous_key'] = $options['previous_key'];
				// render container
				$temp_container_value = $this->render_container($data['fm_part_child_container_name'], $parents, $options_container);
				if (!empty($html_expand)) {
					// get part id
					$temp_id = $this->id('part_details', [
						'part_name' => $data['fm_part_name'],
						// todo pass $k2 from parent
						'part_id' => $options_container['previous_id']
					]);
					$temp_id_div_inner = $temp_id . '_html_expand_div_inner';
					$temp_expand_div_inner = [
						'id' => $temp_id_div_inner,
						'style' => 'display: none;',
						'value' => $temp_container_value
					];
					$temp_expand_div_a = [
						'href' => 'javascript:void(0);',
						'onclick' => "numbers.element.toggle('{$temp_id_div_inner}');",
						'value' => '+ / -'
					];
					$temp_expand_div_outer = [
						'align' => 'left',
						'value' => html::a($temp_expand_div_a) . '<br />' . html::div($temp_expand_div_inner)
					];
					$value = html::div($temp_expand_div_outer);
				} else {
					$value = $temp_container_value;
				}
				$result_options['value'] = $value;
				break;
			case 'field':
				$element_method = $element_options['method'] ?? 'html::input';
				if (strpos($element_method, '::') === false) {
					$element_method = 'html::' . $element_method;
				}
				// value in special order
				$flag_translated = false;
				if ($element_method == 'html::a') {
					$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value']);
					$flag_translated = true;
				} else {
					$result_options['value'] = $value;
				}
				// todo: processing readonly modes
				/*
				if ($options['fm_container_mode'] == 'readonly') {
					$result_options['readonly'] = 'readonly';
				} else if ($options['fm_container_mode'] == 'html' || $data['fm_part_type'] == 'html') {
					// special processing for html types
					if (!empty($result_options['options'])) {
						$result_options['value'] = html::render_value_from_options($result_options['value'], $result_options['options']);
						$flag_translated = true;
					} else if (!$flag_translated && !is_numeric($result_options['value'])) {
						$result_options['value'] = i18n(null, $result_options['value']);
						$flag_translated = true;
					}
				}
				*/
				break;
			case 'html':
				$element_method = null;
				$result_options['value'] = $value;
				break;
			default:
				Throw new Exception('Render detail type: ' . $data['fm_part_type']);
		}
		// handling html_method
		if (isset($element_method)) {
			$temp = explode('::', $element_method);
			if (count($temp) > 1) {
				$temp_model = $temp[0];
				$temp_method = $temp[1];
			} else {
				$temp_model = 'html';
				$temp_method = $temp[0];
			}
			$field_method_object = new $temp_model();
			return $field_method_object->{$temp_method}($result_options);
		} else {
			return $value;
		}
	}
}