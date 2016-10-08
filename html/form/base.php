<?php

class numbers_frontend_html_form_base extends numbers_frontend_html_form_wrapper_parent {

	/**
	 * Form link
	 *
	 * @var string
	 */
	public $form_link;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Form class
	 *
	 * @var string
	 */
	public $form_class;

	/**
	 * Form parent
	 *
	 * @var string
	 */
	public $form_parent;

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
	 * Feilds for details
	 *
	 * @var array
	 */
	public $detail_fields = [];

	/**
	 * Values
	 *
	 * @var array
	 */
	public $values = [];

	/**
	 * Original values
	 *
	 * @var array
	 */
	public $original_values = [];

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
	public $current_tab = [];

	/**
	 * If we are making an ajax call to another form
	 *
	 * @var boolean
	 */
	private $flag_another_ajax_call = false;

	/**
	 * Cached domains
	 *
	 * @var array
	 */
	public static $cached_domains = null;

	/**
	 * Misc. Settings
	 *
	 * @var array
	 */
	public $misc_settings = [];

	/**
	 * Whether we have attributes, set automatically when we have __attributes key in tabs and primary collection model has attributes flag set
	 *
	 * @var boolean
	 */
	public $attributes;

	/**
	 * Constructor
	 *
	 * @param string $form_link
	 * @param array $options
	 */
	public function __construct($form_link, $options = []) {
		$this->form_link = $form_link . '';
		$this->options = $options;
		// overrides from ini files
		$overrides = application::get('flag.numbers.frontend.html.form');
		if (!empty($overrides)) {
			$this->options = array_merge_hard($this->options, $overrides);
		}
		$this->errors['flag_error_in_fields'] = false;
		// actions
		if (!empty($this->options['actions'])) {
			$this->actions = array_merge($this->actions, $this->options['actions']);
		}
	}

