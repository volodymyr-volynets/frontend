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
	 * Fields for details
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
	 * Batch values
	 *
	 * @var array
	 */
	public $batch_values = [];

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
	public $process_submit_all = [];

	/**
	 * Submitted
	 *
	 * @var boolean
	 */
	public $submitted = false;

	/**
	 * Refresh
	 *
	 * @var boolean
	 */
	public $refresh = false;

	/**
	 * Reset
	 *
	 * @var boolean
	 */
	public $blank = false;

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
	 * Indicator that the record has been inserted/updated in database
	 *
	 * @var boolean
	 */
	public $values_saved = false;

	/**
	 * Indicator that record has been deleted
	 *
	 * @var boolean
	 */
	public $values_deleted = false;

	/**
	 * Indicator whether transaction has been started
	 *
	 * @var boolean
	 */
	public $transaction = false;

	/**
	 * Indicator that transaction has been rolled back
	 *
	 * @var boolean
	 */
	public $rollback = false;

	/**
	 * Primary key
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * If full pk was provided
	 *
	 * @var boolean
	 */
	public $full_pk = false;

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
	 * Misc. Settings
	 *
	 * @var array
	 */
	public $misc_settings = [];

	/**
	 * Master object, used for validations
	 *
	 * @var object
	 */
	public $master_object;

	/**
	 * Master options
	 *
	 * @var array
	 */
	public $master_options = [];

	/**
	 * Tab index
	 *
	 * @var int
	 */
	private $tabindex = 1;

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
		$this->error_reset_all();
		// actions
		if (!empty($this->options['actions'])) {
			$this->actions = array_merge($this->actions, $this->options['actions']);
		}
		// batch values
		if (!empty($this->options['batch_values'])) {
			$this->batch_values = $this->options['batch_values'];
			unset($this->options['batch_values']);
		}
	}

	/**
	 * Trigger method
	 *
	 * @param string $method
	 */
	public function trigger_method($method) {
		// call methods from master first
		if (!empty($this->master_options['methods'][$method]) && method_exists($this->master_object->ledgers->{$this->master_options['ledger']}, $this->master_options['methods'][$method])) {
			$this->master_object->ledgers->{$this->master_options['ledger']}->{$this->master_options['methods'][$method]}($this, []);
		}
		// handling actual method
		if (!empty($this->wrapper_methods[$method])) {
			foreach ($this->wrapper_methods[$method] as $k => $v) {
				call_user_func_array($v, [& $this]);
			}
		}
	}

	/**
	 * Get original values
	 *
	 * @param array $input
	 */
	private function get_original_values($input, $for_update) {
		// process primary key
		$this->full_pk = false;
		$this->load_pk($input);
		// load values if we have full pk
		if ($this->full_pk) {
			$temp = $this->load_values($for_update);
			if ($temp !== false) {
				$this->original_values = $temp;
				$this->values_loaded = true;
			}
		}
	}

	/**
	 * Sort fields for processing
	 *
	 * @param array $fields
	 * @return int
	 */
	private function sort_fields_for_processing($fields) {
		foreach ($fields as $k => $v) {
			if (!empty($v['options']['default']) && (strpos($v['options']['default'], 'parent::') !== false || strpos($v['options']['default'], 'static::') !== false)) {
				$column = str_replace(['parent::', 'static::'], '', $v['options']['default']);
				$fields[$k]['order_for_defaults'] = ($fields[$column]['order_for_defaults'] ?? 0) + 100;
			} else if (!isset($fields[$k]['order_for_defaults'])) {
				$fields[$k]['order_for_defaults'] = 0;
			}
		}
		array_key_sort($fields, ['order_for_defaults' => SORT_ASC]);
		return $fields;
	}

	/**
	 * Validate required one field
	 *
	 * @param mixed $value
	 * @param string $error_name
	 * @param array $options
	 */
	private function validate_required_one_field(& $value, $error_name, $options) {
		// check if its required field
		if (isset($options['options']['required']) && ($options['options']['required'] === true || $options['options']['required'] === 1 || $options['options']['required'] === '1')) {
			if ($options['options']['php_type'] == 'integer' || $options['options']['php_type'] == 'float') {
				if (empty($value)) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else if ($options['options']['php_type'] == 'bcnumeric') { // accounting numbers
				if (math::compare($value, '0') == 0) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else if (!empty($options['options']['multiple_column'])) {
				if (empty($value)) {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			} else {
				if ($value . '' == '') {
					$this->error('danger', object_content_messages::required_field, $error_name);
				}
			}
		}
		// validator
		if (!empty($options['options']['validator_method']) && !empty($value)) {
			$neighbouring_values_key = $options['options']['values_key'];
			array_pop($neighbouring_values_key);
			$temp = object_validator_base::method(
				$options['options']['validator_method'],
				$value,
				$options['options']['validator_params'] ?? [],
				$options['options'],
				array_key_get($this->values, $neighbouring_values_key)
			);
			if (!$temp['success']) {
				foreach ($temp['error'] as $v10) {
					$this->error('danger', $v10, $error_name);
				}
			} else if (!empty($temp['data'])) {
				$value = $temp['data'];
			}
		}
	}

	/**
	 * Get all values
	 *
	 * @param array $input
	 * @param array $options
	 *		validate_required
	 * @return array
	 */
	private function get_all_values($input, $options = []) {
		// reset values
		$this->misc_settings['options_model'] = [];
		$this->values = [];
		// sort fields
		$fields = $this->sort_fields_for_processing($this->fields);
		// if we delete we only allow pk and optimistic lock
		$allowed = [];
		if (!empty($options['validate_for_delete'])) {
			$allowed = $this->collection_object->data['pk'];
			if ($this->collection_object->primary_model->optimistic_lock) {
				$allowed[] = $this->collection_object->primary_model->optimistic_lock_column;
			}
		}
		// process fields
		foreach ($fields as $k => $v) {
			// skip certain values
			if (!empty($v['options']['process_submit'])) continue;
			if ($k == $this::separator_horisontal || $k == $this::separator_vertical) continue;
			if (!empty($options['only_columns']) && !in_array($k, $options['only_columns'])) continue;
			// process allowed
			if (!empty($allowed) && !in_array($k, $allowed)) continue;
			// default data type
			if (empty($v['options']['type'])) {
				$v['options']['type'] = 'varchar';
			}
			// get value
			$value = array_key_get($input, $v['options']['values_key']);
			$error_name = $v['options']['error_name'];
			// multiple column
			if (!empty($v['options']['multiple_column'])) {
				if (!empty($value)) {
					if (!is_array($value)) {
						$value = [$value];
					}
					$temp_value = [];
					foreach ($value as $k2 => $v2) {
						$temp = $this->validate_data_types_single_value($k, $v, $v2, $error_name);
						if (empty($temp['flag_error'])) {
							$temp_value[$v2] = [
								$v['options']['multiple_column'] => $temp[$k]
							];
						} else {
							$temp_value[$v2] = [
								$v['options']['multiple_column'] => $v2
							];
						}
					}
					$value = $temp_value;
				} else {
					$value = [];
				}
			} else {
				$temp = $this->validate_data_types_single_value($k, $v, $value, $error_name);
				if (empty($temp['flag_error'])) {
					if (empty($temp[$k]) && !empty($temp[$k . '_is_serial'])) {
						// we do not create empty serial keys
						continue;
					} else {
						$value = $temp[$k];
					}
				}
			}
			// persistent
			if ($this->values_loaded && !empty($this->misc_settings['persistent']['fields'][$k]) && isset($this->original_values[$k])) {
				if (is_null($value)) {
					$value = $this->original_values[$k];
				} else if ($value !== $this->original_values[$k]) {
					$this->error('danger', 'You are trying to change persistent field!', $error_name);
				}
			}
			// default
			if (array_key_exists('default', $v['options'])) {
				if (strpos($v['options']['default'], 'static::') !== false || is_null($value)) {
					$value = $this->process_default_value($k, $v['options']['default']);
				}
			}
			// validate required
			if (!empty($options['validate_required'])) {
				$this->validate_required_one_field($value, $error_name, $v);
			}
			array_key_set($this->values, $v['options']['values_key'], $value);
			// options_model
			if (!empty($v['options']['options_model']) && empty($v['options']['options_manual_validation'])) {
				// options depends & params
				$v['options']['options_depends'] = $v['options']['options_depends'] ?? [];
				$v['options']['options_params'] = $v['options']['options_params'] ?? [];
				$this->process_params_and_depends($v['options']['options_depends'], $this->values, [], true);
				$this->process_params_and_depends($v['options']['options_params'], $this->values, [], false);
				$v['options']['options_params'] = array_merge_hard($v['options']['options_params'], $v['options']['options_depends']);
				$this->misc_settings['options_model'][$k] = [
					'options_model' => $v['options']['options_model'],
					'options_params' => $v['options']['options_params'],
					'key' => !empty($v['options']['detail_11']) ? [$v['options']['detail_11'], $k] : [$k]
				];
			}
		}
		// check optimistic lock
		if ($this->values_loaded && $this->collection_object->primary_model->optimistic_lock) {
			if (($this->values[$this->collection_object->primary_model->optimistic_lock_column] ?? '') !== $this->original_values[$this->collection_object->primary_model->optimistic_lock_column]) {
				$this->error('danger', object_content_messages::optimistic_lock);
			}
		}
		// process details & subdetails
		if (empty($options['validate_for_delete']) && !empty($this->detail_fields)) {
			foreach ($this->detail_fields as $k => $v) {
				$details = $input[$k] ?? [];
				// sort fields
				$fields = $this->sort_fields_for_processing($v['elements']);
				// we we have custom data processor
				if (!empty($v['options']['details_process_widget_data'])) {
					Throw new Exception('details_process_widget_data');
					continue;
				}
				// process details one by one
				$autoincrement = $v['options']['details_autoincrement'];
				$counter = 1;
				$new_pk_counter = 1;
				$new_pk_locks = [];
				foreach ($details as $k2 => $v2) {
					$flag_change_detected = false;
					// we need to convert keys from new rows
					$new_pk = [];
					foreach ($v['options']['details_pk'] as $v8) {
						if (!empty($v2[$v8])) {
							$new_pk[] = $v2[$v8];
						} else {
							$new_pk[] = '__new_key_' . $new_pk_counter;
							$new_pk_counter++;
						}
					}
					$k2 = implode('::', $new_pk);
					if (!empty($new_pk_locks[$k2])) {
						$k2 = '__duplicate_key_' . $new_pk_counter;
						$new_pk_counter++;
						foreach ($v['options']['details_pk'] as $v8) {
							$this->error('danger', object_content_messages::duplicate_value, "{$v3['options']['details_key']}[{$k2}][{$v8}]");
						}
					} else {
						$new_pk_locks[$k2] = true;
					}
					$detail = [];
					foreach ($fields as $k3 => $v3) {
						$error_name = "{$v3['options']['details_key']}[{$k2}]";
						// skip certain values
						if (!empty($v3['options']['process_submit'])) continue;
						if ($k3 == $this::separator_horisontal || $k3 == $this::separator_vertical) continue;
						// default data type
						if (empty($v3['options']['type'])) {
							$v3['options']['type'] = 'varchar';
						}
						// get value
						$value = $v2[$k3] ?? null;
						// validate data type
						$temp = $this->validate_data_types_single_value($k3, $v3, $value, "{$error_name}[{$k3}]");
						if (empty($temp['flag_error'])) {
							if (empty($temp[$k3]) && !empty($temp[$k . '_is_serial'])) {
								// we do not create empty serial keys
								continue;
							} else {
								$value = $temp[$k3];
							}
						}
						// persistent
						// todo test
						if ($this->values_loaded && !empty($this->misc_settings['persistent']['details'][$k][$k3]) && isset($this->original_values[$k][$k2][$k3])) {
							if (is_null($value)) {
								$value = $this->original_values[$k][$k2][$k3];
							} else if ($value !== $this->original_values[$k][$k2][$k3]) {
								$this->error('danger', 'You are trying to change persistent field!', "{$error_name}[{$k3}]");
							}
						}
						// default
						$default = null;
						if (array_key_exists('default', $v3['options'])) {
							$default = $this->process_default_value($k3, $v3['options']['default']);
							if (strpos($v3['options']['default'], 'static::') !== false || is_null($value)) {
								$value = $default;
							}
						}
						// see if we changed the value
						if (!is_null($value) && $value !== $default) {
							$flag_change_detected = true;
						}
						$detail[$k3] = $value;
					}
					// if we have changes we puth them into values
					if ($flag_change_detected) {
						$this->values[$k][$k2] = $detail;
						// validate required fields
						if (!empty($options['validate_required'])) {
							foreach ($fields as $k3 => $v3) {
								$v3['options']['values_key'] = [$k, $k2, $k3];
								$this->validate_required_one_field($this->values[$k][$k2][$k3], "{$error_name}[{$k3}]", $v3);
							}
						}
					}
				}
				// see if detail is required
				if (!empty($options['validate_required']) && !empty($v['options']['required']) && empty($this->values[$k])) {
					foreach ($v['options']['details_pk'] as $v8) {
						$this->error('danger', object_content_messages::required_field, "{$v['options']['details_key']}[1][{$v8}]");
					}
				}
				
				
				// important!!! if we can not process details - all subdetails will be lost

				// process subdetails
				/*
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
				*/
			}
		}
	}

	/**
	 * Process
	 */
	public function process() {
		// reset
		$this->submitted = false;
		$this->refresh = false;
		$this->blank = false;
		$this->values_loaded = false;
		$this->values_saved = false;
		$this->values_deleted = false;
		$this->transaction = $this->rollback = false;
		// preload collection, must be first
		if ($this->preload_collection_object()) {
			// if we have relation
			if (!empty($this->collection_object->primary_model->relation['field']) && !in_array($this->collection_object->primary_model->relation['field'], $this->collection_object->primary_model->pk)) {
				$this->element($this::hidden, $this::hidden, $this->collection_object->primary_model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'persistent' => true]);
			}
			// optimistic lock
			if (!empty($this->collection_object->primary_model->optimistic_lock)) {
				$this->element($this::hidden, $this::hidden, $this->collection_object->primary_model->optimistic_lock_column, ['label_name' => 'Optimistic Lock', 'domain' => 'optimistic_lock', 'null' => true, 'default' => null, 'method'=> 'hidden']);
			}
		}
		// hidden buttons to handle form though javascript
		$this->element($this::hidden, $this::hidden, $this::button_submit_refresh, $this::button_submit_refresh_data);
		if (!isset($this->process_submit_all[$this::button_submit_blank])) {
			$this->element($this::hidden, $this::hidden, $this::button_submit_blank, $this::button_submit_blank_data);
		}
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
				$this->load_pk($this->options['input']);
				// we need to set this flag so ajax calls can go through
				//$this->values_loaded = true;
				$this->flag_another_ajax_call = true;
				return;
			}
		} else if (!empty($this->options['input']['__form_link']) && $this->options['input']['__form_link'] != $this->form_link) { // it its a call from another form
			$this->trigger_method('refresh');
			goto load_values;
		}
		// navigation
		if (!empty($this->options['input']['navigation'])) {
			$this->process_navigation($this->options['input']['navigation']);
		}
		// we need to see if form has been submitted
		$this->process_submit = [];
		if (isset($this->process_submit_all[$this::button_submit_blank]) && !empty($this->options['input'][$this::button_submit_blank])) {
			$this->blank = true;
			$this->process_submit = [
				$this::button_submit_blank => true
			];
		} else if (isset($this->process_submit_all[$this::button_submit_refresh]) && !empty($this->options['input'][$this::button_submit_refresh])) {
			$this->refresh = true;
			$this->process_submit = [
				$this::button_submit_refresh => true
			];
		} else {
			foreach ($this->process_submit_all as $k => $v) {
				if (!empty($this->options['input'][$k])) {
					$this->submitted = true;
					$this->process_submit[$k] = true;
				}
			}
		}
		// if we are blanking the form
		if ($this->blank) {
			$this->get_all_values([]);
			goto convert_multiple_columns;
		}
		// we need to start transaction
		if (!empty($this->collection_object) && $this->submitted) {
			$this->collection_object->primary_model->db_object->begin();
			$this->transaction = true;
		}
		// load original values
		$this->get_original_values($this->options['input'] ?? [], $this->transaction);
		// validate submits
		if ($this->submitted) {
			if (!$this->validate_submit_buttons()) {
				goto process_errors;
			}
		}
		//print_r2($this->process_submit);
		if (!$this->submitted && !$this->refresh) {
			goto load_values;
		}
		// get all values
		$this->get_all_values($this->options['input'] ?? [], [
			'validate_required' => $this->submitted,
			'validate_for_delete' => $this->process_submit[self::button_submit_delete] ?? false
		]);
		//print_r2($this->values);
		// handling form refresh
		$this->trigger_method('refresh');
		if ($this->refresh) {
			goto convert_multiple_columns;
		}
		// if form has been submitted
		if ($this->submitted) {
			// call attached method to the form
			if (method_exists($this, 'validate')) {
				$this->validate($this);
			} else if (!empty($this->wrapper_methods['validate'])) {
				$this->trigger_method('validate');
			}
			// if we have no error and have proper submit we proceed to saving
			if (!$this->has_errors() && !empty($this->process_submit[$this::button_submit_save])) {
				if (method_exists($this, 'save')) {
					$this->values_saved = $this->save($this);
				} else if (!empty($this->wrapper_methods['save'])) {
					$this->values_saved = $this->trigger_method('save');
				} else { // native save based on collection
					$this->values_saved = $this->save_values();
					/*
					 * todo
					if ($this->save_values() || empty($this->errors['general']['danger'])) {
						// we need to redirect for certain buttons
						$mvc = application::get('mvc');
						// save and new
						if (!empty($this->process_submit[self::button_submit_save_and_new])) {
							request::redirect($mvc['full']);
						}
						// save and close
						if (!empty($this->process_submit[self::button_submit_save_and_close])) {
							request::redirect($mvc['controller'] . '/_index');
						}
						// we reload form values
						goto load_values;
					} else {
						goto convert_multiple_columns;
					}
					*/
				}
			}
		}
		// adding general error
process_errors:
		if ($this->errors['flag_error_in_fields']) {
			$this->error('danger', object_content_messages::submission_problem);
		}
		if ($this->errors['flag_warning_in_fields']) {
			$this->error('warning', object_content_messages::submission_warning);
		}
		// if everything went ok
		if ($this->transaction) {
			if ($this->values_saved) { // we commit
				$this->collection_object->primary_model->db_object->commit();
			} else if (!$this->rollback) {
				$this->collection_object->primary_model->db_object->rollback();
			}
		}
load_values:
		if (!$this->has_errors()) {
			if ($this->values_deleted) { // we need to provide default values
				$this->values_loaded = false;
				$this->original_values = [];
				$this->get_all_values([]);
			} else if ($this->values_saved) { // if saved we need to reload from database
				$this->original_values = $this->values = $this->load_values();
				$this->values_loaded = true;
			} else if ($this->values_loaded) { // otherwise set loaded values
				$this->values = $this->original_values;
			}
		}
convert_multiple_columns:
		// convert multiple column to a form renderer can accept
		$this->convert_multiple_columns($this->values);
		// assuming save has been executed without errors we need to process on_success_js
		if (!$this->has_errors() && !empty($this->options['on_success_js'])) {
			layout::onload($this->options['on_success_js']);
		}
		// we need to hide buttons
		$this->validate_submit_buttons(['skip_validation' => true]);
	}

	/**
	 * Process navigation
	 *
	 * @param array $navigation
	 */
	private function process_navigation($navigation) {
		do {
			$column = key($navigation);
			if (empty($this->fields[$column]['options']['navigation'])) break;
			$navigation_type = key($navigation[$column]);
			if (empty($navigation_type) || !in_array($navigation_type, ['first', 'previous', 'refresh', 'next', 'last'])) break;
			// we need to process columns
			$navigation_columns = [$column];
			$navigation_depends = [];
			if (is_array($this->fields[$column]['options']['navigation'])) {
				if (!empty($this->fields[$column]['options']['navigation']['depends'])) {
					foreach ($this->fields[$column]['options']['navigation']['depends'] as $v) {
						$navigation_columns[] = $v;
						$navigation_depends[] = $v;
					}
				}
			}
			// get all values
			$this->get_all_values($this->options['input'] ?? [], [
				'validate_required' => false,
				'validate_for_delete' => false,
				'only_columns' => $navigation_columns
			]);
			// if we have errors we need to refresh
			if ($this->has_errors()) {
				$this->error_reset_all();
				$this->options['input'][$this::button_submit_refresh] = true;
				break;
			}
			$params = [
				'column_name' => $column,
				'column_value' => $this->values[$column],
				'depends' => []
			];
			foreach ($navigation_depends as $v) {
				$params['depends'][$v] = $this->values[$v];
			}
			$model = new numbers_frontend_html_form_model_datasource_navigation();
			$result = $model->get([
				'model' => $this->collection['model'],
				'type' => $navigation_type,
				'column' => $column,
				'pk' => $this->collection_object->data['pk'],
				'where' => $params
			]);
			// if we have data we override
			if (!empty($result[0])) {
				$this->options['input'] = $result[0];
			} else {
				if ($navigation_type == 'refresh') {
					$this->error('danger', object_content_messages::record_not_found, $column);
				} else {
					$this->error('danger', object_content_messages::prev_or_next_record_not_found, $column);
				}
				$this->options['input'][$this::button_submit_refresh] = true;
			}
		} while(0);
	}

	/**
	 * Convert multiple columns
	 */
	private function convert_multiple_columns(& $values) {
		foreach ($this->fields as $k => $v) {
			if (!empty($v['options']['multiple_column'])) {
				if (!empty($v['options']['detail_11'])) {
					$value_key = [$v['options']['detail_11'], $k];
				} else {
					$value_key = [$k];
				}
				$temp = array_key_get($values, $value_key);
				if (!empty($temp)) {
					array_key_set($values, $value_key, array_keys($temp));
				}
			}
		}
	}

	/**
	 * Validate submit buttons
	 *
	 * @param array $options
	 */
	public function validate_submit_buttons($options = []) {
		$buttons_found = [];
		$names = [];
		$have_batch_buttons = false;
		foreach ($this->data as $k => $v) {
			foreach ($v['rows'] as $k2 => $v2) {
				if ($k2 == $this::batch_buttons) {
					$have_batch_buttons = true;
				}
				// find all process submit buttons
				foreach ($v2['elements'] as $k3 => $v3) {
					if (!empty($v3['options']['process_submit'])) {
						if (!isset($buttons_found[$k3])) {
							$buttons_found[$k3] = [];
						}
						$buttons_found[$k3][] = [
							'name' => $v3['options']['value'],
							'key' => [$k, 'rows', $k2, 'elements', $k3]
						];
						$names[$k3] = $v3['options']['value'];
					}
				}
			}
		}
		// validations
		if ($have_batch_buttons) {
			// make a call to master object
			$result = $this->master_object->ledgers->{$this->master_options['ledger']}->__process_batch_buttons($this, [
				'key' => [$k, 'rows', $k2, 'elements'],
				'skip_validation' => $options['skip_validation'] ?? false
			]);
			$not_allowed = $result['not_allowed'];
			// todo: move it to master
			$also_set_save = [self::button_submit_delete];
		} else { // standard buttons
			$all_standard_buttons = [
				self::button_submit,
				self::button_submit_save,
				self::button_submit_save_and_new,
				self::button_submit_save_and_close,
				self::button_submit_reset,
				self::button_submit_delete
			];
			// process
			$not_allowed = [];
			// remove delete buttons if we do not have loaded values or do not have permission
			if (!$this->values_loaded || !object_controller::can('record_delete')) {
				$not_allowed[] = self::button_submit_delete;
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
				$not_allowed[] = self::button_submit_save;
				$not_allowed[] = self::button_submit_save_and_new;
				$not_allowed[] = self::button_submit_save_and_close;
			}
			// these buttons are considered save
			$also_set_save = [self::button_submit, self::button_submit_save_and_new, self::button_submit_save_and_close, self::button_submit_delete];
		}
		// validate if we have that button
		$result = true;
		foreach ($buttons_found as $k => $v) {
			if (empty($this->process_submit[$k])) {
				unset($this->process_submit[$k]);
			} else if (empty($buttons_found[$k]) || in_array($k, $not_allowed)) {
				// if we have validation
				if (empty($options['skip_validation'])) {
					$temp = i18n(null, 'Form action [action] is not allowed!', ['replace' => ['[action]' => i18n(null, $names[$k])]]);
					$this->error('danger', $temp, null, ['skip_i18n' => true]);
					$result = false;
				}
				unset($this->process_submit[$k]);
			}
			// hide it
			if (!empty($options['skip_validation'])) {
				if (!empty($buttons_found[$k]) && in_array($k, $not_allowed)) {
					foreach ($buttons_found[$k] as $v2) {
						// we disable buttons in test mode
						if (application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$temp = array_key_get($this->data, $v2['key']);
							$temp['options']['class'] = ($temp['options']['class'] ?? '') . ' disabled';
							array_key_set($this->data, $v2['key'], $temp);
						} else { // remove in regular mode
							array_key_get($this->data, $v2['key'], ['unset' => true]);
						}
					}
				}
			}
		}
		$this->submitted = !empty($this->process_submit);
		// fix for save
		foreach ($also_set_save as $v) {
			if (!empty($this->process_submit[$v])) {
				$this->process_submit[self::button_submit_save] = true;
			}
		}
		return $result;
	}

	/**
	 * Add error to tabs
	 *
	 * @param array $counters
	 *		type => number
	 */
	public function error_in_tabs($counters) {
		if (empty($this->current_tab) || empty($counters)) {
			return;
		}
		if (!isset($this->errors['tabs'])) {
			$this->errors['tabs'] = [];
		}
		// we need to process errors in a special way
		foreach ($counters as $type => $counter) {
			$current_tab = $this->current_tab;
			do {
				$key = implode('__', $current_tab) . '__' . $type;
				$current_value = array_key_get($this->errors['tabs'], $key);
				if (is_null($current_value)) {
					$current_value = 0;
				}
				array_key_set($this->errors['tabs'], $key, $current_value + $counter);
				array_pop($current_tab);
			} while (count($current_tab) > 0 && $type != 'records');
		}
	}

	/**
	 * Validate data type for single value
	 *
	 * @param string $k
	 * @param array $v
	 * @param mixed $in_value
	 * @param string $error_field
	 */
	final public function validate_data_types_single_value($k, $v, $in_value, $error_field = null) {
		// we set error field as main key
		if (empty($error_field)) {
			$error_field = $k;
		}
		// perform validation
		$data = object_table_columns::process_single_column_type($k, $v['options'], $in_value, ['process_datetime' => true]);
		if (array_key_exists($k, $data)) {
			// validations
			$error = false;
			$value = $in_value;
			// perform validation
			if ($v['options']['type'] == 'boolean') {
				if (!empty($value) && ($value . '' != $data[$k] . '')) {
					$this->error('danger', 'Wrong boolean value!', $error_field);
					$error = true;
				}
			} else if (in_array($v['options']['type'], ['date', 'time', 'datetime', 'timestamp'])) { // dates first
				if (!empty($value) && empty($data[$k . '_strtotime_value'])) {
					$this->error('danger', 'Invalid date, time or datetime!', $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'integer') {
				if (!empty($value) && ($data[$k] == 0 || ($value . '' !== $data[$k] . ''))) {
					$this->error('danger', 'Wrong integer value!', $error_field);
					$error = true;
				}
			} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
				if ($value . '' !== '' && !format::read_floatval($value, ['valid_check' => 1])) {
					$this->error('danger', 'Wrong numeric value!', $error_field);
					$error = true;
				}
				// precision & scale validations
				if (!$error) {
					// validate scale
					$digits = explode('.', $data[$k] . '');
					if (!empty($v['options']['scale'])) {
						if (!empty($digits[1]) && strlen($digits[1]) > $v['options']['scale']) {
							$this->error('danger', i18n(null, 'Only [digits] fraction digits allowed!', ['replace' => ['[digits]' => $v['options']['scale']]]), $error_field, ['skip_i18n' => true]);
							$error = true;
						}
					}
					// validate precision
					if (!empty($v['options']['precision'])) {
						$precision = $v['options']['precision'] - $v['options']['scale'] ?? 0;
						if (strlen($digits[0]) > $precision) {
							$this->error('danger', i18n(null, 'Only [digits] digits allowed!', ['replace' => ['[digits]' => $precision]]), $error_field, ['skip_i18n' => true]);
							$error = true;
						}
					}
				}
			} else if ($v['options']['php_type'] == 'float') { // regular floats
				if (!empty($value) && $data[$k] == 0) {
					$this->error('danger', 'Wrong float value!', $error_field);
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
						$this->error('danger', i18n(null, 'The length must be [length] characters!', ['replace' => ['[length]' => $v['options']['length']]]), $error_field, ['skip_i18n' => true]);
						$error = true;
					} else if (!empty($v['options']['length']) && strlen($data[$k]) > $v['options']['length']) { // varchar
						$this->error('danger', i18n(null, 'String is too long, should be no longer than [length]!', ['replace' => ['[length]' => $v['options']['length']]]), $error_field, ['skip_i18n' => true]);
						$error = true;
					}
				}
			}
			$data['flag_error'] = $error;
		} else if (!empty($data[$k . '_is_serial'])) {
			if ($in_value . '' !== '' && !empty($data[$k . '_is_serial_error'])) {
				$this->error('danger', 'Wrong sequence value!', $error_field);
				$data['flag_error'] = true;
			}
		} else {
			$this->error('danger', object_content_messages::unknown_value, $error_field);
			$data['flag_error'] = true;
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
		if (empty($this->collection_object)) {
			Throw new Exception('You must provide collection object!');
		}
		$options = [
			'flag_delete_row' => $this->process_submit[self::button_submit_delete] ?? false,
			'options_model' => $this->misc_settings['options_model'] ?? []
		];
		// we do not need to reload values from database because we locked them
		if ($this->values_loaded) {
			$options['original'] = $this->original_values;
		}
		$result = $this->collection_object->merge($this->values, $options);
		if (!$result['success']) {
			if (!empty($result['error'])) {
				foreach ($result['error'] as $v) {
					$this->error('danger', $v);
				}
			}
			if (!empty($result['warning'])) {
				foreach ($result['warning'] as $v) {
					$this->error('warning', $v);
				}
			}
			if (!empty($result['options_model'])) {
				foreach ($result['options_model'] as $k => $v) {
					$this->error('danger', object_content_messages::unknown_value, $k);
				}
				$this->error('danger', object_content_messages::submission_problem);
			}
			$this->rollback = true;
			return false;
		} else {
			if (!empty($result['deleted'])) {
				$this->error('success', object_content_messages::record_deleted);
				$this->values_deleted = true;
			} else if ($result['inserted']) {
				$this->error('success', object_content_messages::record_inserted);
				// we must put serial columns back into values
				if (!empty($result['new_serials'])) {
					$this->values = array_merge_hard($this->values, $result['new_serials']);
					$this->load_pk($this->values);
				}
			} else {
				$this->error('success', object_content_messages::recort_updated);
			}
			return true;
		}
	}

	/**
	 * Pre load collection object
	 *
	 * @return boolean
	 */
	final public function preload_collection_object() {
		if (empty($this->collection_object)) {
			$this->collection_object = object_collection::collection_to_model($this->collection);
			if (empty($this->collection_object)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Update collection object
	 */
	final public function update_collection_object() {
		$this->collection_object->data = array_merge_hard($this->collection_object->data, $this->collection);
	}

	/**
	 * Load primary key from values
	 */
	final public function load_pk(& $values) {
		$this->pk = [];
		$this->full_pk = true;
		if (!empty($this->collection_object)) {
			foreach ($this->collection_object->data['pk'] as $v) {
				if (isset($values[$v])) {
					$temp = object_table_columns::process_single_column_type($v, $this->collection_object->primary_model->columns[$v], $values[$v]);
					if (array_key_exists($v, $temp)) {
						$this->pk[$v] = $temp[$v];
					} else {
						$this->full_pk = false;
					}
				} else {
					$this->full_pk = false;
				}
			}
		} else {
			$this->full_pk = false;
		}
	}

	/**
	 * Load values from database
	 *
	 * @return mixed
	 */
	final public function load_values($for_update = false) {
		if ($this->full_pk) {
			return $this->collection_object->get(['where' => $this->pk, 'single_row' => true, 'for_update' => $for_update]);
		}
		return false;
	}

	/**
	 * Validate required fields
	 *
	 * @return boolean
	 */
	private function validate_required() {
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
								$this->error('danger', object_content_messages::required_field, $name);
							}
						} else if ($v['options']['php_type'] == 'bcnumeric') { // accounting numbers
							if (math::compare($value, '0') == 0) {
								$this->error('danger', object_content_messages::required_field, $name);
							}
						} else {
							if ($value . '' == '') {
								$this->error('danger', object_content_messages::required_field, $name);
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
											$this->error('danger', object_content_messages::required_field, $name);
										}
									} else if ($v22['options']['php_type'] == 'bcnumeric') { // accounting numbers
										if (math::compare($value, '0') == 0) {
											$this->error('danger', object_content_messages::required_field, $name);
										}
									} else {
										if ($value . '' == '') {
											$this->error('danger', object_content_messages::required_field, $name);
										}
									}
								}
							}
						}
					}
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
		if ($function == 'strtoupper' && strtoupper($value) !== $value) {
			$this->error('danger', object_content_messages::string_uppercase, $name);
		} else if ($function == 'strtolower' && strtolower($value) !== $value) {
			$this->error('danger', object_content_messages::string_lowercase, $name);
		} else if ($function($value) !== $value) {
			$this->error('danger', object_content_messages::string_function, $name);
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
			if ($type == 'danger') {
				$this->errors['flag_error_in_fields'] = true;
			}
			if ($type == 'warning') {
				$this->errors['flag_warning_in_fields'] = true;
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
	 * @param mixed $error_names
	 * @return boolean
	 */
	public function has_errors($error_names = null) {
		if (empty($error_names)) {
			return !empty($this->errors['flag_error_in_fields']) || !empty($this->errors['general']['danger']);
		} else {
			if (!is_array($error_names)) {
				$error_names = [$error_names];
			}
			foreach ($error_names as $v) {
				if (!empty($this->errors['fields'][$v]['danger'])) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Reset all error messages
	 */
	public function error_reset_all() {
		$this->errors = [
			'flag_error_in_fields' => false,
			'flag_warning_in_fields' => false,
		];
	}

	/**
	 * Process attributes
	 *
	 * @param array $options
	 * @param string $type
	 */
	/*
	private function process_attributes(& $options, $type) {
		if ($type == $this::attributes) {
			// see if model supports attributes
			if (!empty($this->collection_object->primary_model->attributes)) {
				$object = factory::model($this->collection_object->primary_model->attributes_model, true);
				$collection_key = [$this->collection_object->primary_model->attributes_model];
				$details_key = $this->collection_object->primary_model->attributes_model;
				$model_name = get_class($this->collection_object->primary_model);
			}
		} else if ($type == $this::attribute_details && !empty($options['details_parent_key'])) {
			$temp = factory::model($options['details_parent_key'], true);
			if ($temp->attributes) {
				$object = factory::model($temp->attributes_model, true);
				$collection_key = [$options['details_parent_key'], 'details', $temp->attributes_model];
				$details_key = $temp->attributes_model;
				$model_name = $options['details_parent_key'];
			}
		}
		// if we can continue
		if (empty($object)) {
			return false;
		}
		// generate collection object
		$collection = [
			'pk' => $object->pk,
			'type' => '1M',
			'map' => $object->attribute_map,
			'attributes' => true
		];
		array_key_set($this->collection['details'], $collection_key, $collection);
		// put everything into options
		$options['details_key'] = $details_key;
		$options['details_pk'] = $object->attribute_pk;
		$options['details_column_prefix'] = $object->column_prefix;
		$options['details_attribute_model'] = $model_name;
		// create containers
		if ($type == $this::attributes) {
			$options['details_attribute_model'] = $model_name;
			$this->container('__attributes_entries_container', [
				'label_name' => 'Attributes',
				'type' => 'details',
				'details_rendering_type' => 'table',
				'details_new_rows' => 5,
				'details_key' => $details_key,
				'details_pk' => $object->attribute_pk,
				'details_column_prefix' => $object->column_prefix,
				'order' => PHP_INT_MAX
			]);
			// link containers
			$this->element($options['container_link'], $this::attributes, '__attributes', ['container' => '__attributes_entries_container', 'order' => 1]);
			$this->process_attribute_elements('__attributes_entries_container', $options);
		}
		return true;
	}
	*/

	/**
	 * 
	 * @param string $container_link
	 * @param array $options
	 */
	/*
	private function process_attribute_elements($container_link, $options) {
		$this->element($container_link, 'row0', 'rn_attrdata_attrattr_id', [
			'order' => 1,
			'label_name' => 'Attribute',
			'domain' => 'group_id',
			'required' => true,
			'percent' => 35,
			'method'=> 'select',
			'options_model' => 'numbers_data_relations_model_datasource_attribute_availattrs::options_active',
			'options_params' => [
				'rn_attrmdl_code' => $options['details_attribute_model']
			]
		]);
	}
	*/

	/**
	 * Process widget
	 *
	 * @param array $options
	 * @return boolean
	 */
	private function process_widget($options) {
		$property = str_replace('__widget_', '', $options['row_link']);
		if (empty($this->collection_object->primary_model->{$property})) return false;
		$object = factory::model($this->collection_object->primary_model->{"{$property}_model"}, true);
		return $object->process_widget($this, $options);
	}

	/**
	 * Add container to the form
	 *
	 * @param string $container_link
	 * @param array $options
	 */
	public function container($container_link, $options = []) {
		if (!isset($this->data[$container_link])) {
			$options['container_link'] = $container_link;
			// make hidden container last
			if ($container_link == $this::hidden) {
				$options['order'] = PHP_INT_MAX - 1000;
			}
			// see if we have attributes
//			if (isset($options[$this::attribute_details])) {
//				$temp = $options[$this::attribute_details];
//				unset($options[$this::attribute_details]);
//				$options = array_merge_hard($temp, $options);
//				$options['attributes'] = true;
//				if (!$this->process_attributes($options, $this::attribute_details)) {
//					return;
//				}
//			}
			$type = $options['type'] ?? 'fields';
			// processing details
			if ($type == 'details') {
				if (empty($options['details_key']) || empty($options['details_pk'])) {
					Throw new Exception('Detail key or pk?');
				}
				$options['details_autoincrement'] = !empty($options['details_autoincrement']);
			}
			// processing subdetails
			if ($type == 'subdetails') {
				if (empty($options['details_key']) || empty($options['details_pk']) || empty($options['details_parent_key'])) {
					Throw new Exception('Subdetail key, parent key or pk?');
				}
				$options['flag_child'] = true;
				$options['details_autoincrement'] = !empty($options['details_autoincrement']);
			}
			$this->data[$container_link] = [
				'options' => $options,
				'order' => $options['order'] ?? 0,
				'type' => $type,
				'flag_child' => !empty($options['flag_child']),
				'default_row_type' => $options['default_row_type'] ?? 'grid',
				'rows' => [],
			];
			// special handling for details
			if ($type == 'details') {
				$model = factory::model($options['details_key'], true);
				// if we have relation
				if (!empty($model->relation['field']) && !in_array($model->relation['field'], $model->pk)) {
					$this->element($container_link, $this::hidden, $model->relation['field'], ['label_name' => 'Relation #', 'domain' => 'relation_id_sequence', 'method'=> 'hidden', 'persistent' => true]);
				}
			}
			if ($type == 'details' || $type == 'subdetails') {
				// if we have autoincrement
				if ($options['details_autoincrement']) {
					$model = factory::model($options['details_key'], true);
					foreach ($options['details_pk'] as $v) {
						$this->element($container_link, $this::hidden, $v, $model->columns[$v]);
					}
				}
			}
			// add attribute elements
//			if (!empty($options['attributes'])) {
//				$this->process_attribute_elements($container_link, $options);
//			}
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
		$this->container($container_link, array_key_extract_by_prefix($options, 'container_'));
		if (!isset($this->data[$container_link]['rows'][$row_link])) {
			// hidden rows
			if ($row_link == $this::hidden) {
				$options['order'] = PHP_INT_MAX - 1000;
			}
			// validating row type
			$types = object_html_form_row_types::get_static();
			if (!isset($options['type']) || !isset($types[$options['type']])) {
				$options['type'] = $this->data[$container_link]['default_row_type'];
			}
			$options['container_link'] = $container_link;
			$options['row_link'] = $row_link;
			// setting values
			$this->data[$container_link]['rows'][$row_link] = [
				'type' => $options['type'],
				'elements' => [],
				'options' => $options,
				'order' => $options['order'] ?? 0
			];
			// handling widgets
			if ($this->data[$container_link]['type'] == 'tabs') {
				if (in_array($row_link, $this::widgets)) {
					if (!$this->process_widget($options)) {
						unset($this->data[$container_link]['rows'][$row_link]);
					}
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
		if (in_array($row_link, [$this::buttons, $this::batch_buttons])) {
			$options['row_type'] = 'grid';
			if (!isset($options['row_order'])) {
				$options['row_order'] = PHP_INT_MAX - 500;
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
				if ($this->data[$container_link]['type'] == 'details' || $this->data[$container_link]['type'] == 'subdetails') { // details & subdetails
					$options['values_key'] = $options['error_name'] = $options['name'] = null;
					$options['id'] = null;
					$options['details_key'] = $this->data[$container_link]['options']['details_key'];
					$options['details_parent_key'] = $this->data[$container_link]['options']['details_parent_key'] ?? null;
					$options['details_field_name'] = $element_link;
				} else if (!empty($options['detail_11'])) { // detail 11
					$options['error_name'] = $options['name'] = $options['detail_11'] . '[' . $element_link . ']';
					$options['field_name'] = $element_link;
					$options['id'] = "form_{$this->form_link}_element_{$element_link}";
					$options['values_key'] = [$options['detail_11'], $element_link];
				} else { // regular fields
					$options['values_key'] = $options['error_name'] = $options['name'] = $element_link;
					$options['id'] = "form_{$this->form_link}_element_{$element_link}";
					// we do not validate preset fields
					if (!empty($options['preset'])) {
						$options['options_manual_validation'] = true;
						$options['tree'] = true;
					}
				}
				// process domain & type
				$temp = object_data_common::process_domains(['options' => $options]);
				$options = $temp['options'];
				$options['row_link'] = $row_link;
				$options['container_link'] = $container_link;
				// put data into fields array
				$field = [
					'id' => $options['id'],
					'name' => $options['name'],
					'options' => $options
				];
				// we need to put values into fields and details
				$persistent_key = [];
				if ($this->data[$container_link]['type'] == 'details') {
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
					// persistant
					$persistent_key[] = 'details';
					$persistent_key[] = $this->data[$container_link]['options']['details_key'];
					$persistent_key[] = $element_link;
				} else if ($this->data[$container_link]['type'] == 'subdetails') {
					$this->data[$container_link]['options']['container_link'] = $container_link;
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'elements', $element_link], $field);
					array_key_set($this->detail_fields, [$this->data[$container_link]['options']['details_parent_key'], 'subdetails', $this->data[$container_link]['options']['details_key'], 'options'], $this->data[$container_link]['options']);
					// details_unique_select
					if (!empty($field['options']['details_unique_select'])) {
						$this->misc_settings['details_unique_select'][$this->data[$container_link]['options']['details_parent_key'] . '::' . $this->data[$container_link]['options']['details_key']][$element_link] = [];
					}
					// todo: handle persistance
				} else {
					array_key_set($this->fields, $element_link, $field);
					$persistent_key[] = 'fields';
					$persistent_key[] = $element_link;
				}
				// persistent
				if (!empty($field['options']['persistent']) && !empty($persistent_key)) {
					array_key_set($this->misc_settings['persistent'], $persistent_key, 1);
				}
				// type is field by default
				$type = 'field';
				$container = null;
				// process submit elements
				if (!empty($options['process_submit'])) {
					$this->process_submit_all[$element_link] = false;
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
		$this->tabindex = 1;
		// css & js
		layout::add_js('/numbers/media_submodules/numbers_frontend_html_form_base.js', 9000);
		// load mask
		numbers_frontend_media_libraries_loadmask_base::add();
		// new record action
		$mvc = application::get('mvc');
		if (object_controller::can('record_new')) {
			$onclick = 'return confirm(\'' . strip_tags(i18n(null, object_content_messages::confirm_blank)) . '\');';
			$this->actions['form_new'] = ['value' => 'New', 'sort' => -31000, 'icon' => 'file-o', 'href' => $mvc['full'] . '?' . $this::button_submit_blank . '=1', 'onclick' => $onclick, 'internal_action' => true];
		}
		// back to list
		if (object_controller::can('list_view')) {
			$this->actions['form_back'] = ['value' => 'Back', 'sort' => -32000, 'icon' => 'arrow-left', 'href' => $mvc['controller'] . '/_index', 'internal_action' => true];
		}
		// reload button
		if ($this->values_loaded) {
			$url = $mvc['full'] . '?' . http_build_query2($this->pk);
			$this->actions['form_refresh'] = ['value' => 'Refresh', 'sort' => 32000, 'icon' => 'refresh', 'href' => $url, 'internal_action' => true];
		}
		// handling override_field_value method
		if (!empty($this->wrapper_methods['pre_render']['main'])) {
			call_user_func_array($this->wrapper_methods['pre_render']['main'], [& $this]);
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
					$tab_id = "form_tabs_{$this->form_link}_{$k}";
					$tab_header = [];
					$tab_values = [];
					$tab_options = [];
					$have_tabs = false;
					// sort rows
					array_key_sort($v['rows'], ['order' => SORT_ASC]);
					foreach ($v['rows'] as $k2 => $v2) {
						$this->current_tab[] = "{$tab_id}_{$k2}";
						$labels = '';
						foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
							$labels.= html::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '__' . $v78]);
						}
						$tab_header[$k2] = i18n(null, $v2['options']['label_name']) . $labels;
						$tab_values[$k2] = '';
						// handling override_tabs method
						if (!empty($this->wrapper_methods['override_tabs']['main'])) {
							$tab_options[$k2] = call_user_func_array($this->wrapper_methods['override_tabs']['main'], [& $this, & $v2, & $k2, & $this->values]);
							if (empty($tab_options[$k2]['hidden'])) {
								$have_tabs = true;
							}
						} else {
							$have_tabs = true;
						}
						// tab index for not hidden tabs
						if (empty($tab_options[$k2]['hidden'])) {
							$tab_options[$k2]['tabindex'] = $this->tabindex;
							$this->tabindex++;
						}
						// render containers
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
					// if we do not have tabs
					if ($have_tabs) {
						$result[$k]['html'] = html::tabs([
							'id' => $tab_id,
							'class' => 'form-tabs',
							'header' => $tab_header,
							'options' => $tab_values,
							'tab_options' => $tab_options
						]);
					}
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
		$result.= html::hidden(['name' => '__form_link', 'value' => $this->form_link]);
		$result.= html::hidden(['name' => '__form_values_loaded', 'value' => $this->values_loaded]);
		if (!empty($this->options['bypass_hidden_values'])) {
			foreach ($this->options['bypass_hidden_values'] as $k => $v) {
				$result.= html::hidden(['name' => $k, 'value' => $v]);
			}
		}
		// js to update counters in tabs
		if (!empty($this->errors['tabs'])) {
			foreach ($this->errors['tabs'] as $k => $v) {
				layout::onload("$('#{$k}').html($v); $('#{$k}').show();");
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
				'onsubmit' => empty($this->options['no_ajax_form_reload']) ? 'return numbers.form.on_form_submit(this);' : null
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
		$details_empty_warning_message = $this->data[$container_link]['options']['details_empty_warning_message'] ?? null;
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
		$result['data']['html'] = $this->render_container_type_details_rows($this->data[$container_link]['rows'], $data, ['details_key' => $key, 'new_rows' => $details_new_rows, 'empty_warning_message' => $details_empty_warning_message, 'details_rendering_type' => $this->data[$container_link]['options']['details_rendering_type'] ?? 'grid_with_label']);
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
		$details_empty_warning_message = $this->data[$container_link]['options']['details_empty_warning_message'] ?? null;
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
		$result['data']['html'] = $this->render_container_type_details_rows($this->data[$container_link]['rows'], $data, ['details_key' => $key, 'details_parent_key' => $parent_key, '__parent_row_number' => $options['__parent_row_number'], 'new_rows' => $details_new_rows, 'empty_warning_message' => $details_empty_warning_message, 'details_rendering_type' => $this->data[$container_link]['options']['details_rendering_type'] ?? 'grid_with_label']);
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
		// empty_warning_message
		if (empty($options['new_rows']) && empty($values) && isset($options['empty_warning_message'])) {
			if (empty($options['empty_warning_message'])) {
				return html::message(['type' => 'warning', 'options' => [object_content_messages::no_rows_found]]);
			} else {
				return html::message(['type' => 'warning', 'options' => [$options['empty_warning_message']]]);
			}
		}
		// building table
		$table = [
			'header' => [
				'row_number' => '',
				'row_data' => '',
				'row_delete' => ''
			],
			'options' => [],
			'skip_header' => true
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
						// hidden row
						if ($k === $this::hidden && !application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
						}
						$data['options'][$k][$k2][$k3] = [
							'label' => $this->render_element_name($first),
							'options' => $v3['options'],
							'row_class' => $v3['options']['row_class'] ?? null
						];
					}
				}
			}
			// add a row to a table
			$table['options']['__header'] = [
				'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
				'row_data' => ['value' => html::grid($data), 'width' => '98%'],
				'row_delete' => ['value' => '&nbsp;', 'width' => '1%'],
			];
		}
		// we must sort
		array_key_sort($rows, ['order' => SORT_ASC]);
		// generating rows
		$row_number = 1;
		$max_rows = count($values) + $options['new_rows'] ?? 0;
		$k0 = null;
		$v0 = [];
		$processing_values = !empty($values);
		do {
			// we exit if there's no rows and if we have no values
			if ($row_number > $max_rows) break;
			// render
			$data = [
				'options' => []
			];
			// grab next element from an array
			if ($processing_values) {
				$k0 = key($values);
				$v0 = current($values);
			} else {
				$k0 = $row_number;
				$v0 = [];
			}
			$i0 = $row_number;
			// we need to preset default values
			foreach ($rows as $k => $v) {
				foreach ($v['elements'] as $k2 => $v2) {
					if (array_key_exists('default', $v2['options']) && !isset($v0[$k2])) {
						$temp = $this->process_default_value($k2, $v2['options']['default'], $v0);
					}
				}
			}
			// looping though rows
			foreach ($rows as $k => $v) {
				// row_id
				if (empty($options['details_parent_key'])) {
					$row_id = "form_{$this->form_link}_details_{$options['details_key']}_{$row_number}_row";
				} else {
					$row_id = "form_{$this->form_link}_subdetails_{$options['details_parent_key']}_{$options['__parent_row_number']}_{$options['details_key']}_{$row_number}_row";
				}
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					if ($first_key == self::separator_horisontal) {
						$data['options'][$row_number . '_' . $k][$k2][0] = [
							'value' => html::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
							'separator' => true
						];
					} else {
						$first['prepend_to_field'] = ':';
						foreach ($v2 as $k3 => $v3) {
							// generate id, name and error name
							if (empty($options['details_parent_key'])) {
								$name = "{$options['details_key']}[{$i0}][{$k3}]";
								$id = "form_{$this->form_link}_details_{$options['details_key']}_{$row_number}_{$k3}";
								$error_name = "{$options['details_key']}[{$k0}][{$k3}]";
								$values_key = [$options['details_key'], $k0, $k3];
							} else {
								$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k0}][{$k3}]";
								$id = "form_{$this->form_link}_subdetails_{$options['details_parent_key']}_{$options['__parent_row_number']}_{$options['details_key']}_{$row_number}_{$k3}";
								$error_name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k0}][{$k3}]";
								// todo
								$values_key = [];
								//$values_key = [$options['details_key'], $k0, $k3];
							}
							// error
							$error = $this->get_field_errors([
								'options' => [
									'name' => $error_name
								]
							]);
							if (!empty($error['counters'])) {
								$this->error_in_tabs($error['counters']);
							}
							// hidden row
							$hidden = false;
							if ($k === $this::hidden && !application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
								$hidden = true;
							}
							// generate proper element
							$value_options = $v3;
							$value_options['options']['id'] = $id;
							$value_options['options']['name'] = $name;
							$value_options['options']['error_name'] = $error_name;
							$value_options['options']['details_parent_key'] = $options['details_parent_key'] ?? null;
							$value_options['options']['__parent_row_number'] = $options['__parent_row_number'] ?? null;
							$value_options['options']['__row_number'] = $row_number;
							// need to set values_key
							$value_options['options']['values_key'] = $values_key;
							// tabindex but not for subdetails
							if (!$hidden && empty($options['__parent_row_number'])) {
								$value_options['options']['tabindex'] = $this->tabindex;
								$this->tabindex++;
							}
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
								'row_class' => ($v3['options']['row_class'] ?? '') . (!($row_number % 2) ? ' grid_row_even' : ' grid_row_odd')
							];
						}
					}
				}
			}
			// increase counter
			if ($processing_values) {
				$this->error_in_tabs(['records' => 1]);
			}
			// subdetails
			if (!empty($this->detail_fields[$options['details_key']]['subdetails'])) {
				$tab_id = "form_tabs_{$this->form_link}_subdetails_{$options['details_key']}_{$row_number}";
				$tab_header = [
					'tabs_subdetails_none' => html::icon(['type' => 'toggle-on'])
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
					$this->current_tab[] = "{$tab_id}_{$k10}";
					$labels = '';
					foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
						$labels.= html::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->current_tab) . '__' . $v78]);
					}
					$tab_header[$k10] = i18n(null, $v10['options']['label_name']) . $labels;
					$tab_values[$k10] = '';
					// handling override_tabs method
					if (!empty($this->wrapper_methods['override_tabs']['main'])) {
						$tab_options[$k10] = call_user_func_array($this->wrapper_methods['override_tabs']['main'], [& $this, & $v10, & $k10, & $v0]);
						if (empty($tab_options[$k10]['hidden'])) {
							$have_tabs = true;
						}
					} else {
						$have_tabs = true;
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
					$tab_options['tabs_subdetails_none']['hidden'] = true;
				}
				$subdetails = html::tabs([
					'id' => $tab_id,
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
			// delete link
			$link = html::a(['href' => 'javascript:void(0);', 'value' => '<i class="fa fa-trash-o"></i>', 'onclick' => "if (confirm('" . strip_tags(i18n(null, object_content_messages::confirm_delete)) . "')) { numbers.form.details_delete_row('{$row_id}'); } return false;"]);
			// add a row to a table
			$table['options'][$row_number] = [
				'row_number' => ['value' => $row_number . '.', 'width' => '1%', 'row_id' => $row_id],
				'row_data' => ['value' => html::grid($data), 'width' => '98%'],
				'row_delete' => ['value' => $link, 'width' => '1%'],
			];
			$row_number++;
			// we need to determine if we have values
			if (next($values) === false) {
				$processing_values = false;
			}
		} while(1);
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
			if (in_array($v['key'], [$this::buttons, $this::batch_buttons])) {
				$buttons = [
					'left' => [],
					'center' => [],
					'right' => []
				];
				foreach ($v['value']['elements'] as $k2 => $v2) {
					$button_group = $v2['options']['button_group'] ?? 'left';
					if (!isset($buttons[$button_group])) {
						$buttons[$button_group] = [];
					}
					$v2['options']['tabindex'] = $this->tabindex;
					$this->tabindex++;
					$buttons[$button_group][] = $this->render_element_value($v2);
				}
				// render button groups
				foreach ($buttons as $k2 => $v2) {
					$value = implode(' ', $v2);
					if ($k2 != 'left') {
						$value = '<div style="text-align: ' . $k2 . ';">' . $value . '</div>';
					}
					$data['options'][$k][$v['key']][$k2] = [
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
				if ($first_key == self::separator_horisontal) {
					$data['options'][$k][$k2][0] = [
						'value' => html::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
						'separator' => true
					];
				} else {
					$first['prepend_to_field'] = ':';
					foreach ($v2 as $k3 => $v3) {
						// handling errors
						$error = $this->get_field_errors($v3);
						if (!empty($error['counters'])) {
							$this->error_in_tabs($error['counters']);
						}
						// hidden row
						$hidden = false;
						if ($v['key'] === $this::hidden && !application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
							$hidden = true;
						} else if ($v['key'] === $this::hidden) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden_testing';
						}
						// we do not show hidden fields
						if (($v3['options']['method'] ?? '') == 'hidden') {
							if (application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['method'] = 'input';
							} else {
								$v3['options']['style'] = ($v3['options']['style'] ?? '') . 'display: none;';
								$hidden = true;
							}
						}
						if (!$hidden) {
							$v3['options']['tabindex'] = $this->tabindex;
							$this->tabindex++;
						}
						// processing value and neighbouring_values
						if (!empty($v3['options']['detail_11'])) {
							$neighbouring_values = & $this->values[$v3['options']['detail_11']];
						} else {
							$neighbouring_values = & $this->values;
						}
						$value = array_key_get($this->values, $v3['options']['values_key']);
						$data['options'][$k][$k2][$k3] = [
							'error' => $error,
							'label' => $this->render_element_name($first),
							'value' => $this->render_element_value($v3, $value, $neighbouring_values),
							'description' => $v3['options']['description'] ?? null,
							'options' => $v3['options'],
							'row_class' => $v3['options']['row_class'] ?? null
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
				'counters' => []
			];
			$sorted = [
				'danger' => [],
				'warning' => [],
				'success' => [],
				'info' => []
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
					$result['counters'][$k] = ($result[$k] ?? 0) + 1;
					$sorted[$k][$k2] = $v2;
				}
			}
			foreach ($sorted as $k => $v) {
				if (empty($v)) continue;
				foreach ($v as $k2 => $v2) {
					$result['message'].= html::text(['tag' => 'div', 'type' => $k, 'value' => $v2]);
				}
			}
			return $result;
		}
		return null;
	}

	/**
	 * Render table rows
	 *
	 * @param array $rows
	 * @return type
	 */
	public function render_row_table($rows) {

		// todo
		Throw new Exception('todo: make the same as render_row_grid!');

		/*
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
		*/
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
				if ($options['options']['required'] === true || $options['options']['required'] === '1' || $options['options']['required'] === 1) {
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
	 * Process depends and params
	 *
	 * @param array $params
	 * @param array $neighbouring_values
	 * @param array $options
	 * @param boolean $flag_params
	 */
	private function process_params_and_depends(& $params, & $neighbouring_values, $options, $flag_params = true) {
		foreach ($params as $k => $v) {
			// if we have a parent
			if (strpos($v, 'parent::') !== false) {
				$field = str_replace(['parent::', 'static::'], '', $v);
				if (!empty($this->errors['fields'][$field]['danger'])) {
					$params[$k] = null;
				} else {
					$params[$k] = $this->values[$field] ?? null;
				}
			} else if ($flag_params) {
				// todo process errors   
				$params[$k] = $neighbouring_values[$v] ?? null;
			}
		}
	}

	/**
	 * Process default value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @param array $neighbouring_values
	 * @return mixed
	 */
	private function process_default_value($key, $default, & $neighbouring_values = null) {
		if (strpos($default, 'parent::') !== false) {
			$field = str_replace(['parent::', 'static::'], '', $default);
			$value = $this->values[$field] ?? null;
		} else {
			$value = $default;
		}
		if (isset($neighbouring_values)) {
			array_key_set($neighbouring_values, $key, $value);
		}
		return $value;
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
	public function render_element_value(& $options, $value = null, & $neighbouring_values = []) {
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
		$flag_select_or_autocomplete = !empty($result_options['options_model']) || !empty($result_options['options']);
		if (!empty($result_options['options_model'])) {
			if (empty($result_options['options_params'])) {
				$result_options['options_params'] = [];
			}
			if (empty($result_options['options_options'])) {
				$result_options['options_options'] = [];
			}
			$result_options['options_options']['i18n'] = true;
			if (empty($result_options['options_depends'])) {
				$result_options['options_depends'] = [];
			}
			// options depends & params
			$this->process_params_and_depends($result_options['options_depends'], $neighbouring_values, $options, true);
			$this->process_params_and_depends($result_options['options_params'], $neighbouring_values, $options, false);
			$result_options['options_params'] = array_merge_hard($result_options['options_params'], $result_options['options_depends']);
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
				$result_options['options'] = object_data_common::process_options($result_options['options_model'], $this, $result_options['options_params'], $value, $skip_values, $result_options['options_options']);
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
							$result_options['onclick'].= 'numbers.form.trigger_submit_on_button(this); return true;';
						} else {
							$result_options['onclick'] = 'numbers.form.trigger_submit_on_button(this); ' . $result_options['onclick'];
						}
					}
					$flag_translated = true;
					// icon
					if (!empty($result_options['icon'])) {
						$result_options['value'] = html::icon(['type' => $result_options['icon']]) . ' ' . $result_options['value'];
					}
					// accesskey
					if (isset($result_options['accesskey'])) {
						$accesskey = explode('::', i18n(null, 'accesskey::' . $result_options['name'] . '::' . $result_options['accesskey']));
						$result_options['accesskey'] = $accesskey[2];
						$result_options['title'] = ($result_options['title'] ?? '') . ' ' . i18n(null, 'Shortcut Key: ') . $accesskey[2];
					}
				} else if (in_array($element_method, ['html::div', 'html::span'])) {
					if (!empty($result_options['i18n'])) {
						$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value'] ?? null);
						$flag_translated = true;
					}
				} else {
					// editable fields
					$result_options['value'] = $value;
					// if we need to empty value, mostly for password fields
					if (!empty($result_options['empty_value'])) {
						$result_options['value'] = '';
					}
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
					// processing persistent
					$old_value = array_key_get($this->original_values, $result_options['values_key']);
					if (!empty($result_options['persistent']) && !empty($old_value)) {
						$result_options['readonly'] = true;
					}
					// maxlength
					if (in_array($result_options['type'] ?? '', ['char', 'varchar']) && !empty($result_options['length'])) {
						$result_options['maxlength'] = $result_options['length'];
					}
					// global readonly
					if (!empty($this->misc_settings['global']['readonly']) && empty($result_options['navigation'])) {
						$result_options['readonly'] = true;
					}
				}
				// translate place holder
				if (array_key_exists('placeholder', $result_options)) {
					if (!empty($result_options['placeholder'])) {
						$result_options['placeholder'] = strip_tags(i18n(null, $result_options['placeholder']));
					}
				} else if (!empty($result_options['validator_method']) && empty($result_options['value'])) {
					$temp = object_validator_base::method($result_options['validator_method'], $result_options['value'], $result_options['validator_params'] ?? [], $options['options'], $neighbouring_values);
					if ($flag_select_or_autocomplete) {
						$placeholder = $temp['placeholder_select'];
					} else {
						$placeholder = $temp['placeholder'];
					}
					if (!empty($placeholder)) {
						$result_options['placeholder'] = strip_tags(i18n(null, $placeholder));
					}
				}
				// events
				foreach (['onchange'] as $e) {
					if (!empty($result_options[$e])) {
						$result_options[$e] = str_replace('this.form.submit();', "numbers.form.trigger_submit(this.form);", $result_options[$e]);
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
			// todo: unset non html attributes
			$value = $field_method_object->{$method[1]}($result_options);
			// building navigation
			if (!empty($result_options['navigation'])) {
				$name = 'navigation[' . $result_options['name'] . ']';
				$temp = '<table width="100%">';
					$temp.= '<tr>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[first]', 'value' => html::icon(['type' => 'step-backward']), 'onclick' => 'numbers.form.trigger_submit_on_button(this);']) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[previous]', 'value' => html::icon(['type' => 'caret-left']), 'onclick' => 'numbers.form.trigger_submit_on_button(this);']) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="90%">' . $value . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[refresh]', 'value' => html::icon(['type' => 'refresh']), 'onclick' => 'numbers.form.trigger_submit_on_button(this);']) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[next]', 'value' => html::icon(['type' => 'caret-right']), 'onclick' => 'numbers.form.trigger_submit_on_button(this);']) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . html::button2(['name' => $name . '[last]', 'value' => html::icon(['type' => 'step-forward']), 'onclick' => 'numbers.form.trigger_submit_on_button(this);']) . '</td>';
					$temp.= '</tr>';
				$temp.= '</table>';
				$value = $temp;
			}
		}
		// if we need to display settings
		if (application::get('flag.numbers.frontend.html.form.show_field_settings')) {
			$id_original = $result_options['id'] . '__settings_original';
			$id_modified = $result_options['id'] . '__settings_modified';
			$value.= html::a(['href' => 'javascript:void(0);', 'onclick' => "$('#{$id_original}').toggle();", 'value' => html::label2(['type' => 'primary', 'value' => count($options['options'])])]);
			$value.= html::a(['href' => 'javascript:void(0);', 'onclick' => "$('#{$id_modified}').toggle();", 'value' => html::label2(['type' => 'warning', 'value' => count($result_options)])]);
			$value.= '<div id="' . $id_original . '" style="display:none; position: absolute; text-align: left; width: 500px; z-index: 32000;">' . print_r2($options['options'], true) . '</div>';
			$value.= '<div id="' . $id_modified . '" style="display:none; position: absolute; text-align: left; width: 500px; z-index: 32000;">' . print_r2($result_options, true) . '</div>';
		}
		return $value;
	}
}