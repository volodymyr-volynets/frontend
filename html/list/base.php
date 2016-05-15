<?php

class numbers_frontend_html_list_base {

	/**
	 * List link
	 *
	 * @var string
	 */
	public $list_link;

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Columns
	 *
	 * @var array
	 */
	public $columns = [];

	/**
	 * Number of records per page
	 *
	 * @var int
	 */
	public $limit = 30;

	/**
	 * Offset
	 *
	 * @var int
	 */
	public $offset = 0;

	/**
	 * Total rows
	 *
	 * @var int
	 */
	public $total = 0;

	/**
	 * Number of rows fetched
	 *
	 * @var int
	 */
	public $num_rows = 0; 

	/**
	 * Rows
	 *
	 * @var array
	 */
	public $rows = [];

	/**
	 * Page sizes
	 *
	 * @var array
	 */
	public $page_sizes = [
		20 => ['name' => 20],
		30 => ['name' => 30],
		50 => ['name' => 50],
		100 => ['name' => 100],
		250 => ['name' => 250],
		500 => ['name' => 500]
	];

	/**
	 * Model
	 *
	 * @var string
	 */
	public $model;
	
	/**
	 * Datasources
	 *
	 * @var array 
	 */
	public $datasources = [
		'count' => null, // count datasource
		'data' => null // data datasource
	];

	/**
	 * Pagination class
	 *
	 * @var array
	 */
	public $pagination = [
		'top' => null, // top pagination class
		'bottom' => null, // bottom pagination class
	];

	/**
	 * Order by
	 *
	 * @var array
	 */
	public $orderby = [];

	/**
	 * Filter
	 *
	 * @var array
	 */
	public $filter = [];

	/**
	 * Constructor
	 *
	 * @param string $list_link
	 * @param array $options
	 */
	public function __construct($options = []) {
		$this->options = $options;
		// processing model
		if (empty($this->columns) && !empty($this->model)) {
			$model_class = $this->model;
			$model_object = new $model_class();
			$this->columns = $model_object->columns;
		}
		// check if we have columns
		if (empty($this->columns)) {
			Throw new Exception('List must have columns!');
		}
		// limit
		$limit = intval($options['input']['limit'] ?? 0);
		if ($limit > 0) {
			$this->limit = $limit;
		}
		// offset
		$offset = intval($options['input']['offset'] ?? 0);
		if ($offset > 0) {
			$this->offset = $offset;
		}
		// datasources, count first
		if (empty($this->datasources['count']) && !empty($this->model)) {
			$this->datasources['count'] = [
				'model' => 'numbers_frontend_html_list_model_datasource_count',
				'options' => [
					'model' => $this->model,
					// todo: add where
				]
			];
		} else if (!empty($this->datasources['count'])) {
			$this->datasources['count'] = [
				'model' => $this->datasources['count'],
				'options' => [
					// todo: add where
				]
			];
		}
		// datasources, data second
		if (empty($this->datasources['data']) && !empty($this->model)) {
			$this->datasources['data'] = [
				'model' => 'numbers_frontend_html_list_model_datasource_data',
				'options' => [
					'model' => $this->model,
					'offset' => $this->offset,
					'limit' => $this->limit,
					'orderby' => $this->orderby
					// todo: add where
				]
			];
		} else if (!empty($this->datasources['data'])) {
			$this->datasources['data'] = [
				'model' => $this->datasources['data'],
				'options' => [
					'offset' => $this->offset,
					'limit' => $this->limit,
					'orderby' => $this->orderby
					// todo: add where
				]
			];
		}
	}

	/**
	 * Render list
	 *
	 * @return string
	 */
	final public function render() {
		$result = '';
		// hidden fields
		$result.= html::hidden(['name' => 'offset', 'id' => 'offset', 'value' => $this->offset]);
		$result.= html::hidden(['name' => 'limit', 'id' => 'limit', 'value' => $this->limit]);
		// filter
		if (!empty($this->filter)) {
			layout::add_action('list_filter', ['value' => 'Filter', 'orderby' => 1, 'icon' => 'filter', 'onclick' => "$('#list_filter').modal('show');"]);
			$result.= numbers_frontend_html_list_filter::render($this);
		}
		// get total number of rows from count datasource
		if (!empty($this->datasources['count'])) {
			$class = $this->datasources['count']['model'];
			$object = new $class();
			$temp = $object->get($this->datasources['count']['options']);
			$this->total = intval($temp[0]['count'] ?? 0);
		}
		// get rows
		if (!empty($this->datasources['data'])) {
			$class = $this->datasources['data']['model'];
			$object = new $class();
			$this->rows = $object->get($this->datasources['data']['options']);
			$this->num_rows = count($this->rows);
		}
		// pagination top
		if (!empty($this->pagination['top'])) {
			$class = $this->pagination['top'];
			$object = new $class();
			$result.= $object->render($this, 'top');
		}
		// data
		$result.= '<hr/>';
		if (method_exists($this, 'render_data')) {
			$result.= $this->render_data();
		} else {
			$result.= $this->render_data_default();
		}
		$result.= '<hr/>';
		// pagination bottom
		if (!empty($this->pagination['bottom'])) {
			$class = $this->pagination['bottom'];
			$object = new $class();
			$result.= $object->render($this, 'bottom');
		}
		return html::form(['name' => 'list', 'value' => $result]);
	}

	/**
	 * Data default renderer
	 *
	 * @return string
	 */
	final private function render_data_default() {
		$result = '';
		// if we have no rows we display a messsage
		if ($this->num_rows == 0) {
			return html::message(['type' => 'warning', 'options' => ['No rows found!']]);
		}
		// process options_models
		foreach ($this->columns as $k => $v) {
			if (!empty($v['options_model'])) {
				$class = $v['options_model'];
				$object = new $class();
				$this->columns[$k]['options'] = $object->options();
			}
		}
		$counter = 1;
		$table = [
			'header' => [],
			'options' => []
		];
		// generate columns
		foreach ($this->columns as $k => $v) {
			$table['header'][$k] = ['value' => $v['name'], 'width' => $v['width'] ?? null];
		}
		// generate rows
		foreach ($this->rows as $k => $v) {
			// process all columns first
			$row = [];
			foreach ($this->columns as $k2 => $v2) {
				$value = [];
				// create cell properties
				foreach (['width', 'align'] as $v3) {
					if (isset($v2[$v3])) {
						$value[$v3] = $v2[$v3];
					}
				}
				// process rows
				if ($k2 == 'row_number') {
					$value['value'] = $counter . '.';
				} else if ($k2 == 'offset_number') {
					$value['value'] = ($this->offset + $counter) . '.';
				} else if (!empty($v2['options']) && !is_array($v[$k2])) {
					$value['value'] = $v2['options'][$v[$k2]]['name'];
				} else if (isset($v[$k2])) {
					$value['value'] = $v[$k2];
				} else {
					$value['value'] = null;
				}
				// put value into row
				$row[$k2] = $value;
			}
			// put processed columns though user defined function
			if (method_exists($this, 'render_data_rows')) {
				$table['options'][$counter] = $this->render_data_rows($row, $v);
			} else {
				$table['options'][$counter] = $row;
			}
			$counter++;
		}
		return html::table($table);
	}
}