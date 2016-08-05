<?php

class numbers_frontend_html_list_pagination_base implements numbers_frontend_html_list_pagination_interface {

	/**
	 * Render pagination
	 *
	 * @param object $object
	 * @return string
	 */
	public function render($object, $type) {
		// fetched
		$fetched = i18n(null, 'Fetched') . ': ' . $object->num_rows . ($object->total > 0 ? (' ' . i18n(null, 'of') . ' ' . $object->total) : '');
		// sorting
		$sort = '';
		if (!empty($object->orderby)) {
			$sort.= i18n(null, 'Sort') . ': ';
			$temp = [];
			foreach ($object->orderby as $k => $v) {
				if ($k == 'full_text_search') {
					$temp[] = i18n(null, 'Text Search') . ' ' . html::icon(['type' => 'sort-alpha-' . ($v == SORT_ASC ? 'asc' : 'desc')]);
				} else {
					$temp[] = i18n(null, $object->columns[$k]['name']) . ' ' . html::icon(['type' => 'sort-alpha-' . ($v == SORT_ASC ? 'asc' : 'desc')]);
				}
			}
			$sort.= implode(', ', $temp);
		}
		// displaying
		$displaying = i18n(null, 'Displaying') . ' ';
		$page_options = $object->page_sizes;
		$page_options[PHP_INT_MAX] = ['name' => i18n(null, $page_options[PHP_INT_MAX]['name'])];
		$displaying.= '<div style="width: 80px; display: inline-block;">' . html::select(['id' => 'page_sizes_' . $type, 'options' => $page_options, 'value' => $object->limit, 'no_choose' => true, 'onchange' => "$('#offset').val(0); $('#limit').val(this.value); numbers.frontend_list.trigger_submit(this.form);"]) . '</div>';
		// navigation
		$navigation = [];
		$flag_next_row_exists = false;
		$flag_last_row_exists = false;
		$current_page = intval($object->offset / $object->limit);
		if ($current_page >= 1) {
			$navigation[]= html::button2(['value' => i18n(null, 'First'), 'onclick' => "$('#offset').val(0); numbers.frontend_list.trigger_submit(this.form);"]);
		}
		if ($current_page >= 2) {
			$previous = (($current_page - 1) * $object->limit);
			$navigation[]= html::button2(['value' => i18n(null, 'Previous'), 'onclick' => "$('#offset').val({$previous}); numbers.frontend_list.trigger_submit(this.form);"]);
		}
		// select with number of pages
		$pages = ceil($object->total / $object->limit);
		if ($object->num_rows) {
			$temp = [];
			for ($i = 0; $i < $pages; $i++) {
				$temp[($i * $object->limit)] = ['name' => $i + 1];
			}
			$navigation2 = i18n(null, 'Page') . ': ';
			$previous = (($current_page - 1) * $object->limit);
			$navigation2.= '<div style="width: 100px; display: inline-block;">' . html::select(['id' => 'pages_' . $type, 'options' => $temp, 'value' => $object->offset, 'no_choose' => true, 'onchange' => "$('#offset').val(this.value); numbers.frontend_list.trigger_submit(this.form);"]) . '</div>';
			$navigation[] = $navigation2;
			// checking for next and last pages
			$flag_next_row_exists = ($pages - $current_page - 2 > 0) ? true : false;
			$flag_last_row_exists = ($pages - $current_page - 1 > 0) ? true : false;
		} else {
			$navigation[]= i18n(null, 'Page') . ': ' . ($current_page + 1);
		}
		if ($flag_next_row_exists) {
			$next = (($current_page + 1) * $object->limit);
			$navigation[]= html::button2(['value' => i18n(null, 'Next'), 'onclick' => "$('#offset').val({$next}); numbers.frontend_list.trigger_submit(this.form);"]);
		}
		if ($flag_last_row_exists) {
			$last = (($pages - 1) * $object->limit);
			$navigation[]= html::button2(['value' => i18n(null, 'Last'), 'onclick' => "$('#offset').val({$last}); numbers.frontend_list.trigger_submit(this.form);"]);
		}
		// generating grid
		$grid = [
			'options' => [
				0 => [
					'Displaying' => [
						'Displaying' => [
							'value' => $displaying,
							'options' => [
								'field_size' => 'col-xs-6 col-sm-6 col-lg-2',
								'percent' => 15,
								'style' => 'height: 40px; line-height: 40px;',
							]
						]
					],
					'Fetched' => [
						'Fetched' => [
							'value' => $fetched,
							'options' => [
								'field_size' => 'col-xs-6 col-sm-6 col-lg-2',
								'percent' => 15,
								'style' => 'height: 40px; line-height: 40px;',
							]
						]
					],
					'Sort' => [
						'Sort' => [
							'class' => 'list_pagination_sort',
							'value' => $sort,
							'options' => [
								'field_size' => 'col-xs-12 col-sm-12 col-lg-3',
								'percent' => 15,
								'style' => 'height: 40px; line-height: 40px;',
							]
						]
					],
					'Navigation' => [
						'Navigation' => [
							'class' => 'list_pagination_navigation',
							'value' => implode(' ', $navigation),
							'options' => [
								'field_size' => 'col-xs-12 col-sm-12 col-lg-5',
								'percent' => 50,
								'style' => 'height: 40px; line-height: 40px;',
							]
						]
					],
				]
			]
		];
		return html::grid($grid);
	}
}