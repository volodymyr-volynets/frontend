<?php

class numbers_frontend_html_form_report {

	/**
	 * Form object
	 *
	 * @var object
	 */
	private $form_object;

	/**
	 * Db object
	 *
	 * @var object
	 */
	public $db_object;

	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Even/Odd/Blank
	 */
	const odd = 1;
	const even = 2;
	const blank = 0;

	/**
	 * Constructor
	 *
	 * @param object $form_object
	 */
	public function __construct(& $form_object) {
		$this->form_object = $form_object;
	}

	/**
	 * Initialize
	 *
	 * @param array $options
	 */
	public function initialize(& $form, $options = []) {
		$this->options = $options;
		$this->options['format'] = $options['format'] ?? $form->values['format'] ?? '';
		$this->options['formats'] = factory::model('numbers_frontend_html_form_model_formats', true)->get();
		if (empty($this->options['formats'][$this->options['format']])) $this->options['format'] = 'screen';
		$this->options['i18n'] = $this->options['i18n'] ?? true;
		// db object
		if (!empty($form->collection_object->primary_model)) {
			$this->db_object = & $form->collection_object->primary_model->db_object;
		} else {
			$this->db_object = new db();
		}
		// escaped values
		$form->escaped_values = $this->db_object->escape_array($form->values);
	}

	/**
	 * Header
	 *
	 * @param string $link
	 * @param string $row_id
	 * @param array $data
	 * @param array $options
	 */
	public function header($link, $row_id, $data, $options = []) {
		// process columns
		$index = 0;
		foreach ($data as $k => $v) {
			$data[$k]['data'] = $v['data'] ?? [];
			// process domain & type
			if (isset($v['data']['domain']) || isset($v['data']['type'])) {
				$temp = object_data_common::process_domains_and_types(['options' => $v['data']]);
				$data[$k]['data'] = $temp['options'];
			}
			$data[$k]['index'] = $index;
			$data[$k]['align'] = $data[$k]['align'] ?? 'left';
			$index++;
		}
		$this->data[$link]['header'][$row_id] = [
			'row_id' => $row_id,
			'data' => & $data,
			'options' => $options
		];
	}

	/**
	 * Row
	 *
	 * @param string $link
	 * @param string $row_id
	 * @param array $data
	 * @param int $even
	 */
	public function row($link, $row_id, $data, $even = null) {
		if (!isset($this->data[$link]['rows'])) {
			$this->data[$link]['rows'] = [];
		}
		// convert columns
		$columns = $this->data[$link]['header'][$row_id]['data'];
		$temp = [];
		foreach ($columns as $k => $v) {
			if (isset($data[$k]) && is_array($data[$k])) {
				$merged = array_merge_hard($v['data'], $data[$k]);
				// format values
				if (!empty($merged['format'])) {
					$merged['format_options'] = $merged['format_options'] ?? [];
					$method = factory::method($merged['format'], 'format');
					$data[$k]['value'] = call_user_func_array([$method[0], $method[1]], [$merged['value'], $merged['format_options']]);
				}
			}
			$temp[$v['index']] = $data[$k] ?? null;
		}
		// process odd/even/blank
		if (!isset($even)) {
			$even = $this->even();
		}
		// add data to an array
		$this->data[$link]['rows'][] = [
			'row_id' => $row_id,
			'even' => $even,
			'data' => $temp
		];
	}

	/**
	 * Status
	 *
	 * @param string $link
	 * @param string $row_id
	 * @param mixed $quantity
	 * @param string $description
	 */
	public function status($link, $row_id, $quantity, $description = null) {
		$first_column = key($this->data[$link]['header'][$row_id]['data']);
		if (!isset($description)) $description = 'rows';
		if ($this->options['i18n']) {
			$quantity = i18n(null, $quantity);
			$description = i18n(null, $description);
		}
		$value = $quantity . ' ' . $description;
		$this->row($link, $row_id, [
			$first_column => ['value' => $value, 'bold' => true, 'global_class' => 'numbers_frontend_form_report_screen_table_global_row_header_first', 'colspan' => count($this->data[$link]['header'][$row_id]['data'])]
		], self::blank);
	}

	/**
	 * Legend
	 *
	 * @param string $name
	 * @param array $data
	 * @param string $type
	 *		string horizontal
	 *		string vertical
	 */
	public function legend($name, $data, $type = 'horizontal') {
		$link = 'legend_' . random_int(100, 999);
		if ($this->options['i18n']) {
			$name = i18n(null, $name);
		}
		// add header
		$this->header($link, 'row1', [
			'name' => ['value' => '', 'width' => '100%']
		], ['do_not_render' => true]);
		$this->row($link, 'row1', [
			'name' => ['value' => $name ,'bold' => true]
		]);
		// transform data
		$result = [];
		foreach ($data as $k => $v) {
			if ($this->options['i18n']) {
				$v = i18n(null, $v);
			}
			if (!is_int($k)) {
				$v = $k . ' - ' . $v;
			}
			if ($type == 'horizontal') {
				if (!isset($result[0])) {
					$result[0] = [];
				}
				$result[0][] = $v;
			} else {
				$result[] = $v;
			}
		}
		if ($type == 'horizontal') {
			$result[0] = implode(', ', $result[0]);
		}
		// add rows
		foreach ($result as $v) {
			$this->row($link, 'row1', [
				'name' => ['value' => $v, 'blank' => true]
			], self::blank);
		}
	}

	/**
	 * Process even
	 *
	 * @param int $even
	 * @return int
	 */
	public function even($even = null) {
		if (isset($even)) {
			$this->options['even'] = $even;
		} else {
			if (!isset($this->options['even'])) {
				$this->options['even'] = self::odd;
			} else {
				if ($this->options['even'] == self::odd) {
					 $this->options['even'] = self::even;
				} else {
					$this->options['even'] = self::odd;
				}
			}
		}
		return $this->options['even'];
	}

	/**
	 * Separator
	 *
	 * @param string $link
	 * @param array $data
	 */
	public function separator($link, $row_id) {
		$this->data[$link]['rows'][] = [
			'row_id' => $row_id,
			'data' => [
				['value' => ' ']
			]
		];
	}

	/**
	 * Render
	 */
	public function render() {
		if (!empty($this->options['formats'][$this->options['format']]['custom_renderer'])) {
			return factory::model($this->options['formats'][$this->options['format']]['custom_renderer'])->render($this);
		} else {
			Throw new Exception('Report: renderer is not implemented!');
		}
	}
}