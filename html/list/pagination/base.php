<?php

class numbers_frontend_html_list_pagination_base implements numbers_frontend_html_list_pagination_interface {

	/**
	 * Render pagination
	 *
	 * @param object $object
	 * @return string
	 */
	public function render($object, $type) {
		$result = '';
		$result.= '<table cellpadding="2" cellspacing="0" width="100%" class="html_list_pagination">';
			$result.= '<tr>';
				$result.= '<td width="1%">Displaying&nbsp;</td>';
				$result.= '<td width="80" style="width: 80px !impirtant;" nowrap>';
					$result.= html::select(['id' => 'page_sizes_' . $type, 'options' => $object->page_sizes, 'value' => $object->limit, 'no_choose' => true, 'onchange' => "$('#offset').val(0); $('#limit').val(this.value); this.form.submit();"]);
				$result.= '</td>';
				$result.= '<td width="1%">&nbsp;rows</td>';
				// separator
				$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
				// rows fetched
				$result.= '<td width="1%" nowrap>';
					$result.= 'Fetched: ' . $object->num_rows . ($object->total ? (' of ' . $object->total) : '');
				$result.= '</td>';
				// separator
				$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
				// sorting
				if (!empty($object->orderby)) {
					$result.= '<td nowrap width="25%">';
						$result.= 'Sort:&nbsp;';
						$temp = [];
						foreach ($object->orderby as $k => $v) {
							$temp[] = $object->columns[$k]['name'] . ' ' . ($v == SORT_ASC ? '&#8593;' : '&#8595;');
						}
						$result.= implode(', ', $temp);
					$result.= '</td>';
				}
				// separator
				$result.= '<td nowrap width="25%">&nbsp;</td>';
				// navigation
				$flag_next_row_exists = false;
				$flag_last_row_exists = true;
				$current_page = intval($object->offset / $object->limit);
				if ($current_page >= 1) {
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>';
						$result.= html::button(['value' => 'First', 'onclick' => "$('#offset').val(0);this.form.submit();"]);
					$result.= '</td>';
				}
				if ($current_page >= 2) {
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>';
						$previous = (($current_page - 1) * $object->limit);
						$result.= html::button(['value' => 'Previous', 'onclick' => "$('#offset').val({$previous});this.form.submit();"]);
					$result.= '</td>';
				}
				// select with number of pages
				if ($object->num_rows) {
					$pages = ceil($object->total / $object->limit);
					$temp = [];
					for ($i = 0; $i < $pages; $i++) {
						$temp[($i * $object->limit)] = ['name' => $i + 1];
					}
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>Page:&nbsp;</td>';
					$result.= '<td width="100" nowrap>';
						$previous = (($current_page - 1) * $object->limit);
						$result.= html::select(['id' => 'pages_' . $type, 'options' => $temp, 'value' => $object->offset, 'no_choose' => true, 'onchange' => "$('#offset').val(this.value); this.form.submit();"]);
					$result.= '</td>';
					// checking for next and last pages
					$flag_next_row_exists = ($pages - $current_page - 2 > 0) ? true : false;
					$flag_last_row_exists = ($pages - $current_page - 1 > 0) ? true : false;
				} else {
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>Page:&nbsp;</td>';
					$result.= '<td width="100" nowrap>';
						$result.= ($current_page + 1);
					$result.= '</td>';
				}
				if ($flag_next_row_exists) {
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>';
						$next = (($current_page + 1) * $object->limit);
						$result.= html::button(['value'=>'Next', 'onclick'=>"$('#offset').val({$next}); this.form.submit();"]);
					$result.= '</td>';
				}
				if ($flag_last_row_exists) {
					$result.= '<td class="html_list_separator">&nbsp;&nbsp;|&nbsp;&nbsp;</td>';
					$result.= '<td width="1%" nowrap>';
						$last = (($pages - 1) * $object->limit);
						$result.= html::button(['value'=>'Last', 'onclick'=>"$('#offset').val({$last}); this.form.submit();"]);
					$result.= '</td>';
				}
			$result.= '</tr>';
		$result.= '</table>';
		return $result;
	}
}