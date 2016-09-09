<?php

class numbers_frontend_html_list_pagination_links implements numbers_frontend_html_list_pagination_interface {

	/**
	 * Render pagination
	 *
	 * @param object $object
	 * @return string
	 */
	public function render($object, $type) {
		// fetched
		$fetched = i18n(null, 'Fetched') . ': ' . $object->num_rows . ($object->total > 0 ? (' ' . i18n(null, 'of') . ' ' . $object->total) : '');
		$displaying = i18n(null, 'Displaying') . ': ' . $object->limit;
		$links = [];
		$pages = ceil($object->total / $object->limit);
		$current_page = intval($object->offset / $object->limit);
		$total_pages = ceil($object->total / $object->limit);
		// numer of links to the right and left
		$right = intval($object->max_pages / 2);
		$start = 0;
		if ($current_page - $right > 0) {
			$start = $current_page - $right;
		}
		$counter = 0;
		// link to previous page
		if ($current_page > 0) {
			$links['previous'] = $current_page - 1;
		}
		for ($i = $start; $i < $current_page; $i++) {
			$links[$counter] = $i;
			$counter++;
		}
		for ($i = $current_page; $i < $total_pages; $i++) {
			$links[$counter] = $i;
			$counter++;
			if ($counter > $object->max_pages) {
				break;
			}
		}
		if ($current_page < $total_pages - 1) {
			$links['next'] = $current_page + 1;
		}
		$result = [];
		if (count($links) > 1) {
			foreach ($links as $k => $v) {
				$value = $v + 1;
				$left2 = '';
				$right2 = '';
				$no_url = false;
				if ($k === 'previous') {
					$value = i18n(null, 'Previous');
					$right2 = '&nbsp;&nbsp;&nbsp;';
				} else if ($k === 'next') {
					$value = i18n(null, 'Next');
					$left2 = '&nbsp;&nbsp;&nbsp;';
				} else if ($v === $current_page) {
					$value = '<b>' . $value . '</b>';
					$no_url = true;
				}
				$result[] = $left2 . ($no_url ? $value : html::a(['value' => $value, 'href' => 'javascript:void(0);', 'onclick' => $object->url . '(' . ($v * $object->limit) . ')'])) . $right2;
			}
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
					'Navigation' => [
						'Navigation' => [
							'class' => '',
							'value' => implode(' ', $result),
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