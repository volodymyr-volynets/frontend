<?php

class numbers_frontend_html_form_renderers_html_pagination_base {

	/**
	 * Render
	 *
	 * @param array $options
	 * @return string
	 */
	public function render(& $options) {
		$type = $options['pagination_type'] ?? 'top';
		// fetched
		if (!empty($options['total'])) {
			$fetched = i18n(null, 'Fetched [num_rows] of [total]', [
				'replace' => [
					'[num_rows]' => i18n(null, $options['num_rows']),
					'[total]' => i18n(null, $options['total'])
				]
			]);
		} else {
			$fetched = i18n(null, 'Fetched [num_rows]', [
				'replace' => [
					'[num_rows]' => i18n(null, $options['num_rows'])
				]
			]);
		}
		// sorting
		$sort = '';
		if (!empty($options['sort'])) {
			$sort.= '<table style="min-height: 40px;"><tr><td valign="middle">';
				$sort.= i18n(null, 'Sort') . ': ';
				$temp = [];
				foreach ($options['sort'] as $k => $v) {
					$temp[] = i18n(null, $k) . ' ' . Html::icon(['type' => 'sort-alpha-' . ($v == SORT_ASC ? 'asc' : 'desc')]);
				}
				$sort.= implode(', ', $temp);
			$sort.= '</td></tr></table>';
		}
		// displaying
		$displaying = '<table>';
			$displaying.= '<tr>';
				// preview button
				if (!empty($options['preview'])) {
					$preview_icon = 'list';
					$preview_value = 0;
					$preview_title = i18n(null, 'List');
				} else {
					$preview_icon = 'th-list';
					$preview_value = 1;
					$preview_title = i18n(null, 'Preview');
				}
				$displaying.= '<td>' . Html::button2(['type' => 'default', 'title' => $preview_title, 'value' => Html::icon(['type' => $preview_icon]), 'onclick' => "numbers.form.set_value(this.form, '__preview', {$preview_value}); numbers.form.trigger_submit(this.form, '__submit_button');"]) . '</td>';
				$displaying.= '<td>&nbsp;</td>';
				$displaying.= '<td><div style="width: 80px;">' . Html::select(['id' => 'page_sizes_' . $type, 'title' => i18n(null, 'Displaying rows'), 'options' => Factory::model('numbers_framework_object_form_model_pagesizes', true)->options(['i18n' => 'skip_sorting']), 'value' => $options['limit'], 'no_choose' => true, 'onchange' => "numbers.form.set_value(this.form, '__offset', 0); numbers.form.set_value(this.form, '__limit', this.value); numbers.form.trigger_submit(this.form, '__submit_button');"]) . '</div></td>';
			$displaying.= '</tr>';
		$displaying.= '</table>';
		// navigation
		$navigation = [];
		$flag_next_row_exists = false;
		$flag_last_row_exists = false;
		$current_page = intval($options['offset'] / $options['limit']);
		if ($current_page >= 1) {
			$navigation[]= Html::button2(['value' => i18n(null, 'First'), 'onclick' => "numbers.form.set_value(this.form, '__offset', 0); numbers.form.trigger_submit(this.form, '__submit_button');"]);
		}
		if ($current_page >= 2) {
			$previous = (($current_page - 1) * $options['limit']);
			$navigation[]= Html::button2(['value' => i18n(null, 'Previous'), 'onclick' => "numbers.form.set_value(this.form, '__offset', {$previous}); numbers.form.trigger_submit(this.form, '__submit_button');"]);
		}
		// select with number of pages
		$pages = ceil($options['total'] / $options['limit']);
		if ($options['num_rows']) {
			$temp = [];
			for ($i = 0; $i < $pages; $i++) {
				$temp[($i * $options['limit'])] = ['name' => i18n(null, $i + 1)];
			}
			$navigation2 = i18n(null, 'Page') . ': ';
			$previous = (($current_page - 1) * $options['limit']);
			$navigation2.= '<div style="width: 100px; display: inline-block;">' . Html::select(['id' => 'pages_' . $type, 'options' => $temp, 'value' => $options['offset'], 'no_choose' => true, 'onchange' => "numbers.form.set_value(this.form, '__offset', this.value); numbers.form.trigger_submit(this.form, '__submit_button');"]) . '</div>';
			$navigation[] = $navigation2;
			// checking for next and last pages
			$flag_next_row_exists = ($pages - $current_page - 2 > 0) ? true : false;
			$flag_last_row_exists = ($pages - $current_page - 1 > 0) ? true : false;
		} else {
			$navigation[]= i18n(null, 'Page') . ': ' . ($current_page + 1);
		}
		if ($flag_next_row_exists) {
			$next = (($current_page + 1) * $options['limit']);
			$navigation[]= Html::button2(['value' => i18n(null, 'Next'), 'onclick' => "numbers.form.set_value(this.form, '__offset', {$next}); numbers.form.trigger_submit(this.form, '__submit_button');"]);
		}
		if ($flag_last_row_exists) {
			$last = (($pages - 1) * $options['limit']);
			$navigation[]= Html::button2(['value' => i18n(null, 'Last'), 'onclick' => "numbers.form.set_value(this.form, '__offset', {$last}); numbers.form.trigger_submit(this.form, '__submit_button');"]);
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
							'class' => 'numbers_frontend_form_list_sort',
							'value' => $sort,
							'options' => [
								'field_size' => 'col-xs-12 col-sm-12 col-lg-3',
								'percent' => 15,
								'style' => 'min-height: 40px;'
							]
						]
					],
					'Navigation' => [
						'Navigation' => [
							'class' => 'numbers_frontend_form_list_pagination_navigation',
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
		return Html::grid($grid);
	}
}