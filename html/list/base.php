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
		//1 => ['name' => 1],
		10 => ['name' => 10],
		20 => ['name' => 20],
		30 => ['name' => 30],
		50 => ['name' => 50],
		100 => ['name' => 100],
		250 => ['name' => 250],
		500 => ['name' => 500],
		PHP_INT_MAX => ['name' => 'All']
	];

	/**
	 * Model
	 *
	 * @var string
	 */
	public $model;

	/**
	 * Model object
	 *
	 * @var object
	 */
	private $model_object;
	
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
	 * Whether filter has been set
	 *
	 * @var boolean
	 */
	public $filtered = false;

	/**
	 * Actions
	 *
	 * @var array
	 */
	public $actions = [];

	/**
	 * Constructor
	 *
	 * @param string $list_link
	 * @param array $options
	 */
	public function __construct($options = []) {
		$this->options = $options;
		// processing model
		if (!empty($this->model)) {
			$this->model_object = factory::model($this->model);
		}
		if (empty($this->columns) && !empty($this->model)) {
			$this->columns = $this->model_object->columns;
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
		// we need to set maximum limit if we are exporting
		if (!empty($this->options['input']['submit_export']) && !empty($this->options['input']['export']['format'])) {
			$this->limit = PHP_INT_MAX;
		}
		// offset
		$offset = intval($options['input']['offset'] ?? 0);
		if ($offset > 0) {
			$this->offset = $offset;
		}
		// filter
		$where = [];
		if (!empty($this->options['input']['filter'])) {
			$where = numbers_frontend_html_list_filter::where($this);
			if (!empty($where)) {
				$this->filtered = true;
			}
		}
		// sort
		if (!empty($this->options['input']['sort'])) {
			$this->orderby = [];
			foreach ($this->options['input']['sort'] as $k => $v) {
				if (!empty($v['column']) && !empty($this->columns[$v['column']])) {
					$this->orderby[$v['column']] = $v['order'] ?? SORT_ASC;
				} else if (!empty($v['column']) && $v['column'] == 'full_text_search' && !empty($this->filter['full_text_search'])) {
					$this->orderby['full_text_search'] = $v['order'] ?? SORT_ASC;
				}
			}
		}
		// datasources, count first
		if (empty($this->datasources['count']) && !empty($this->model)) {
			$this->datasources['count'] = [
				'model' => 'numbers_frontend_html_list_model_datasource_count',
				'options' => [
					'model' => $this->model,
					'where' => $where
				]
			];
		} else if (!empty($this->datasources['count'])) {
			$this->datasources['count'] = [
				'model' => $this->datasources['count'],
				'options' => [
					'where' => $where
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
					'orderby' => $this->orderby,
					'where' => $where
				]
			];
		} else if (!empty($this->datasources['data'])) {
			$this->datasources['data'] = [
				'model' => $this->datasources['data'],
				'options' => [
					'offset' => $this->offset,
					'limit' => $this->limit,
					'orderby' => $this->orderby,
					'where' => $where
				]
			];
		}
		// process options model
		foreach ($this->columns as $k => $v) {
			if (!empty($v['options_model'])) {
				$this->columns[$k]['options'] = factory::model($v['options_model'])->options();
			}
		}
		// actions
		if (!empty($this->options['actions'])) {
			$this->actions = array_merge($this->actions, $this->options['actions']);
		}
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
	 * Render list
	 *
	 * @return string
	 */
	final public function render() {
		$result = '';
		// css & js
		layout::add_css('/numbers/media_submodules/numbers_frontend_html_list_fixes.css', 9000);
		layout::add_js('/numbers/media_submodules/numbers_frontend_html_list_base.js', 9000);
		// load mask
		numbers_frontend_media_libraries_loadmask_base::add();
		// hidden fields
		$result.= html::hidden(['name' => 'offset', 'id' => 'offset', 'value' => $this->offset]);
		$result.= html::hidden(['name' => 'limit', 'id' => 'limit', 'value' => $this->limit]);
		// get total number of rows from count datasource
		if (!empty($this->datasources['count'])) {
			$temp = factory::model($this->datasources['count']['model'])->get($this->datasources['count']['options']);
			$this->total = $temp[0]['count'] ?? 0;
		}
		// get rows
		if (!empty($this->datasources['data'])) {
			$this->rows = factory::model($this->datasources['data']['model'])->get($this->datasources['data']['options']);
			$this->num_rows = count($this->rows);
		}
		// new record
		if (object_controller::can('record_new')) {
			$mvc = application::get('mvc');
			$url = $mvc['controller'] . '/_edit';
			$this->actions['list_new'] = ['value' => 'New', 'sort' => -32000, 'icon' => 'file-o', 'href' => $url];
		}
		// filter
		if (!empty($this->filter)) {
			$this->actions['list_filter'] = ['value' => 'Filter', 'sort' => 1, 'icon' => 'filter', 'onclick' => "numbers.modal.show('list_{$this->list_link}_filter');"];
			$result.= numbers_frontend_html_list_filter::render($this);
		}
		// order by
		$this->actions['list_sort'] = ['value' => 'Sort', 'sort' => 2, 'icon' => 'sort-alpha-asc', 'onclick' => "numbers.modal.show('list_{$this->list_link}_sort');"];
		$result.= numbers_frontend_html_list_sort::render($this);
		// export, before pagination
		if (object_controller::can('list_export')) {
			// add export link to the panel
			$result.= numbers_frontend_html_list_export::render($this);
			$this->actions['list_export'] = ['value' => 'Export/Print', 'sort' => 3, 'icon' => 'print', 'onclick' => "numbers.modal.show('list_{$this->list_link}_export');"];
			// if we are exporting
			if (!empty($this->options['input']['submit_export']) && !empty($this->options['input']['export']['format'])) {
				$result.= numbers_frontend_html_list_export::export($this, $this->options['input']['export']['format']);
				goto finish;
			}
		}
		// pagination top
		if (!empty($this->pagination['top'])) {
			$result.= factory::model($this->pagination['top'])->render($this, 'top');
		}
		// data
		$result.= '<hr class="simple"/>';
		if (method_exists($this, 'render_data')) {
			$result.= $this->render_data();
		} else {
			$result.= $this->render_data_default();
		}
		$result.= '<hr class="simple"/>';
		// pagination bottom
		if (!empty($this->pagination['bottom'])) {
			$result.= factory::model($this->pagination['bottom'])->render($this, 'bottom');
		}
finish:
		$value = '';
		if (!empty($this->actions)) {
			$value.= '<div style="text-align: right;">' . $this->render_actions() . '</div>';
			$value.= '<hr class="simple" />';
		}
		// we add hidden submit element
		$result.= html::submit(['name' => 'submit_hidden' , 'value' => 1, 'style' => 'display: none;']);
		// build a form
		$value.= html::form([
			'name' => "list_{$this->list_link}_form",
			'id' => "list_{$this->list_link}_form",
			'value' => $result,
			'onsubmit' => 'return numbers.frontend_list.on_form_submit(this);'
		]);
		// if we came from ajax we return as json object
		if (!empty($this->options['input']['__ajax'])) {
			$result = [
				'success' => true,
				'html' => $value,
				'js' => layout::$onload
			];
			layout::render_as($result, 'application/json');
		}
		$value = "<div id=\"list_{$this->list_link}_form_mask\"><div id=\"list_{$this->list_link}_form_wrapper\">" . $value . '</div></div>';
		$temp = [
			'type' => 'primary',
			'value' => $value
		];
		return html::segment($temp);
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
			return html::message(['type' => 'warning', 'options' => [i18n(null, object_content_messages::$no_rows_found)]]);
		}
		$counter = 1;
		$table = [
			'header' => [],
			'options' => []
		];
		// action flags
		$actions = [];
		if (object_controller::can('record_view')) {
			$actions['view'] = true;
		}
		// generate columns
		foreach ($this->columns as $k => $v) {
			// if we can not view we skip action column
			if (empty($actions) && $k == 'action') {
				continue;
			}
			$table['header'][$k] = ['value' => i18n(null, $v['name']) , 'nowrap' => true, 'width' => $v['width'] ?? null];
		}
		// generate rows
		foreach ($this->rows as $k => $v) {
			// process all columns first
			$row = [];
			foreach ($this->columns as $k2 => $v2) {
				// if we can not view we skip action column
				if (empty($actions) && $k2 == 'action') {
					continue;
				}
				$value = [];
				// create cell properties
				foreach (['width', 'align'] as $v3) {
					if (isset($v2[$v3])) {
						$value[$v3] = $v2[$v3];
					}
				}
				// process rows
				if ($k2 == 'action') {
					$value['value'] = [];
					if (!empty($actions['view'])) {
						$mvc = application::get('mvc');
						$pk = extract_keys($this->model_object->pk, $v);
						$url = $mvc['controller'] . '/_edit?' . http_build_query2($pk);
						$value['value'][] = html::a(['value' => i18n(null, 'View'), 'href' => $url]);
					}
					$value['value'] = implode(' ', $value['value']);
				} else if ($k2 == 'row_number') {
					$value['value'] = $counter . '.';
				} else if ($k2 == 'offset_number') {
					$value['value'] = ($this->offset + $counter) . '.';
				} else if (!empty($v2['options']) && !is_array($v[$k2])) {
					if (isset($v2['options'][$v[$k2]])) {
						$value['value'] = $v2['options'][$v[$k2]]['name'];
					} else {
						$value['value'] = null;
					}
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