<?php

class numbers_frontend_html_form_base extends numbers_frontend_html_form_wrapper_parent {

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
	 * Fields
	 *
	 * @var array 
	 */
	public $fields = [];

	/**
	 * Values
	 *
	 * @var array
	 */
	public $values = [];

	/**
	 * Collection, model or array
	 *
	 * @var mixed
	 */
	public $collection;

	/**
	 * Collection object
	 *
	 * @var object
	 */
	public $collection_object;

	/**
	 * Optional fields settings
	 *
	 * @var array
	 */
	public $optional_fields;

	/**
	 * Error messages
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * Wrapper methods
	 *
	 * @var array 
	 */
	public $wrapper_methods = [];

	/**
	 * Which elements submit the form
	 *
	 * @var array
	 */
	public $process_submit = [];

	/**
	 * Actions
	 *
	 * @var array
	 */
	public $actions = [];

	/**
	 * Indicator that values has been loaded
	 *
	 * @var boolean
	 */
	public $values_loaded = false;

	/**
	 * Optimistic Lock
	 *
	 * @var array
	 *		column
	 *		value
	 */
	public $optimistic_lock;

	/**
	 * Primary key
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * Current tab
	 *
	 * @var string
	 */
	public $current_tab;

	/**
	 * Constructor
	 *
	 * @param string $form_link
	 * @param array $options
	 */
	public function __construct($form_link, $options = []) {
		$this->form_link = $form_link . '';
		$this->options = $options;
		$this->errors['flag_error_in_fields'] = false;
	}