	/**
	 * Process from events
	 */
	public function process() {
		// ajax requests from other forms are filtered by id
		if (!empty($this->options['input']['__ajax'])) {
			// if its ajax call to this form
			if (($this->options['input']['__ajax_form_id'] ?? '') == "form_{$this->form_link}_form") {
				// it its a call to auto complete
				if ($this->attributes && !empty($this->options['input']['__ajax_autocomplete']['rn_attrattr_id'])) {
					return factory::model('numbers_data_relations_model_attribute_form', true)->autocomplete($this, $this->options['input']);
				} else if (!empty($this->options['input']['__ajax_autocomplete']['name'])
					&& !empty($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'])
					&& strpos($this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method'], 'autocomplete') !== false
				) {
					$options = $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options'];
					$options['__ajax'] = true;
					$options['__ajax_autocomplete'] = $this->options['input']['__ajax_autocomplete'];
					$temp = explode('::', $this->fields[$this->options['input']['__ajax_autocomplete']['name']]['options']['method']);
					if (count($temp) == 1) {
						return html::{$temp[0]}($options);
					} else {
						return factory::model($temp[0])->{$temp[1]}($options);
					}
				}
			} else {
				// load pk
				if ($this->preload_collection_object()) {
					$this->load_pk();
					// we need to set this flag so ajax calls can go through
					$this->values_loaded = true;
				}
				$this->flag_another_ajax_call = true;
				return;
			}
		}
		// navigation
		if (!empty($this->options['input']['navigation'])) {
			$column = key($this->options['input']['navigation']);
			do {
				if (empty($this->fields[$column]['options']['navigation'])) break;
				$navigation_type = key($this->options['input']['navigation'][$column]);
				if (empty($this->options['input'][$column]) && in_array($navigation_type, ['next', 'previous', 'refresh'])) break;
				$this->preload_collection_object();
				$temp = object_table_columns::process_single_column_type($column, $this->collection_object->primary_model->columns[$column], $this->options['input'][$column] ?? null);
				$where = [];
				if (!array_key_exists($column, $temp) && in_array($navigation_type, ['next', 'previous', 'refresh'])) {
					break;
				} else {
					$where[$column] = $temp[$column];
				}
				$model = new numbers_frontend_html_form_model_datasource_navigation();
				$result = $model->get([
					'model' => $this->collection['model'],
					'type' => $navigation_type,
					'column' => $column,
					'pk' => $this->collection_object->data['pk'][0],
					'where' => $where
				]);
				// we need to reset input and values
				$this->options['input'] = $this->values = [];
				if (!empty($result[0])) {
					$this->values = $result[0];
					if (!isset($this->values[$column])) {
						$this->values[$column] = $temp[$column];
					}
				} else {
					$this->values[$column] = $temp[$column];
					if ($navigation_type == 'refresh') {
						$this->error('danger', i18n(null, 'Invalid value!'), $column);
					} else {
						$this->error('danger', i18n(null, 'Could not find any values!'), $column);
					}
					goto process_errors;
				}
			} while(0);
		}
		// we need to see if we have optional fields
		if ($this->attributes) {
			$this->values[$this->misc_settings['attributes']['values_model']] = $this->options['input'][$this->misc_settings['attributes']['values_model']] ?? [];
		}
		// we need to process details
		if (!empty($this->detail_fields)) {
			foreach ($this->detail_fields as $k => $v) {
				$this->values[$k] = $this->options['input'][$k] ?? [];
				// we need to determine how to process details
				// important!!! if we can not process details - all subdetails will be lost
				$intersect = array_intersect($v['options']['details_pk'], array_keys($v['elements']));
				if (count($v['options']['details_pk']) == 1 && empty($intersect)) { // auto incremented integer
					// determine key
					$pk_one = current($v['options']['details_pk']);
					// find all must have columns
					$must_haves = [];
					foreach ($v['elements'] as $k2 => $v2) {
						if (!empty($v2['options']['details_must_have_column'])) {
							$must_haves[$k2] = $k2;
						}
					}
					$counter = 1;
					$temp = [];
					foreach ($this->values[$k] as $v2) {
						$found = false;
						foreach ($must_haves as $v3) {
							if (!empty($v2[$v3])) {
								$found = true;
								break;
							}
						}
						if ($found) {
							$temp[$counter] = $v2;
							$temp[$counter][$pk_one] = $counter;
							$counter++;
						}
					}
					$this->values[$k] = $temp;
				} else { // keys are present in the input or multi key
					pk($v['options']['details_pk'], $this->values[$k]);
					unset($this->values[$k]['']);
				}
				// process subdetails
				if (!empty($v['subdetails'])) {
					foreach ($v['subdetails'] as $k0 => $v0) {
						foreach ($this->values[$k] as $k1 => $v1) {
							// make an empty array
							if (empty($v1[$k0])) {
								$this->values[$k][$k1][$k0] = [];
								continue;
							}
							// convert keys
							$intersect = array_intersect($v0['options']['details_pk'], array_keys($v0['elements']));
							if (count($v0['options']['details_pk']) == 1 && empty($intersect)) { // auto incremented integer
								// determine key
								$pk_one = current($v0['options']['details_pk']);
								// find all must have columns
								$must_haves = [];
								foreach ($v0['elements'] as $k2 => $v2) {
									if (!empty($v2['options']['details_must_have_column'])) {
										$must_haves[$k2] = $k2;
									}
								}
								$counter = 1;
								$temp = [];
								foreach ($this->values[$k][$k1][$k0] as $v2) {
									$found = false;
									foreach ($must_haves as $v3) {
										if (!empty($v2[$v3])) {
											$found = true;
											break;
										}
									}
									if ($found) {
										$temp[$counter] = $v2;
										$temp[$counter][$pk_one] = $counter;
										$counter++;
									}
								}
								$this->values[$k][$k1][$k0] = $temp;
							} else { // keys are present in the input or multi key
								pk($v0['options']['details_pk'], $this->values[$k][$k1][$k0]);
								unset($this->values[$k][$k1][$k0]['']);
							}
						}
					}
				}
			}
		}
		// we need to see if form has been submitted
		$submitted = false;
		foreach ($this->process_submit as $k => $v) {
			if (!empty($this->options['input'][$k])) {
				$submitted = true;
				$this->process_submit[$k] = true;
			}
		}
		// reset form
		if (!empty($this->options['input']['submit_hidden_reset'])) {
			$this->values = [];
		}
		// if we submit thought ajax we pass this variable
		if (!empty($this->options['input']['submit_hidden_submit'])) {
			$submitted = true;
			$this->process_submit[self::BUTTON_SUBMIT] = true;
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
		// handling form reload
		if (!empty($this->wrapper_methods['refresh']['main'])) {
			call_user_func_array($this->wrapper_methods['refresh']['main'], [& $this]);
		}
		// if form has been submitted but not for save
		if (!empty($this->options['input']['__form_submitted']) && !$submitted) {
			// nothing
		} else if ($submitted) { // if form has been submitted
			// validate data types and options values
			$this->validate_data_types();
			// validate required fields
			$this->validate_required();
			// call attached method to the form
			if (method_exists($this, 'validate')) {
				$this->validate($this);
			} else if (!empty($this->wrapper_methods['validate'])) {
				foreach ($this->wrapper_methods['validate'] as $k => $v) {
					call_user_func_array($v, [& $this]);
				}
			}
			// validate attributes
			if ($this->attributes) {
				factory::model('numbers_data_relations_model_attribute_form', true)->validate($this);
			}
			// important to do field conversion last
			$this->process_multiple_columns();
			// adding general error
process_errors:
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
						// we reload form values
						goto load_values;
					} else {
						goto convert_multiple_columns;
					}
				}
				// assuming save has been executed without errors we need to process on_success_js
				if (empty($this->errors['general']['danger'])) {
					if (!empty($this->options['on_success_js'])) {
						layout::onload($this->options['on_success_js']);
					}
				}
			} else {
convert_multiple_columns:
				// we need to convert details columns
				foreach ($this->fields as $k => $v) {
					if (!empty($v['options']['multiple_column']) && !empty($this->values[$k])) {
						$this->values[$k] = array_keys($this->values[$k]);
					}
				}
			}
		} else {
load_values:
			// if not submitted we try to load data from database
			$temp = $this->load_values();
			if (!empty($temp)) {
				// we need to convert details columns
				foreach ($this->fields as $k => $v) {
					if (!empty($v['options']['multiple_column']) && !empty($temp[$k])) {
						$data = $temp[$k];
						$temp[$k] = [];
						foreach ($data as $k2 => $v2) {
							$temp[$k][] = $v2[$v['options']['multiple_column']];
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
					$temp_pk = $this->load_pk();
					if (!$this->values_loaded || !$record_delete || empty($temp_pk)) {
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
		$type = $record ? 'records' : 'tabs';
		// we need to process errors in a special way
		$current_tab = $this->current_tab;
		do {
			$key = implode('__', $current_tab);
			$current_value = array_key_get($this->errors[$type], $key);
			if (is_null($current_value)) {
				$current_value = 0;
			}
			array_key_set($this->errors[$type], $key, $current_value + $counter);
			array_pop($current_tab);
		} while (count($current_tab) > 0 && $type != 'records');
	}

	/**
	 * Process multiple
	 */
	final private function process_multiple_columns() {
		foreach ($this->fields as $k => $v) {
			if (!empty($v['options']['multiple_column']) && !empty($this->values[$k])) {
				$temp = [];
				foreach ($this->values[$k] as $k2 => $v2) {
					$temp[$v2] = [
						$v['options']['multiple_column'] => $v2
					];
				}
				$this->values[$k] = $temp;
			}
		}
	}

	/**
	 * Validate datatypes
	 */
	final public function validate_data_types($override_values = false) {
		$this->misc_settings['options_model'] = [];
		// regular & multiple fields & single details
		foreach ($this->fields as $k => $v) {
			// skip submit buttons
			if (!empty($v['options']['process_submit'])) continue;
			if ($k == '__separator_horizontal') continue;
			// default type is varchar
			if (empty($v['options']['type'])) {
				$v['options']['type'] = 'varchar';
			}
			// if we have multiple values
			if (!empty($v['options']['multiple_column'])) {
				if (!empty($this->values[$k])) {
					foreach ($this->values[$k] as $k2 => $v2) {
						$this->validate_data_types_single_value($k, $v, $v2, $k2, null, false, $override_values);
					}
				} else {
					$this->values[$k] = [];
				}
			} else if (!empty($v['options']['detail_11'])) { // 1 to 1 details
				$value = array_key_get($this->values, [$v['options']['detail_11'], $v['options']['field_name']]);
				$temp = $this->validate_data_types_single_value($k, $v, $value, null, $v['options']['name'], true, $override_values);
				if (empty($temp['flag_error'])) {
					$this->values[$v['options']['detail_11']][$v['options']['field_name']] = $temp[$k];
				}
			} else {
				$this->validate_data_types_single_value($k, $v, $this->values[$k], null, null, false, $override_values);
			}
			// options_model
			if (!empty($v['options']['options_model']) && empty($v['options']['options_manual_validation'])) {
				$params = $v['options']['options_params'] ?? [];
				if (!empty($v['options']['options_depends'])) {
					foreach ($v['options']['options_depends'] as $k8 => $v8) {
						$params[$k8] = $this->values[$v8] ?? null;
					}
				}
				$this->misc_settings['options_model'][$k] = [
					'options_model' => $v['options']['options_model'],
					'options_params' => $params,
					'key' => !empty($v['options']['detail_11']) ? [$v['options']['detail_11'], $k] : [$k]
				];
			}
		}
		// details
		foreach ($this->detail_fields as $k0 => $v0) {
			// make empty details as array
			if (empty($this->values[$v0['options']['details_key']]) || !is_array($this->values[$v0['options']['details_key']])) {
				$this->values[$v0['options']['details_key']] = [];
			}
			foreach ($this->values[$v0['options']['details_key']] as $k11 => $v11) {
				foreach ($v0['elements'] as $k => $v) {
					// skip submit buttons
					if (!empty($v['options']['process_submit'])) continue;
					// default type is varchar
					if (empty($v['options']['type'])) {
						$v['options']['type'] = 'varchar';
					}
					// validate
					$name = $v0['options']['details_key'] . "[{$k11}][" . ($k) . "]";
					$temp = $this->validate_data_types_single_value($k, $v, $v11[$k] ?? null, true, $name, true, $override_values);
					if (empty($temp['flag_error'])) {
						$this->values[$v0['options']['details_key']][$k11][$k] = $temp[$k];
					}
					// options_model
					if (!empty($v['options']['options_model']) && empty($v['options']['options_manual_validation'])) {
						$params = $v['options']['options_params'] ?? [];
						if (!empty($v['options']['options_depends'])) {
							foreach ($v['options']['options_depends'] as $k8 => $v8) {
								$params[$k8] = $this->values[$v0['options']['details_key']][$k11][$v8] ?? null;
							}
						}
						$this->misc_settings['options_model'][$name] = [
							'options_model' => $v['options']['options_model'],
							'options_params' => $params,
							'key' => [$v0['options']['details_key'], $k11, $k]
						];
					}
				}
				// subdetails
				if (!empty($v0['subdetails'])) {
					foreach ($v0['subdetails'] as $k20 => $v20) {
						foreach ($v11[$k20] as $k21 => $v21) {
							foreach ($v20['elements'] as $k22 => $v22) {
								// skip submit buttons
								if (!empty($v22['options']['process_submit'])) continue;
								// default type is varchar
								if (empty($v22['options']['type'])) {
									$v22['options']['type'] = 'varchar';
								}
								// validate
								$name = "{$v22['options']['details_parent_key']}[{$k11}][{$v22['options']['details_key']}][{$k21}][{$k22}]";
								$temp = $this->validate_data_types_single_value($k22, $v22, $v21[$k22] ?? null, true, $name, true, $override_values);
								if (empty($temp['flag_error'])) {
									$this->values[$v0['options']['details_key']][$k11][$k20][$k21][$k22] = $temp[$k22];
								}
								// options_model
								if (!empty($v22['options']['options_model']) && empty($v22['options']['options_manual_validation'])) {
									$params = $v22['options']['options_params'] ?? [];
									if (!empty($v22['options']['options_depends'])) {
										foreach ($v22['options']['options_depends'] as $k8 => $v8) {
											$params[$k8] = $v21[$v8] ?? null;
										}
									}
									$this->misc_settings['options_model'][$name] = [
										'options_model' => $v22['options']['options_model'],
										'options_params' => $params,
										'key' => [$v22['options']['details_parent_key'], $k11, $v22['options']['details_key'], $k21, $k22]
									];
								}
							}
						}
					}
				}
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
	final public function validate_data_types_single_value($k, $v, $in_value, $multiple_key = null, $error_field = null, $do_not_set_values = false, $override_values = false) {
		// cache domains
		if (empty(self::$cached_domains)) {
			self::$cached_domains = factory::model('object_data_domains')->get();
		}
		// perform validation
		$data = object_table_columns::process_single_column_type($k, $v['options'], $in_value, ['process_datetime' => true]);
		if (array_key_exists($k, $data)) {
			// we set error field as main key
			if (empty($error_field)) {
				$error_field = $k;
			}
			// validations
			$error = false;
			$value = $in_value;
			// perform validation
			if ($v['options']['type'] == 'boolean') {
				if (!empty($value) && ($value . '' != $data[$k] . '')) {
					$this->error('danger', i18n(null, 'Wrong boolean value!'), $error_field);
					$error = true;
				}
			} else if (in_array($v['options']['type'], ['date', 'time', 'datetime', 'timestamp'])) { // dates first
				if (!empty($value) && empty($data[$k . '_strtotime_value'])) {
					$this->error('danger', i18n(null, 'Invalid date, time or datetime!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'integer') {
				if (!empty($value) && ($data[$k] == 0 || $value . '' != $data[$k] . '')) {
					$this->error('danger', i18n(null, 'Wrong integer value!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
				if ($value . '' !== '' && !format::read_floatval($value, ['valid_check' => 1])) {
					$this->error('danger', i18n(null, 'Wrong numeric value!'), $error_field);
					$error = true;
				}
				// precision & scale validations
				if (!$error) {
					// validate scale
					$digits = explode('.', $data[$k] . '');
					if (!empty($v['options']['scale'])) {
						if (!empty($digits[1]) && strlen($digits[1]) > $v['options']['scale']) {
							$this->error('danger', i18n(null, 'Only [digits] fraction digits allowed!', ['replace' => ['[digits]' => $v['options']['scale']]]), $error_field);
							$error = true;
						}
					}
					// validate precision
					if (!empty($v['options']['precision'])) {
						$precision = $v['options']['precision'] - $v['options']['scale'] ?? 0;
						if (strlen($digits[0]) > $precision) {
							$this->error('danger', i18n(null, 'Only [digits] digits allowed!', ['replace' => ['[digits]' => $precision]]), $error_field);
							$error = true;
						}
					}
				}
			} else if ($v['options']['php_type'] == 'float') { // regular floats
				if (!empty($value) && $data[$k] == 0) { //  || $value . '' != $data[$k] . ''
					$this->error('danger', i18n(null, 'Wrong numeric value!'), $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'string') {
				// we need to convert empty string to null
				if ($data[$k] . '' === '' && !empty($v['options']['null'])) {
					$data[$k] = null;
				}
				// validate string length
				if ($data[$k] . '' !== '') {
					// validate length
					if (!empty($v['options']['type']) && $v['options']['type'] == 'char' && strlen($data[$k]) != $v['options']['length']) {  // char
						$this->error('danger', i18n(null, 'The length must be [length] characters!', ['replace' => ['[length]' => $v['options']['length']]]), $error_field);
						$error = true;
					} else if (!empty($v['options']['length']) && strlen($data[$k]) > $v['options']['length']) { // varchar
						$this->error('danger', i18n(null, 'String is too long, should be no longer than [length]!', ['replace' => ['[length]' => $v['options']['length']]]), $error_field);
						$error = true;
					}
					// validate function
					if (!empty($v['options']['function'])) {
						$this->validate_process_function($v['options']['function'], $data[$k], $error_field);
					}
				}
			}
			// execute domain validator
			if (!empty($v['options']['domain']) && !empty(self::$cached_domains[$v['options']['domain']]['validator_method']) && !empty($data[$k])) {
				$method = explode('::', self::$cached_domains[$v['options']['domain']]['validator_method']);
				$method[0] = factory::model($method[0]);
				$validator_result = call_user_func_array($method, [$data[$k]]);
				if (!$validator_result['success']) {
					foreach ($validator_result['error'] as $v0) {
						$this->error('danger', i18n(null, $v0), $error_field);
					}
					$error = true;
				} else if ($validator_result['success'] && !empty($validator_result['data'])) {
					$data[$k] = $validator_result['data'];
				}
			}
			$data['flag_error'] = $error;
			// if no error we update the value
			if ((!$error && !$do_not_set_values) || $override_values) {
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
			'optimistic_lock' => $this->optimistic_lock,
			'options_model' => $this->misc_settings['options_model'] ?? []
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
			if (!empty($result['options_model'])) {
				foreach ($result['options_model'] as $k => $v) {
					$this->error('danger', i18n(null, object_content_messages::$unknown_value), $k);
				}
				$this->errors['general']['danger'][] = i18n(null, 'There was some errors with your submission!');
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
		if (!empty($this->collection_object)) {
			foreach ($this->collection_object->data['pk'] as $v) {
				if (isset($this->values[$v])) {
					$temp = object_table_columns::process_single_column_type($v, $this->collection_object->primary_model->columns[$v], $this->values[$v]);
					if (array_key_exists($v, $temp)) {
						$this->pk[$v] = $temp[$v];
					}
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
	final public function load_values($for_update = false) {
		// load collection object
		if (!$this->preload_collection_object()) {
			return false;
		}
		// load primary key
		$where = $this->load_pk();
		if (!empty($where)) {
			return $this->collection_object->get(['where' => $where, 'single_row' => true, 'for_update' => $for_update]);
		}
		return false;
	}

	/**
	 * Validate required fields
	 *
	 * @return boolean
	 */
	private function validate_required() {
		// validate regular fields
		foreach ($this->fields as $k => $v) {
			// 1 to 1 details
			if (!empty($v['options']['detail_11'])) {
				$value = array_key_get($this->values, [$v['options']['detail_11'], $v['options']['field_name']]);
			} else {
				$value = array_key_get($this->values, $v['options']['name']);
			}
			// check if its required field
			if (isset($v['options']['required']) && $v['options']['required'] === true) {
				if ($v['options']['php_type'] == 'integer' || $v['options']['php_type'] == 'float') {
					if (empty($value)) {
						$this->error('danger', i18n(null, object_content_messages::$required_field), $k);
					}
				} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
					if (math::compare($value, '0') == 0) {
						$this->error('danger', i18n(null, object_content_messages::$required_field), $k);
					}
				} else {
					if ($value . '' == '') {
						$this->error('danger', i18n(null, object_content_messages::$required_field), $k);
					}
				}
			}
		}
		// validate details
		foreach ($this->detail_fields as $k0 => $v0) {
			$data = $this->values[$v0['options']['details_key']] ?? [];
			foreach ($data as $k11 => $v11) {
				foreach ($v0['elements'] as $k => $v) {
					// skip buttons
					if (!empty($v['options']['process_submit'])) {
						continue;
					}
					$name = $v0['options']['details_key'] . "[{$k11}][" . ($k) . "]";
					$value = $v11[$k] ?? null;
					if (isset($v['options']['required']) && $v['options']['required'] === true) {
						if (empty($v['options']['type'])) {
							$v['options']['type'] = 'varchar';
						}
						// validate
						if ($v['options']['php_type'] == 'integer' || $v['options']['php_type'] == 'float') {
							if (empty($value)) {
								$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
							}
						} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
							if (math::compare($value, '0') == 0) {
								$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
							}
						} else {
							if ($value . '' == '') {
								$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
							}
						}
					}
				}
				// subdetails
				if (!empty($v0['subdetails'])) {
					foreach ($v0['subdetails'] as $k20 => $v20) {
						foreach ($v11[$k20] as $k21 => $v21) {
							foreach ($v20['elements'] as $k22 => $v22) {
								// skip submit buttons
								if (!empty($v22['options']['process_submit'])) continue;
								// validate
								$name = "{$v22['options']['details_parent_key']}[{$k11}][{$v22['options']['details_key']}][{$k21}][{$k22}]";
								$value = $v21[$k22] ?? null;
								if (isset($v22['options']['required']) && $v22['options']['required'] === true) {
									if (empty($v22['options']['type'])) {
										$v22['options']['type'] = 'varchar';
									}
									// validate
									if ($v22['options']['php_type'] == 'integer' || $v22['options']['php_type'] == 'float') {
										if (empty($value)) {
											$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
										}
									} else if ($v22['options']['php_type'] == 'bcnumeric') { // accounting numbers
										if (math::compare($value, '0') == 0) {
											$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
										}
									} else {
										if ($value . '' == '') {
											$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
										}
									}
								}
							}
						}
					}
				}
			}
			// see if detail is required
			if (!empty($v0['options']['required']) && empty($data)) {
				// find all must have columns
				$must_haves = [];
				foreach ($v0['elements'] as $k2 => $v2) {
					if (!empty($v2['options']['details_must_have_column'])) {
						$must_haves[$k2] = $k2;
					}
				}
				if (empty($must_haves)) {
					$must_haves = $v0['options']['details_pk'];
				}
				foreach ($must_haves as $v2) {
					$name = $v0['options']['details_key'] . "[__new_row_1][" . ($v2) . "]";
					$this->error('danger', i18n(null, object_content_messages::$required_field), $name);
				}
			}
		}
	}

	/**
	 * Validate function
	 *
	 * @param string $function
	 * @param string $value
	 * @param string $name
	 */
	private function validate_process_function($function, $value, $name) {
		if ($function == 'strtoupper' && strtoupper($value) != $value) {
			$this->error('danger', i18n(null, 'The string must be uppercase!'), $name);
		} else if ($function == 'strtolower' && strtolower($value) != $value) {
			$this->error('danger', i18n(null, 'The string must be lowercase!'), $name);
		} else if ($function($value) != $value) {
			$this->error('danger', i18n(null, 'The string did not pass validation function!'), $name);
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
	 * @param array $options
	 *		boolean skip_i18n
	 * @param mixed $field
	 */
	public function error($type, $messages, $field = null, $options = []) {
		// convert messages to array
		if (!is_array($messages)) {
			$messages = [$messages];
		}
		// i18n
		if (empty($options['skip_i18n'])) {
			foreach ($messages as $k => $v) {
				$messages[$k] = i18n(null, $v);
			}
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
			// format
			if (!empty($options['format'])) {
				array_key_set($this->errors['formats'], $key, 1);
			}
		} else {
			if (!isset($this->errors['general'][$type])) {
				$this->errors['general'][$type] = [];
			}
			$this->errors['general'][$type] = array_merge($this->errors['general'][$type], $messages);
		}
	}

	/**
	 * Whether form has errors
	 *
	 * @return boolean
	 */
	public function has_errors() {
		return !empty($this->errors['flag_error_in_fields']) || !empty($this->errors['general']['danger']);
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
			if ($type == 'details' && (empty($options['details_key']) || empty($options['details_pk']))) {
				Throw new Exception('Detail key or pk?');
			}
			// processing subdetails
			if ($type == 'subdetails') {
				if (empty($options['details_key']) || empty($options['details_pk']) || empty($options['details_parent_key'])) {
					Throw new Exception('Subdetail key, parent key or pk?');
				}
				$options['flag_child'] = true;
			}
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
			// handling attributes
			if ($row_link == '__attributes' && $this->data[$container_link]['type'] == 'tabs' && application::get('dep.submodule.numbers.data.relations')) {
				$this->attributes = factory::model($this->collection['model'])->attributes;
				if ($this->attributes) {
					// fix row/element
					$this->container('__attributes_container', ['default_row_type' => 'grid', 'order' => 999999, 'custom_renderer' => 'numbers_data_relations_model_attribute_form::render']);
					$this->element($container_link, $row_link, '__attributes', ['container' => '__attributes_container', 'order' => 1]);
					// add model to the collection
					$this->misc_settings['attributes']['values_model'] = 'numbers_data_relations_model_attribute_value1';
					$this->collection['details'][$this->misc_settings['attributes']['values_model']] = [
						'pk' => ['rn_attrvls_attrmdl_id', 'rn_attrvls_link1_id', 'rn_attrvls_attrattr_id', 'rn_attrvls_group_id'],
						'type' => '1M',
						'map' => ['em_entity_id' => 'rn_attrvls_link1_id'],
						'sql' => [
							'where' => "rn_attrvls_attrmdl_id = (SELECT rn_attrmdl_id FROM rn_attribute_models WHERE rn_attrmdl_code = '{$this->collection['model']}')"
						]
					];
				} else {
					// remove it from the tabs
					unset($this->data[$container_link]['rows'][$row_link]);
				}
			}
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
				// need to add a container to the tabs
				$this->misc_settings['tabs'][$container] = $this->data[$container_link]['rows'][$row_link]['options']['label_name'];
			} else {
				// name & id
				if ($this->data[$container_link]['type'] == 'details' || $this->data[$container_link]['type'] == 'subdetails') {
					$options['name'] = $element_link;
					$options['id'] = $element_link;
					$options['details_key'] = $this->data[$container_link]['options']['details_key'];
					$options['details_parent_key'] = $this->data[$container_link]['options']['details_parent_key'] ?? null;
					$options['details_field_name'] = $element_link;
				} else if (!empty($options['detail_11'])) {
					$options['name'] = $options['detail_11'] . '[' . $element_link . ']';
					$options['field_name'] = $element_link;
					$options['id'] = 'form_' . $this->form_link . '_element_' . $element_link;
					if (empty($options['process_submit'])) {
						$value = array_key_get($this->options['input'], [$options['detail_11'], $element_link]);
						$this->values[$options['detail_11']][$element_link] = $value;
					}
				} else {
					$options['name'] = $element_link;
					$options['id'] = 'form_' . $this->form_link . '_element_' . $element_link;
					// populate value array but not for buttons
					if (empty($options['process_submit'])) {
						$value = array_key_get($this->options['input'], $element_link);
						$this->values[$element_link] = $value;
						// detect changes
						if (!empty($options['detect_changes'])) {
							$this->values[$element_link . '_detect_changes'] = $this->options['input'][$element_link . '_detect_changes'] ?? null;
						}
					}
				}
				// process domain & type
				$temp = object_data_common::process_domains(['options' => $options]);
				$options = $temp['options'];
				// put data into fields array
				$field = [
					'id' => $options['id'],
					'name' => $options['name'],
					'options' => $options
				];
				// we need to put values into fields and details
				if ($this->data[$container_link]['type'] == 'details') {
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
				} else if ($this->data[$container_link]['type'] == 'subdetails') {
					$this->data[$container_link]['options']['container_link'] = $container_link;
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_parent_key'] . '::' . $this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
				} else {
					array_key_set($this->fields, $element_link, $field);
				}
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
			// we need to set few misc options
			if (!empty($options['options_model'])) {
				$temp = explode('::', $options['options_model']);
				$name = [];
				if (isset($this->misc_settings['tabs'][$container_link])) {
					$name[] = $this->misc_settings['tabs'][$container_link];
				}
				$name[] = $options['label_name'];
				$this->misc_settings['option_models'][$element_link] = [
					'model' => $temp[0],
					'field_code' => $element_link,
					'field_name' => implode(': ', $name)
				];
			}
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
		// ajax requests from another form
		if ($this->flag_another_ajax_call) {
			return null;
		}
		// css & js
		layout::add_js('/numbers/media_submodules/numbers_frontend_html_form_base.js', 9000);
		// load mask
		numbers_frontend_media_libraries_loadmask_base::add();
		// new record action
		$mvc = application::get('mvc');
		if (object_controller::can('record_new')) {
			$this->actions['form_new'] = ['value' => 'New', 'sort' => -31000, 'icon' => 'file-o', 'href' => $mvc['full'], 'internal_action' => true];
		}
		// back to list
		if (object_controller::can('list_view')) {
			$this->actions['form_back'] = ['value' => 'Back', 'sort' => -32000, 'icon' => 'arrow-left', 'href' => $mvc['controller'] . '/_index', 'internal_action' => true];
		}
		// reload button
		if ($this->values_loaded) {
			$pk = $this->load_pk();
			$url = $mvc['full'] . '?' . http_build_query2($pk);
			$this->actions['form_refresh'] = ['value' => 'Refresh', 'sort' => -30000, 'icon' => 'refresh', 'href' => $url, 'internal_action' => true];
		}
		// assembling everything into result variable
		$result = [];
		// order containers based on order column
		array_key_sort($this->data, ['order' => SORT_ASC]);
		foreach ($this->data as $k => $v) {
			if (!$v['flag_child']) {
				if ($v['type'] == 'fields' || $v['type'] == 'details') {
					// reset tabs
					$this->current_tab = [];
					$temp = $this->render_container($k);
					if ($temp['success']) {
						$result[$k] = $temp['data'];
					}
				} else if ($v['type'] == 'tabs') { // tabs
					$tab_header = [];
					$tab_values = [];
					// sort rows
					array_key_sort($v['rows'], ['order' => SORT_ASC]);
					foreach ($v['rows'] as $k2 => $v2) {
						$this->current_tab[] = 'form_tabs_' . $this->form_link . '_' . $k . '_' . $k2;
						$labels = '';
						$labels.= html::label2(['type' => 'primary', 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '_record']);
						$labels.= html::label2(['type' => 'danger', 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '_error']);
						$tab_header[$k2] = i18n(null, $v2['options']['label_name']) . $labels;
						$tab_values[$k2] = '';
						array_key_sort($v2['elements'], ['order' => SORT_ASC]);
						foreach ($v2['elements'] as $k3 => $v3) {
							$temp = $this->render_container($v3['options']['container']);
							if ($temp['success']) {
								$tab_values[$k2].= $temp['data']['html'];
							}
						}
						// remove last element from an array
						array_pop($this->current_tab);
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
		// we need to skip internal actions
		if (!empty($this->options['no_actions'])) {
			foreach ($this->actions as $k0 => $v0) {
				if (!empty($v0['internal_action'])) {
					unset($this->actions[$k0]);
				}
			}
		}
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
		$result.= html::submit(['name' => 'submit_hidden' , 'value' => 1, 'style' => 'display: none;']);
		$result.= html::hidden(['name' => 'submit_hidden_submit', 'value' => '']);
		if (!empty($this->optimistic_lock)) {
			$result.= html::hidden(['name' => $this->optimistic_lock['column'], 'value' => $this->optimistic_lock['value']]);
		}
		if (!empty($this->options['bypass_hidden_values'])) {
			foreach ($this->options['bypass_hidden_values'] as $k => $v) {
				$result.= html::hidden(['name' => $k, 'value' => $v]);
			}
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
			$mvc = application::get('mvc');
			$result = html::form([
				'action' => $mvc['full'],
				'name' => "form_{$this->form_link}_form",
				'id' => "form_{$this->form_link}_form",
				'value' => $result,
				'onsubmit' => empty($this->options['no_ajax_form_reload']) ? 'return numbers.frontend_form.on_form_submit(this);' : null
			]);
		}
		// if we came from ajax we return as json object
		if (!empty($this->options['input']['__ajax'])) {
			$result = [
				'success' => true,
				'error' => [],
				'html' => $result,
				'js' => layout::$onload
			];
			layout::render_as($result, 'application/json');
		}
		$result = "<div id=\"form_{$this->form_link}_form_mask\"><div id=\"form_{$this->form_link}_form_wrapper\">" . $result . '</div></div>';
		// if we have segment
		if (isset($this->options['segment'])) {
			$temp = is_array($this->options['segment']) ? $this->options['segment'] : [];
			$temp['value'] = $result;
			$result = html::segment($temp);
		}
		return $result;
	}

	/**
	 * Render container with type details
	 *
	 * @param string $container_link
	 * @return array
	 */
	public function render_container_type_details($container_link) {
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
		// get the data
		$details_new_rows = $this->data[$container_link]['options']['details_new_rows'] ?? 0;
		$key = $this->data[$container_link]['options']['details_key'];
		$data = $this->values[$key] ?? [];
		// details_unique_select
		if (!empty($this->misc_settings['details_unique_select'][$key])) {
			foreach ($this->misc_settings['details_unique_select'][$key] as $k => $v) {
				foreach ($data as $k2 => $v2) {
					if (!empty($v2[$k])) {
						$this->misc_settings['details_unique_select'][$key][$k][$v2[$k]] = $v2[$k];
					}
				}
			}
		}
		// rendering
		$result['data']['html'] = $this->render_container_type_details_rows($this->data[$container_link]['rows'], $data, ['details_key' => $key, 'new_rows' => $details_new_rows, 'details_rendering_type' => $this->data[$container_link]['options']['details_rendering_type'] ?? 'grid_with_label']);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Render container with type subdetails
	 *
	 * @param string $container_link
	 * @param array $options
	 * @return array
	 */
	public function render_container_type_subdetails($container_link, $options = []) {
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
		// get the data
		$details_new_rows = $this->data[$container_link]['options']['details_new_rows'] ?? 0;
		$key = $this->data[$container_link]['options']['details_key'];
		$parent_key = $this->data[$container_link]['options']['details_parent_key'];
		$data = $options['__values'];
		// details_unique_select
		if (!empty($this->misc_settings['details_unique_select'][$parent_key . '::' . $key])) {
			foreach ($this->misc_settings['details_unique_select'][$parent_key . '::' . $key] as $k => $v) {
				foreach ($data as $k2 => $v2) {
					if (!empty($v2[$k])) {
						$this->misc_settings['details_unique_select'][$parent_key . '::' . $key][$k][$options['__parent_row_number']][$v2[$k]] = $v2[$k];
					}
				}
			}
		}
		// rendering
		$result['data']['html'] = $this->render_container_type_details_rows($this->data[$container_link]['rows'], $data, ['details_key' => $key, 'details_parent_key' => $parent_key, '__parent_row_number' => $options['__parent_row_number'], 'new_rows' => $details_new_rows, 'details_rendering_type' => $this->data[$container_link]['options']['details_rendering_type'] ?? 'grid_with_label']);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Details - render table
	 *
	 * @param array $rows
	 * @param array $values
	 * @param array $options
	 */
	public function render_container_type_details_rows($rows, $values, $options = []) {
		$result = '';
		$row_number = 1;
		// building table
		$table = [
			'header' => [
				'row_number' => '',
				'row_data' => '',
			],
			'options' => [],
			'skip_header' => true
		];
		// empty data variable
		$data = [
			'options' => []
		];
		// header rows for table
		if ($options['details_rendering_type'] == 'table') {
			foreach ($rows as $k => $v) {
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					foreach ($v2 as $k3 => $v3) {
						$data['options'][$k][$k2][$k3] = [
							'label' => $this->render_element_name($first),
							'options' => $v3['options'],
						];
					}
				}
			}
			// add a row to a table
			$table['options']['__header'] = [
				'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
				'row_data' => html::grid($data)
			];
		}
		// we must sort
		array_key_sort($rows, ['order' => SORT_ASC]);
		// looping through existing rows
		foreach ($values as $k0 => $v0) {
			// empty data variable
			$data = [
				'options' => []
			];
			foreach ($rows as $k => $v) {
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					if ($first_key == self::SEPARATOR_HORISONTAL) {
						$data['options'][$row_number . '_' . $k][$k2][0] = [
							'value' => html::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
							'separator' => true
						];
					} else {
						$first['prepend_to_field'] = ':';
						foreach ($v2 as $k3 => $v3) {
							// generate id, name and error name
							if (empty($options['details_parent_key'])) {
								$name = "{$options['details_key']}[{$row_number}][{$k3}]";
								$id = "{$options['details_key']}_{$row_number}_{$k3}";
								$error_name = "{$options['details_key']}[{$k0}][{$k3}]";
							} else {
								$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$row_number}][{$k3}]";
								$id = "{$options['details_parent_key']}_{$options['__parent_row_number']}_{$options['details_key']}_{$row_number}_{$k3}";
								$error_name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k0}][{$k3}]";
							}
							// error
							$error = $this->get_field_errors([
								'options' => [
									'name' => $error_name
								]
							]);
							if ($error['counter'] > 0) {
								$this->error_in_tabs($error['counter']);
							}
							// generate proper element
							$value_options = $v3;
							$value_options['options']['id'] = $id;
							$value_options['options']['name'] = $name;
							$value_options['options']['error_name'] = $error_name;
							$value_options['options']['details_parent_key'] = $options['details_parent_key'] ?? null;
							$value_options['options']['__parent_row_number'] = $options['__parent_row_number'] ?? null;
							$value_options['options']['__row_number'] = $row_number;
							// label
							$label = null;
							if ($options['details_rendering_type'] == 'grid_with_label') {
								$label = $this->render_element_name($first);
							}
							// add element to grid
							$data['options'][$row_number . '_' . $k][$k2][$k3] = [
								'error' => $error,
								'label' => $label,
								'value' => $this->render_element_value($value_options, $v0[$k3] ?? null, $v0),
								'description' => null,
								'options' => $v3['options'],
								'row_class' => !($row_number % 2) ? 'grid_row_even' : 'grid_row_odd'
							];
						}
					}
				}
			}
			// increase counter
			$this->error_in_tabs(1, true);
			// subdetails
			if (!empty($this->detail_fields[$options['details_key']]['subdetails'])) {
				$tab_header = [
					'tabs_subdetails_none' => i18n(null, 'None')
				];
				$tab_values = [
					'tabs_subdetails_none' => ''
				];
				$tab_options = [
					'tabs_subdetails_none' => []
				];
				// sort subdetail tabs
				$tab_sorted = [];
				foreach ($this->detail_fields[$options['details_key']]['subdetails'] as $k10 => $v10) {
					$tab_sorted[$k10] = [
						'order' => $v10['options']['order'] ?? 0
					];
				}
				array_key_sort($tab_sorted, ['order' => SORT_ASC]);
				// render tabs
				$have_tabs = false;
				foreach ($tab_sorted as $k10 => $v10) {
					$v10 = $this->detail_fields[$options['details_key']]['subdetails'][$k10];
					$this->current_tab[] = "form_tabs_subdetails_{$v10['options']['details_parent_key']}_{$row_number}_{$k10}";
					$labels = '';
					$labels.= html::label2(['type' => 'primary', 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '_record']);
					$labels.= html::label2(['type' => 'danger', 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '_error']);
					$tab_header[$k10] = i18n(null, $v10['options']['label_name']) . $labels;
					$tab_values[$k10] = '';
					// handling override_field_value method
					if (!empty($this->wrapper_methods['disable_subdetail_tabs']['main'])) {
						$tab_options[$k10] = call_user_func_array($this->wrapper_methods['disable_subdetail_tabs']['main'], [& $this, & $v10, & $k10, & $v0]);
						if (empty($tab_options[$k10]['li_hidden'])) {
							$have_tabs = true;
						}
					}
					$v10['__values'] = $v0[$v10['options']['details_key']] ?? [];
					$v10['__parent_row_number'] = $row_number;
					$temp = $this->render_container_type_subdetails($v10['options']['container_link'], $v10);
					if ($temp['success']) {
						$tab_values[$k10].= $temp['data']['html'];
					}
					// we must unset it
					array_pop($this->current_tab);
				}
				// if we do not have tabs
				if (!$have_tabs) {
					$tab_options['tabs_subdetails_none']['li_hidden'] = true;
				}
				$subdetails = html::tabs([
					'id' => "subdetails_{$options['details_key']}_{$row_number}_tabs",
					'header' => $tab_header,
					'options' => $tab_values,
					'class' => 'tabs_subdetails',
					'tab_options' => $tab_options
				]);
				// add row to the end
				$data['options'][$row_number . '_subdetails']['subdetails']['subdetails'] = [
					'error' => null,
					'label' => null,
					'value' => $subdetails,
					'description' => null,
					'options' => [
						'percent' => 100
					],
					'row_class' => !($row_number % 2) ? 'grid_row_even' : 'grid_row_odd'
				];
			}
			// add a row to a table
			$table['options'][$row_number] = [
				'row_number' => ['value' => $row_number . '.', 'width' => '1%'],
				'row_data' => html::grid($data)
			];
			$row_number++;
		}
		// new rows
		if (!empty($options['new_rows'])) {
			$max = $row_number + $options['new_rows'];
			for ($row_number = $row_number; $row_number < $max; $row_number++) {
				// empty data variable
				$data = [
					'options' => []
				];
				foreach ($rows as $k => $v) {
					array_key_sort($v['elements'], ['order' => SORT_ASC]);
					// group by
					$groupped = [];
					foreach ($v['elements'] as $k2 => $v2) {
						$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
					}
					foreach ($groupped as $k2 => $v2) {
						$first = current($v2);
						$first_key = key($v2);
						if ($first_key == self::SEPARATOR_HORISONTAL) {
							$data['options'][$row_number . '_' . $k][$k2][0] = [
								'value' => html::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
								'separator' => true
							];
						} else {
							$first['prepend_to_field'] = ':';
							foreach ($v2 as $k3 => $v3) {
								// generate id, name and error name
								if (empty($options['details_parent_key'])) {
									$name = "{$options['details_key']}[{$row_number}][{$k3}]";
									$id = "{$options['details_key']}_{$row_number}_{$k3}";
									$error_name = "{$options['details_key']}[__new_row_{$row_number}][{$k3}]";
								} else {
									$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$row_number}][{$k3}]";
									$id = "{$options['details_parent_key']}_{$options['__parent_row_number']}_{$options['details_key']}_{$row_number}_{$k3}";
									$error_name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][__new_row_{$row_number}][{$k3}]";
								}
								// error
								$error = $this->get_field_errors([
									'options' => [
										'name' => $error_name
									]
								]);
								if ($error['counter'] > 0) {
									$this->error_in_tabs($error['counter']);
								}
								// generate proper element
								$value_options = $v3;
								$value_options['options']['id'] = $id;
								$value_options['options']['name'] = $name;
								$value_options['options']['error_name'] = $error_name;
								$value_options['options']['error_name'] = $error_name;
								$value_options['options']['details_parent_key'] = $options['details_parent_key'] ?? null;
								$value_options['options']['__parent_row_number'] = $options['__parent_row_number'] ?? null;
								$value_options['options']['__row_number'] = $row_number;
								// label
								$label = null;
								if ($options['details_rendering_type'] == 'grid_with_label') {
									$label = $this->render_element_name($first);
								}
								// add element to grid
								$data['options'][$row_number . '_' . $k][$k2][$k3] = [
									'error' => $error,
									'label' => $label,
									'value' => $this->render_element_value($value_options, null),
									'description' => null,
									'options' => $v3['options'],
									'row_class' => !($row_number % 2) ? 'grid_row_even' : 'grid_row_odd'
								];
							}
						}
					}
				}
				// add a row to a table
				$table['options'][$row_number] = [
					'row_number' => ['value' => $row_number . '.', 'width' => '1%'],
					'row_data' => html::grid($data)
				];
			}
		}
		return html::table($table);
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
			// important to use $this if its the same class
			if ($temp[0] == $this->form_class) {
				$temp[0] = & $this->form_parent;
			} else {
				$temp[0] = factory::model($temp[0], true);
			}
			return call_user_func_array($temp, [& $this]);
		}
		// if its details we need to render it differently
		if ($this->data[$container_link]['type'] == 'details') {
			return $this->render_container_type_details($container_link);
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
						// handling errors
						$error = $this->get_field_errors($v3);
						if ($error['counter'] > 0) {
							$this->error_in_tabs($error['counter']);
						}
						// we do not show hidden fields
						if (($v3['options']['method'] ?? '') == 'hidden') {
							$v3['options']['style'] = ($v3['options']['style'] ?? '') . 'display: none;';
						}
						$v3['options']['error_name'] = $k3;
						$data['options'][$k][$k2][$k3] = [
							'error' => $error,
							'label' => $this->render_element_name($first),
							'value' => $this->render_element_value($v3, $this->get_field_value($v3), $this->values ?? []),
							'description' => $v3['options']['description'] ?? null,
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
			// 1 to 1 details
			if (!empty($field['options']['detail_11'])) {
				$value = array_key_get($this->values, [$field['options']['detail_11'], $field['options']['field_name']]);
			} else {
				$value = array_key_get($this->values, $field['options']['name']);
			}
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
						$v3['options']['error_name'] = $k3;
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
	 * @param array $neighbouring_values
	 * @return string
	 * @throws Exception
	 */
	public function render_element_value($options, $value = null, $neighbouring_values = []) {
		// handling override_field_value method
		if (!empty($this->wrapper_methods['override_field_value']['main'])) {
			call_user_func_array($this->wrapper_methods['override_field_value']['main'], [& $this, & $options, & $value, & $neighbouring_values]);
		}
		$result_options = $options['options'];
		array_key_extract_by_prefix($result_options, 'label_');
		$element_expand = !empty($result_options['expand']);
		// unset certain keys
		unset($result_options['order'], $result_options['required']);

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
			if (empty($result_options['options_params'])) {
				$result_options['options_params'] = [];
			}
			// options depends
			if (!empty($options['options']['options_depends'])) {
				foreach ($options['options']['options_depends'] as $k => $v) {
					// important to skip fields with errors
					if (!empty($this->errors['fields'][$v]['danger'])) {
						continue;
					}
					$result_options['options_params'][$k] = $neighbouring_values[$v] ?? null;
				}
			}
			// we do not need options for autocomplete
			if (strpos($result_options['method'], 'autocomplete') === false) {
				$skip_values = [];
				if (!empty($options['options']['details_key'])) {
					if (!empty($options['options']['details_parent_key'])) {
						$temp_key = $options['options']['details_parent_key'] . '::' . $options['options']['details_key'];
						if (!empty($this->misc_settings['details_unique_select'][$temp_key][$options['options']['details_field_name']][$options['options']['__parent_row_number']])) {
							$skip_values = array_keys($this->misc_settings['details_unique_select'][$temp_key][$options['options']['details_field_name']][$options['options']['__parent_row_number']]);
						}
					} else {
						if (!empty($this->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']])) {
							$skip_values = array_keys($this->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']]);
						}
					}
				}
				$result_options['options'] = object_data_common::process_options($result_options['options_model'], $this, $result_options['options_params'], $value, $skip_values);
			} else {
				// we need to inject form id into autocomplete
				$result_options['form_id'] = "form_{$this->form_link}_form";
			}
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
					$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value'] ?? null);
					// process confirm_message
					$result_options['onclick'] = $result_options['onclick'] ?? '';
					if (!empty($result_options['confirm_message'])) {
						$result_options['onclick'].= 'return confirm(\'' . strip_tags(i18n(null, $result_options['confirm_message'])) . '\');';
					}
					// processing onclick for buttons
					if (in_array($element_method, ['html::submit', 'html::button', 'html::button2'])) {
						if (empty($result_options['onclick'])) {
							$result_options['onclick'].= 'numbers.frontend_form.trigger_submit_on_button(this); return true;';
						} else {
							$result_options['onclick'] = 'numbers.frontend_form.trigger_submit_on_button(this); ' . $result_options['onclick'];
						}
					}
					$flag_translated = true;
				} else {
					// editable fields
					$result_options['value'] = $value;
					// format
					if (!empty($result_options['format'])) {
						if (!empty($this->errors['fields'][$result_options['error_name']]) && empty($this->errors['formats'][$result_options['error_name']])) {
							// nothing
						} else {
							$method = factory::method($result_options['format'], 'format');
							$result_options['value'] = call_user_func_array([$method[0], $method[1]], [$result_options['value'], $result_options['format_options'] ?? []]);
						}
					}
					// align
					if (!empty($result_options['align'])) {
						$result_options['style'] = ($result_options['style'] ?? '') . 'text-align:' . $result_options['align'] . ';';
					}
					// processing readonly_if_saved
					if (!empty($result_options['readonly_if_saved']) && $this->values_loaded) {
						$result_options['readonly'] = true;
					}
					// processing readonly_if_set_and_saved
					if (!empty($result_options['readonly_if_set_and_saved']) && $this->values_loaded && !empty($value)) {
						$result_options['readonly'] = true;
					}
				}
				break;
			case 'html':
				$element_method = null;
				break;
			default:
				Throw new Exception('Render detail type: ' . $data['fm_part_type']);
		}
		// handling html_method
		if (isset($element_method)) {
			$method = factory::method($element_method, 'html');
			$field_method_object = factory::model($method[0], true);
			$value = $field_method_object->{$method[1]}($result_options);
			// building navigation
			if (!empty($result_options['navigation'])) {
				$name = 'navigation[' . $result_options['name'] . ']';
				$temp = '<table width="100%">';
					$temp.= '<tr>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[first]', 'value' => html::icon(['type' => 'step-backward']), 'onclick' => "$('#form_{$this->form_link}_form').attr('no_ajax', 1); return true;"]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[previous]', 'value' => html::icon(['type' => 'caret-left']), 'onclick' => "$('#form_{$this->form_link}_form').attr('no_ajax', 1); return true;"]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="90%">' . $value . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[refresh]', 'value' => html::icon(['type' => 'refresh']), 'onclick' => "$('#form_{$this->form_link}_form').attr('no_ajax', 1); return true;"]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[next]', 'value' => html::icon(['type' => 'caret-right']), 'onclick' => "$('#form_{$this->form_link}_form').attr('no_ajax', 1); return true;"]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[last]', 'value' => html::icon(['type' => 'step-forward']), 'onclick' => "$('#form_{$this->form_link}_form').attr('no_ajax', 1); return true;"]) . '</td>';
					$temp.= '</tr>';
				$temp.= '</table>';
				$value = $temp;
			}
		}
		// handling changes
		if (!empty($result_options['detect_changes'])) {
			if (is_array($result_options['value'])) {
				foreach ($result_options['value'] as $v) {
					$value.= html::hidden(['name' => $result_options['name'] . '_detect_changes[]', 'value' => $v]);
				}
			} else {
				$value.= html::hidden(['name' => $result_options['name'] . '_detect_changes', 'value' => $result_options['value']]);
			}
		}
		return $value;
	}
}