	/**
	 * Process from events
	 */
	public function process() {
		// we need to see if we have optional fields
		if (!empty($this->optional_fields)) {
			// add it to collections
			$this->optional_fields['type'] = '1M';
			$this->collection['details'][$this->optional_fields['model']] = $this->optional_fields;
			// we need to manually put values into values
			$this->values[$this->optional_fields['model']] = $this->options['input'][$this->optional_fields['model']] ?? [];
			pk(['em_entopt_field_code'], $this->values[$this->optional_fields['model']]);
			unset($this->values[$this->optional_fields['model']]['']);
		}
		// we need to see if form has been submitted
		$submitted = false;
		foreach ($this->process_submit as $k => $v) {
			if (!empty($this->options['input'][$k])) {
				$submitted = true;
				$this->process_submit[$k] = true;
			}
		}
		// __form_values_loaded
		if (!empty($this->options['input']['__form_values_loaded'])) {
			$this->values_loaded = true;
		}
		// process optimistic lock
		if ($this->preload_collection_object() && $this->collection_object->primary_model->optimistic_lock) {
			$this->optimistic_lock = [
				'column' => $this->collection_object->primary_model->optimistic_lock_column,
				'value' => $this->options['input'][$this->collection_object->primary_model->optimistic_lock_column] ?? null,
			];
			$this->values[$this->collection_object->primary_model->optimistic_lock_column] = $this->optimistic_lock['value'] . '';
		}
		// if form has been submitted but not for save
		if (!empty($this->options['input']['__form_submitted']) && !$submitted) {
			// nothing for now
		} else if ($submitted) { // if form has been submitted
			$this->validate_data_types();
			// call attached method to the form
			if (method_exists($this, 'validate')) {
				$this->validate($this);
			} else if (!empty($this->wrapper_methods['validate'])) {
				foreach ($this->wrapper_methods['validate'] as $k => $v) {
					call_user_func_array($v, [& $this]);
				}
			}
			$this->validate_required();
			// optional fields
			if (!empty($this->optional_fields)) {
				$optional_wrapper_object = new numbers_frontend_html_form_wrapper_optional();
				$optional_wrapper_object->validate($this);
			}
			// important to do field conversion last
			$this->process_multiple_columns();
			// adding general error
			if ($this->errors['flag_error_in_fields']) {
				$this->errors['general']['danger'][] = i18n(null, 'There was some errors with your submission!');
			}
			// if we have no error we proceed to saving
			if (empty($this->errors['general']['danger'])) {
				if (method_exists($this, 'save')) {
					$this->save($this);
				} else if (!empty($this->wrapper_methods['save'])) {
					foreach ($this->wrapper_methods['save'] as $k => $v) {
						call_user_func_array($v, [& $this]);
					}
				} else {
					// native save based on collection
					if ($this->save_values() || empty($this->errors['general']['danger'])) {
						// we need to redirect for certain buttons
						$mvc = application::get('mvc');
						// save and new
						if (!empty($this->process_submit[self::BUTTON_SUBMIT_SAVE_AND_NEW])) {
							request::redirect($mvc['full']);
						}
						// save and close
						if (!empty($this->process_submit[self::BUTTON_SUBMIT_SAVE_AND_CLOSE])) {
							request::redirect($mvc['controller'] . '/_index');
						}
					}
					// we reload form values
					goto load_values;
				}
			}
		} else {
load_values:
			// if not submitted we try to load data from database
			$temp = $this->load_values();
			if (!empty($temp)) {
				// we need to convert details columns
				foreach ($this->fields as $k => $v) {
					if (!empty($v['options']['details_column']) && !empty($temp[$k])) {
						$data = $temp[$k];
						$temp[$k] = [];
						foreach ($data as $k2 => $v2) {
							$temp[$k][] = $v2[$v['options']['details_column']];
						}
					}
				}
				$this->values = $temp;
				$this->values_loaded = true;
				// update optimistic lock
				if (!empty($this->optimistic_lock)) {
					$this->optimistic_lock['value'] = $this->values[$this->optimistic_lock['column']];
				}
			}
		}
		// we need to hide buttons
		foreach ($this->data as $k => $v) {
			if (empty($v['rows'])) {
				continue;
			}
			foreach ($v['rows'] as $k2 => $v2) {
				if ($k2 == self::BUTTONS) {
					// remove delete buttons if we do not have loaded values or do not have permission
					$record_delete = object_controller::can('record_delete');
					if (!$this->values_loaded || !$record_delete) {
						unset($this->data[$k]['rows'][$k2]['elements'][self::BUTTON_SUBMIT_DELETE]);
					}
					// we need to check permissions
					$show_save_buttons = false;
					if (object_controller::can('record_new') && !$this->values_loaded) {
						$show_save_buttons = true;
					}
					if (object_controller::can('record_edit') && $this->values_loaded) {
						$show_save_buttons = true;
					}
					if (!$show_save_buttons) {
						unset(
							$this->data[$k]['rows'][$k2]['elements'][self::BUTTON_SUBMIT_SAVE],
							$this->data[$k]['rows'][$k2]['elements'][self::BUTTON_SUBMIT_SAVE_AND_NEW],
							$this->data[$k]['rows'][$k2]['elements'][self::BUTTON_SUBMIT_SAVE_AND_CLOSE]
						);
					}
				}
			}
		}
	}

	/**
	 * Add error to tabs
	 *
	 * @param int $counter
	 */
	public function error_in_tabs($counter, $record = false) {
		if (empty($this->current_tab)) {
			return;
		}
		if (!isset($this->errors['tabs'])) {
			$this->errors['tabs'] = [];
		}
		if (!isset($this->errors['records'])) {
			$this->errors['records'] = [];
		}
		$key = $record ? 'records' : 'tabs';
		$current_value = array_key_get($this->errors[$key], $this->current_tab);
		if (is_null($current_value)) {
			$current_value = 0;
		}
		array_key_set($this->errors[$key], $this->current_tab, $current_value + $counter);
	}

	/**
	 * Process multiple
	 */
	final private function process_multiple_columns() {
		foreach ($this->fields as $k => $v) {
			if (!empty($v['options']['details_column']) && !empty($this->values[$k])) {
				$temp = [];
				foreach ($this->values[$k] as $k2 => $v2) {
					$temp[$v2] = [
						$v['options']['details_column'] => $v2
					];
				}
				$this->values[$k] = $temp;
			}
		}
	}

	/**
	 * Validate datatypes
	 */
	final public function validate_data_types() {
		foreach ($this->fields as $k => $v) {
			if (!empty($v['options']['process_submit'])) {
				continue;
			}
			// process domains first
			if (empty($v['options']['type'])) {
				$v['options']['type'] = 'varchar';
			}
			// if we have multiple values
			if (!empty($v['options']['details_column']) && !empty($this->values[$k])) {
				foreach ($this->values[$k] as $k2 => $v2) {
					$this->validate_data_types_single_value($k, $v, $v2, $k2);
				}
			} else {
				$this->validate_data_types_single_value($k, $v, $this->values[$k]);
			}
		}
	}

	/**
	 * Validate multiple
	 *
	 * @param string $k
	 * @param array $v
	 * @param mixed $in_value
	 * @param boolean $multiple
	 */
	final public function validate_data_types_single_value($k, $v, $in_value, $multiple_key = null, $error_field = null, $do_not_set_values = false) {
		$data = object_table_columns::process_single_column_type($k, $v['options'], $in_value);
		if (array_key_exists($k, $data)) {
			// we set error field as main key
			if (empty($error_field)) {
				$error_field = $k;
			}
			// validations
			$error = false;
			$value = $in_value;
			// perform validation
			if (in_array($v['options']['type'], ['date', 'time', 'datetime'])) { // dates first
				if (!empty($value) && empty($data[$k . '_strtotime_value'])) {
					$this->error('danger', i18n(null, 'Invalid date, time or datetime!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'integer') {
				if (!empty($value) && ($data[$k] == 0 || $value . '' != $data[$k] . '')) {
					$this->error('danger', i18n(null, 'Wrong integer value!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'float') {
				if (!empty($value) && ($data[$k] == 0 || $value . '' != $data[$k] . '')) {
					$this->error('danger', i18n(null, 'Wrong numeric value!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'string') {
				if (!empty($v['options']['length']) && strlen($value) > $v['options']['length']) {
					$this->error('danger', i18n(null, 'String is too long, should be no longer than [length]!', ['replace' => [
						'[length]' => $v['options']['length']
					]]), $error_field);
					$error = true;
				}
			}
			$data['flag_error'] = $error;
			// if no error we update the value
			if (!$error && !$do_not_set_values) {
				if ($multiple_key === null) {
					$this->values[$k] = $data[$k];
				} else {
					$this->values[$k][$multiple_key] = $data[$k];
				}
			}
		} else {
			// unset value
			if ($multiple_key === null) {
				unset($this->values[$k]);
			} else {
				unset($this->values[$k][$multiple_key]);
			}
		}
		return $data;
	}

	/**
	 * Save values to database
	 *
	 * @return boolean
	 */
	final public function save_values() {
		// double check if we have collection object
		if (!$this->preload_collection_object()) {
			Throw new Exception('You must provide collection object!');
		}
		$result = $this->collection_object->merge($this->values, [
			'flag_delete_row' => $this->process_submit['submit_delete'] ?? false,
			'optimistic_lock' => $this->optimistic_lock
		]);
		if (!$result['success']) {
			if (!empty($result['error'])) {
				foreach ($result['error'] as $v) {
					$this->error('danger', i18n(null, $v));
				}
			}
			if (!empty($result['warning'])) {
				foreach ($result['warning'] as $v) {
					$this->error('warning', i18n(null, $v));
				}
			}
		} else {
			if (!empty($result['deleted'])) {
				$this->error('success', i18n(null, 'Record has been successfully deleted!'));
				// we must reset form values
				$this->values = [];
			} else if ($result['inserted']) {
				$this->error('success', i18n(null, 'Record has been successfully created!'));
				// we must set primary key
				if (strpos($this->collection_object->primary_model->columns[$this->collection_object->primary_model->pk[0]]['type'], 'serial') !== false) {
					if (!empty($result['new_pk'])) {
						$this->values[$this->collection_object->primary_model->pk[0]] = $result['new_pk'];
					}
				}
			} else {
				$this->error('success', i18n(null, 'Record has been successfully updated!'));
			}
		}
		return $result['success'];
	}

	/**
	 * Preload collection object
	 *
	 * @return boolean
	 */
	final private function preload_collection_object() {
		if (empty($this->collection_object)) {
			$this->collection_object = object_collection::collection_to_model($this->collection);
			if (empty($this->collection_object)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Load primary key from values
	 */
	final public function load_pk() {
		$this->pk = [];
		foreach ($this->collection_object->data['pk'] as $v) {
			if (isset($this->values[$v])) {
				$temp = object_table_columns::process_single_column_type($v, $this->collection_object->primary_model->columns[$v], $this->values[$v]);
				if (array_key_exists($v, $temp)) {
					$this->pk[$v] = $temp[$v];
				}
			}
		}
		return $this->pk;
	}

	/**
	 * Load values from database
	 *
	 * @return mixed
	 */
	final public function load_values() {
		// load collection object
		if (!$this->preload_collection_object()) {
			return false;
		}
		// load primary key
		$where = $this->load_pk();
		if (!empty($where)) {
			return $this->collection_object->get(['where' => $where, 'single_row' => true]);
		}
		return false;
	}

	/**
	 * Validate required fields
	 *
	 * @return boolean
	 */
	private function validate_required() {
		foreach ($this->fields as $k => $v) {
			// check if its required field
			if (isset($v['options']['required']) && $v['options']['required'] === true) {
				$value = array_key_get($this->values, $v['options']['name']);
				if ($v['options']['php_type'] == 'integer' || $v['options']['php_type'] == 'float') {
					if (empty($value)) {
						$this->error('danger', i18n(null, object_content_messages::$required_field), $k);
					}
				} else {
					if ($value . '' == '') {
						$this->error('danger', i18n(null, object_content_messages::$required_field), $k);
					}
				}
			}
			// validate data type
			// todo: add here
		}
	}

	/**
	 * Add error  to the form
	 *
	 * @param string $type
	 *		muted
	 *		primary
	 *		success
	 *		info
	 *		warning
	 *		danger
	 * @param array $messages
	 * @param mixed $field
	 */
	public function error($type, $messages, $field = null) {
		// convert messages to array
		if (!is_array($messages)) {
			$messages = [$messages];
		}
		// set field error
		if (!empty($field)) {
			if (!isset($this->errors['fields'])) {
				$this->errors['fields'] = [];
			}
			if (!is_array($field)) {
				$key = [$field];
			} else {
				$key = $field;
			}
			$key[] = $type;
			$existing = array_key_get($this->errors['fields'], $key);
			if (!empty($existing)) {
				$existing = array_merge($existing, $messages);
			} else {
				$existing = $messages;
			}
			array_key_set($this->errors['fields'], $key, $existing);
			// set special flag that we have error in fields
			if ($type = 'danger') {
				$this->errors['flag_error_in_fields'] = true;
			}
		} else {
			if (!isset($this->errors['general'][$type])) {
				$this->errors['general'][$type] = [];
			}
			$this->errors['general'][$type] = array_merge($this->errors['general'][$type], $messages);
		}
	}

	/**
	 * Add container to the form
	 *
	 * @param string $container_link
	 * @param array $options
	 */
	public function container($container_link, $options = []) {
		if (!isset($this->data[$container_link])) {
			$type = $options['type'] ?? 'fields';
			$this->data[$container_link] = [
				'rows' => [],
				'options' => $options,
				'order' => $options['order'] ?? 0,
				'type' => $type,
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
			$types = object_html_form_row_type::get_static();
			if (!isset($options['type']) || !isset($types[$options['type']])) {
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
	 * @param string $element_link
	 * @param array $options
	 */
	public function element($container_link, $row_link, $element_link, $options = []) {
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
		if (!isset($this->data[$container_link]['rows'][$row_link]['elements'][$element_link])) {
			if (!empty($options['container'])) {
				$this->data[$options['container']]['flag_child'] = true;
				$type = 'tab';
				$container = $options['container'];
			} else {
				// name & id
				$options['name'] = $element_link;
				$options['id'] = 'form_' . $this->form_link . '_' . $element_link;
				// todo: add parent key here
				// populate value array but not for buttons
				if (empty($options['process_submit'])) {
					$value = array_key_get($this->options['input'], $element_link);
					$this->values[$element_link] = $value;
					// process domain & type
					$temp = object_data_common::process_domains(['options' => $options]);
					$options = $temp['options'];
				}
				// put data into fields array
				$field = [
					'id' => $options['id'],
					'name' => $options['name'],
					'options' => $options
				];
				array_key_set($this->fields, $element_link, $field);
				// type is field by default
				$type = 'field';
				$container = null;
				// process submit elements
				if (!empty($options['process_submit'])) {
					$this->process_submit[$element_link] = false;
				}
			}
			// setting data
			$this->data[$container_link]['rows'][$row_link]['elements'][$element_link] = [
				'type' => $type,
				'container' => $container,
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
		} else {
			$this->data[$container_link]['rows'][$row_link]['elements'][$element_link]['options'] = array_merge_hard($this->data[$container_link]['rows'][$row_link]['elements'][$element_link], $options);
		}
	}

	/**
	 * Render form
	 *
	 * @return mixed
	 */
	public function render() {
		// add actions
		// new record
		$mvc = application::get('mvc');
		if (object_controller::can('record_new')) {
			$this->actions['form_new'] = ['value' => 'New', 'sort' => -31000, 'icon' => 'file-o', 'href' => $mvc['full']];
		}
		// back to list
		if (object_controller::can('list_view')) {
			$this->actions['form_back'] = ['value' => 'Back', 'sort' => -32000, 'icon' => 'arrow-left', 'href' => $mvc['controller'] . '/_index'];
		}
		// reload button
		if ($this->values_loaded) {
			$pk = $this->load_pk();
			$url = $mvc['full'] . '?' . http_build_query2($pk);
			$this->actions['form_refresh'] = ['value' => 'Refresh', 'sort' => -30000, 'icon' => 'refresh', 'href' => $url];
		}
		// assembling everything into result variable
		$result = [];
		// order containers based on order column
		array_key_sort($this->data, ['order' => SORT_ASC]);
		foreach ($this->data as $k => $v) {
			if (!$v['flag_child']) {
				if ($v['type'] == 'fields') {
					$this->current_tab = null;
					$temp = $this->render_container($k);
					if ($temp['success']) {
						$result[$k] = $temp['data'];
					}
				} else if ($v['type'] == 'tabs') {
					$tab_header = [];
					$tab_values = [];
					// sort rows
					array_key_sort($v['rows'], ['order' => SORT_ASC]);
					foreach ($v['rows'] as $k2 => $v2) {
						$this->current_tab = 'form_tabs_' . $this->form_link . '_' . $k . '_' . $k2;
						$labels = '';
						$labels.= html::label2(['type' => 'primary', 'style' => 'display: none;', 'value' => 0, 'id' => $this->current_tab . '_record']);
						$labels.= html::label2(['type' => 'danger', 'style' => 'display: none;', 'value' => 0, 'id' => $this->current_tab . '_error']);
						$tab_header[$k2] = i18n(null, $v2['options']['label_name']) . $labels;
						$tab_values[$k2] = '';
						array_key_sort($v2['elements'], ['order' => SORT_ASC]);
						foreach ($v2['elements'] as $k3 => $v3) {
							$temp = $this->render_container($v3['options']['container']);
							if ($temp['success']) {
								$tab_values[$k2].= $temp['data']['html'];
							}
						}
					}
					$result[$k]['html'] = html::tabs([
						'id' => $k,
						'header' => $tab_header,
						'options' => $tab_values
					]) . '<br/>';
				}
			}
		}
		// formatting data
		$temp = [];
		foreach ($result as $k => $v) {
			$temp[] = $v['html'];
		}
		$result = implode('', $temp);
		// rendering actions
		if (!empty($this->actions)) {
			$value = '<div style="text-align: right;">' . $this->render_actions() . '</div>';
			$value.= '<hr class="simple" />';
			$result = $value . $result;
		}
		// messages
		if (!empty($this->errors['general'])) {
			$messages = '';
			foreach ($this->errors['general'] as $k => $v) {
				$messages.= html::message(['options' => $v, 'type' => $k]);
			}
			$result = $messages . $result;
		}
		// couple hidden fields
		$result.= html::hidden(['name' => '__form_submitted', 'value' => 1]);
		$result.= html::hidden(['name' => '__form_values_loaded', 'value' => $this->values_loaded]);
		if (!empty($this->optimistic_lock)) {
			$result.= html::hidden(['name' => $this->optimistic_lock['column'], 'value' => $this->optimistic_lock['value']]);
		}
		// js
		if (!empty($this->errors['tabs'])) {
			foreach ($this->errors['tabs'] as $k => $v) {
				layout::onload("$('#{$k}_error').html($v); $('#{$k}_error').show();");
			}
		}
		if (!empty($this->errors['records'])) {
			foreach ($this->errors['records'] as $k => $v) {
				layout::onload("$('#{$k}_record').html($v); $('#{$k}_record').show();");
			}
		}
		// if we have form
		if (empty($this->options['skip_form'])) {
			$result = html::form([
				'name' => "form_{$this->form_link}_form",
				'id' => "form_{$this->form_link}_form",
				'value' => $result,
				//'onsubmit' => 'return numbers.frontend_list.submit(this);'
			]);
		}
		// if we have segment
		if (isset($this->options['segment'])) {
			$temp = is_array($this->options['segment']) ? $this->options['segment'] : [];
			$temp['value'] = $result;
			$result = html::segment($temp);
		}
		return $result;
	}

	/**
	 * Render actions
	 *
	 * @return string
	 */
	private function render_actions() {
		// sorting first
		array_key_sort($this->actions, ['sort' => SORT_ASC], ['sort' => SORT_NUMERIC]);
		// looping through data and building html
		$temp = [];
		foreach ($this->actions as $k => $v) {
			$icon = !empty($v['icon']) ? (html::icon(['type' => $v['icon']]) . ' ') : '';
			$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
			$value = !empty($v['value']) ? i18n(null, $v['value']) : '';
			$href = $v['href'] ?? 'javascript:void(0);';
			$temp[] = html::a(array('value' => $icon . $value, 'href' => $href, 'onclick' => $onclick));
		}
		return implode(' ', $temp);
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
		// custom renderer
		if (!empty($this->data[$container_link]['options']['custom_renderer'])) {
			$temp = explode('::', $this->data[$container_link]['options']['custom_renderer']);
			$temp[0] = factory::model($temp[0]);
			return call_user_func_array($temp, [& $this]);
		}
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
			// processing buttons
			if ($v['key'] == '__submit_buttons') {
				$buttons = [];
				foreach ($v['value']['elements'] as $k2 => $v2) {
					$button_group = $v2['options']['button_group'] ?? 'left';
					if (!isset($buttons[$button_group])) {
						$buttons[$button_group] = [];
					}
					$buttons[$button_group][] = $this->render_element_value($v2);
				}
				// render button groups
				foreach ($buttons as $k2 => $v2) {
					$value = implode(' ', $v2);
					if ($k2 != 'left') {
						$value = '<div style="text-align: ' . $k2 . ';">' . $value . '</div>';
					}
					$data['options'][$k]['__submit_buttons'][$k2] = [
						'label' => null,
						'value' => $value,
						'description' => null,
						'error' => [],
						'options' => []
					];
				}
				continue;
			}
			// group by
			$groupped = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
			}
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				$first_key = key($v2);
				if ($first_key == self::SEPARATOR_HORISONTAL) {
					$data['options'][$k][$k2][0] = [
						'value' => html::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
						'separator' => true
					];
				} else {
					$first['prepend_to_field'] = ':';
					foreach ($v2 as $k3 => $v3) {
						$data['options'][$k][$k2][$k3] = [
							'error' => $this->get_field_errors($v3),
							'label' => $this->render_element_name($first),
							'value' => $this->render_element_value($v3, $this->get_field_value($v3)),
							'description' => null,
							'options' => $v3['options']
						];
					}
				}
			}
		}
		return html::grid($data);
	}

	/**
	 * Get field errors
	 *
	 * @param array $field
	 * @return mixed
	 */
	public function get_field_errors($field) {
		$existing = array_key_get($this->errors['fields'], $field['options']['name']);
		if (!empty($existing)) {
			$result = [
				'type' => null,
				'message' => '',
				'counter' => 0
			];
			$types = array_keys($existing);
			if (in_array('danger', $types)) {
				$result['type'] = 'danger';
			} else {
				$temp = current($types);
				$result['type'] = $temp;
			}
			// generating text messages
			foreach ($existing as $k => $v) {
				foreach ($v as $k2 => $v2) {
					if ($k == 'danger') {
						$result['counter']+= 1;
					}
					$result['message'].= html::text(['tag' => 'div', 'type' => $k, 'value' => $v2]);
				}
			}
			return $result;
		}
		return null;
	}

	/**
	 * Get field value
	 *
	 * @param array $field
	 * @return mixed
	 */
	private function get_field_value($field) {
		if (empty($field['options']['empty_value']) && !isset($field['options']['value'])) {
			$value = array_key_get($this->values, $field['options']['name']);
			if ($field['options']['php_type'] == 'integer' && empty($value)) {
				$value = '';
			}
			return $value;
		}
		return null;
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
						$elements[] = $this->render_element_value($v3, $this->get_field_value($v3));
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
			if (!empty($options['options']['required'])) {
				if ($options['options']['required'] === true) {
					$options['options']['required'] = 'mandatory';
				} else if ($options['options']['required'] == 'c') {
					$options['options']['required'] = 'conditional';
				}
				$value = html::mandatory([
					'type' => $options['options']['required'],
					'value' => $value,
					'prepend' => $prepend
				]);
			} else {
				$value.= $prepend;
			}
			$label_options['value'] = $value;
			$label_options['class'] = 'control-label';
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
		array_key_extract_by_prefix($result_options, 'label_');
		$element_expand = !empty($result_options['expand']);
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
		if (!empty($result_options['options_model'])) {
			$result_options['options'] = object_data_common::process_options($result_options['options_model'], $this);
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
				$element_method = $result_options['method'] ?? 'html::input';
				if (strpos($element_method, '::') === false) {
					$element_method = 'html::' . $element_method;
				}
				// value in special order
				$flag_translated = false;
				if (in_array($element_method, ['html::a', 'html::submit', 'html::button', 'html::button2'])) {
					// translate value
					$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value']);
					// process confirm_message
					$result_options['onclick'] = $result_options['onclick'] ?? '';
					if (!empty($result_options['confirm_message'])) {
						$result_options['onclick'].= 'return confirm(\'' . strip_tags(i18n(null, $result_options['confirm_message'])) . '\');';
					}
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
			// adding value
			$field_method_object = new $temp_model();
			return $field_method_object->{$temp_method}($result_options);
		} else {
			return $value;
		}
	}
}