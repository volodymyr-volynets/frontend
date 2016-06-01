<?php

class numbers_frontend_html_report_base {

	/**
	 * Data container
	 * 
	 * @var unknown_type
	 */
	private $data = array();

	/**
	 * Header container
	 * 
	 * @var array
	 */
	private $header = array();

	/**
	 * Flag indicating that we added columns
	 * 
	 * @var boolean
	 */
	private $flag_columns_added = false;

	/**
	 * Whether or not we need to show borders
	 * 
	 * @var boolean
	 */
	private $flag_pdf_show_borders = 0;

	/**
	 * Init report and set header
	 * Possible keys:
	 * 		datetime
	 * 		name
	 * 		description
	 * 		filter
	 * 		font_name
	 * 		font_size
	 * 
	 * @param unknown_type $header
	 * @param unknown_type $link
	 * @throws Exception
	 */
	public function __construct($header) {
		$header['datetime'] = isset($header['datetime']) ? $header['datetime'] : format::now();
		if (empty($header['name'])) Throw new Exception('Name?');
		// font
		$header['font_name'] = isset($header['font_name']) ? $header['font_name'] : 'helvetica';
		$header['font_size'] = isset($header['font_size']) ? $header['font_size'] : 12;
		$this->header = $header;
		// if we need to show borders
		$this->flag_pdf_show_borders = !empty($header['flag_pdf_show_borders']);
	}

	/**
	 * Add data to the report
	 * Possible types:
	 * 		columns
	 * 		data
	 * 		separator
	 * 
	 * @param string $type
	 * @param array $data
	 */
	public function add($data, $type = 'data') {
		if ($type=='columns') {
			$flag_columns_added = true;
		}
		// minimize data
		$temp = array();
		foreach ($data as $k=>$v) {
			if (is_array($v)) {
				$temp[$k]['v'] = $v['value'] ?? null;
				if (isset($v['align'])) $temp[$k]['a'] = $v['align'];
				if (isset($v['width'])) $temp[$k]['w'] = $v['width'];
				if (isset($v['bold'])) $temp[$k]['b'] = $v['bold'];
				if (isset($v['subtotal'])) $temp[$k]['s'] = $v['subtotal']; // single line at the top
				if (isset($v['total'])) $temp[$k]['t'] = $v['total']; // double line at the top
				if (isset($v['underline'])) $temp[$k]['u'] = $v['underline'];
				if (isset($v['colspan'])) $temp[$k]['c'] = $v['colspan'];
				if (isset($v['url'])) $temp[$k]['h'] = $v['url'];
				if (isset($v['title'])) $temp[$k]['l'] = $v['title'];
			} else {
				$temp[$k] = $v;
			}
		}
		$this->data[] = array('t'=>$type, 'd'=>$temp);
	}

	/**
	 * Render
	 * 
	 * @param string $type
	 * @return string
	 */
	public function render($type) {
		$result = '';
		$session = new session();
		// main switch
		switch ($type) {
			case 'pdf':
				// document properties
				$this->header['pdf']['orientation'] = isset($this->header['pdf']['orientation']) ? $this->header['pdf']['orientation'] : 'P';
				$this->header['pdf']['unit'] = 'mm';
				$this->header['pdf']['format'] = isset($this->header['pdf']['format']) ? $this->header['pdf']['format'] : 'LETTER';
				$this->header['pdf']['encoding'] = isset($this->header['pdf']['encoding']) ? $this->header['pdf']['encoding'] : 'UTF-8';
				$this->header['pdf']['font'] = isset($this->header['pdf']['font']) ? $this->header['pdf']['font'] : array('family' => 'helvetica', 'style' => '', 'size' => 8);

				//include 'tcpdf/tcpdf.php';
				// create new PDF document
				$pdf = new TCPDF($this->header['pdf']['orientation'], $this->header['pdf']['unit'], $this->header['pdf']['format'], true, $this->header['pdf']['encoding'], false);

				// set margins
				$pdf->SetMargins(0, 0, 0);
				$pdf->setPrintHeader(false);

				// disable auto break
				$pdf->SetAutoPageBreak(false, 0);

				// set default font subsetting mode
				$pdf->setFontSubsetting(true);

				// set color for background
				$pdf->SetFillColor(255, 255, 255);

				// set font
				$pdf->SetFont($this->header['pdf']['font']['family'], $this->header['pdf']['font']['style'], $this->header['pdf']['font']['size']);

				// stats
				$page_counter = 1;
				$page_y = 0;
				$flag_new_page = true;
				$flag_filter = true;
				$flag_first_row = true;
				$columns = array();
				$all_columns = array();

				// gethering all columns
				foreach ($this->data as $k=>$v) {
					if ($v['t']=='columns') $all_columns[] = $v;
				}

				// looping through the data
				foreach ($this->data as $k=>$v) {
					if ($v['t']=='columns') continue;

					if ($flag_new_page) {
						// add new page
						$pdf->AddPage($this->header['pdf']['orientation'], '', true);

						// drawing header
						$pdf->MultiCell(40, 5, format::datetime(format::now()), 0, 'L', 1, 0, 5, 5, true, 0, false, true, 10, 'T');
						// company + book name
						$pw = $pdf->getPageWidth();
						$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
						// todo: fix here
						$pdf->MultiCell($pw - 90, 5, $session->company_name . ': ' . $session->book_name, 0, 'C', 1, 0, 40, 5, true, 0, false, true, 10, 'T');
						// page counter
						$pdf->SetFont($this->header['pdf']['font']['family'], '', $this->header['pdf']['font']['size']);
						$pdf->MultiCell(40, 5, 'Page ' . $page_counter, 0, 'R', 1, 0, $pw - 45, 5, true, 0, false, true, 10, 'T');
						// report name
						$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
						$report_name = $this->header['name'] . ' (' . implode('-', application::get(array('mvc', 'controllers'))) . ')';
						$pdf->MultiCell($pw - 10, 5, $report_name, 0, 'L', 1, 0, 5, 10, true, 0, false, true, 10, 'T');

						if (isset($this->header['description'])) {
							$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
							$pdf->MultiCell(205, 5, $this->header['description'], 0, 'L', 1, 0, 5, 15, true, 0, false, true, 10, 'T');
							$page_y = 25;
						} else {
							$page_y = 20;
						}

						// if we need to add a filter
						if ($flag_filter) {
							if (isset($this->header['filter'])) {
								foreach ($this->header['filter'] as $k2=>$v2) {
									$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
									$pdf->MultiCell(50, 5, $k2 . ':', 0, 'L', 1, 0, 5, $page_y, true, 0, false, true, 10, 'T');
									$pdf->SetFont($this->header['pdf']['font']['family'], '', $this->header['pdf']['font']['size']);
									$number_of_cells = $pdf->MultiCell($pdf->getPageWidth()-60, 5, $v2, 0, 'L', 1, 0, 55, $page_y, true, 0, false, true, 10, 'T');
									if ($number_of_cells>1) {
										$page_y+= 5 * ($number_of_cells - 1);
									}
									$page_y+= 5;
								}
							}
							$flag_filter = false;

							// adding one line space
							$page_y+= 5;
						}

						// page counter
						$page_counter++;
						$flag_new_page = false;
					}

					// rendering rows
					if ($flag_first_row) {
						if (empty($columns)) {
							$columns = current($all_columns);
							// repopulate width
							$count_empty = 0;
							$taken = 0;
							foreach ($columns['d'] as $k2=>$v2) {
								if (empty($v2['w'])) {
									$count_empty++;
								} else {
									$taken+= $v2['w'];
								}
							}
							if (!empty($count_empty)) {
								$new_width = floor(($pdf->getPageWidth() - 10 - $taken) / $count_empty);
								foreach ($v['d'] as $k2=>$v2) $columns['d'][$k2]['w'] = $new_width;
							}
						}
						$flag_first_row = false;

						// columns
						foreach ($all_columns as $k20=>$v20) {
							$x = 5;
							foreach ($columns['d'] as $k10=>$v10) {
								foreach (array('v', 'c', 'a', 'b', 's', 't', 'u') as $v30) {
									if (isset($v20['d'][$k10][$v30])) $v10[$v30] = $v20['d'][$k10][$v30];
								}
								$new_width = @$v10['w'];
								if (!empty($v10['c'])) {
									// we need to get width of next elements
									for ($i=$k10+1; $i<$k10+$v10['c']; $i++) {
										$new_width+= $columns['d'][$k10]['w'];
									}
								}
								$align = str_replace(array('left', 'right', 'center'), array('L', 'R', 'C'), @$v10['a']);
								if (empty($align)) $align = 'L';
								if (@$v10['b']) {
									$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
								} else {
									$pdf->SetFont($this->header['pdf']['font']['family'], '', $this->header['pdf']['font']['size']);
								}
								$pdf->MultiCell($new_width, 5, @$v10['v'], $this->flag_pdf_show_borders, $align, 1, 0, $x, $page_y, true, 0, false, true, 10, 'T');
								// underline
								if (@$v10['u']) {
									$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
									$pdf->Line($x, $page_y + 5, $x + @$v10['w'], $page_y + 5);
								}
								$x+= @$v10['w'];
							}
							$page_y+= 5;
						}
					}

					$pdf->SetFont($this->header['pdf']['font']['family'], '', $this->header['pdf']['font']['size']);
					$x = 5;
					foreach ($columns['d'] as $k10=>$v10) {
						// we do do render cells if no data
						if (isset($v['d'][$k10]['v'])) {
							$align = str_replace(array('left', 'right', 'center'), array('L', 'R', 'C'), @$v['d'][$k10]['a']);
							if (empty($align)) $align = 'L';
							if (@$v['d'][$k10]['b']) {
								$pdf->SetFont($this->header['pdf']['font']['family'], 'B', $this->header['pdf']['font']['size']);
							} else {
								$pdf->SetFont($this->header['pdf']['font']['family'], '', $this->header['pdf']['font']['size']);
							}
							// if we override width
							$width = $v10['w'];
							if (isset($v['d'][$k10]['w'])) {
								$width = $v['d'][$k10]['w'];
							} else if (isset($v['d'][$k10]['c'])) { // colspan
								// we need to get width of next elements
								for ($i=$k10+1; $i<$k10+$v['d'][$k10]['c']; $i++) {
									$width+= @$columns['d'][$i]['w'];
								}
							}
							$value = @$v['d'][$k10]['v'];
							$value = str_replace('&nbsp;', ' ', $value);
							// rendering cell
							$pdf->MultiCell($width, 5, $value, $this->flag_pdf_show_borders, $align, 1, 0, $x, $page_y, true, 0, false, true, 10, 'T');
							// underline
							if (@$v['d'][$k10]['u']) {
								$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
								$pdf->Line($x, $page_y + 5, $x + $v10['w'], $page_y + 5);
							}
							// subtotal
							if (@$v['d'][$k10]['s']) {
								$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
								$pdf->Line($x + 1, $page_y, $x + $v10['w'] - 1, $page_y);
							}
							// total
							if (@$v['d'][$k10]['t']) {
								$pdf->SetLineStyle(array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
								$pdf->Line($x + 1, $page_y, $x + $v10['w'] - 1, $page_y);
								$pdf->Line($x + 1, $page_y - 0.75, $x + $v10['w'] - 1, $page_y - 0.75);
							}
						}
						$x+= @$v10['w'];
					}

					// incrementing 
					$page_y+= 5;
					if ($page_y > $pdf->getPageHeight() - 10) {
						$flag_new_page = true;
						$flag_first_row = true;
					}
				}

				$pdf->Output($this->header['name'] . '.pdf', 'I');
				exit;

				break;
			case 'csv':
			case 'txt':
			case 'xlsx':
				$sheet = strip_tags($this->header['name']);
				$sheet = str_replace(['/', '\\'], '', $sheet);
				// generating header
				$header = [];
				$header[$sheet][] = [
					format::datetime(format::now()),
					'',
					$session->company_name . ': ' .	$session->book_name, // todo: fix here
					'',
					'Page 1'
				];
				$controllers = application::get(['mvc', 'controllers']);
				$header[$sheet][] = [strip_tags($this->header['name']) . ' (' . implode('-', $controllers) . ')'];
				if (isset($this->header['description'])) {
					$header[$sheet][] = [$this->header['description']];
				}
				$header[$sheet][] = [' '];
				$temp = $header;

				// displaying filter
				if (isset($this->header['filter'])) {
					$temp2 = [];
					foreach ($this->header['filter'] as $k => $v) {
						$temp[$sheet][] = [strip_tags($k), strip_tags($v)];
					}
					$temp[$sheet][] = [' '];
				}
				// converting data
				foreach ($this->data as $k => $v) {
					$temp2 = [];
					foreach ($v['d'] as $k2 => $v2) {
						if (is_array($v2)) {
							$value = $v2['v'] ?? null;
						} else {
							$value = $v2;
						}
						// replaces
						$value = str_replace('&nbsp;', ' ', $value);
						$temp2[] = strip_tags($value);
					}
					$temp[$sheet][] = $temp2;
				}

				// get output buffering
				helper_ob::clean_all();
				// content
				switch ($type) {
					case 'xlsx':
						echo io::array_to_excel($temp, io::$formats[$type]['excel_code'], null);
						break;
					default:
						// csv or text
						header('Content-Type: ' . numbers_frontend_exports_csv_base::$formats[$type]['content_type']);
						header('Content-Disposition: attachment; filename="' . $sheet . '.' . $type . '"');
						header('Cache-Control: max-age=0');
						echo numbers_frontend_exports_csv_base::array_to_csv($temp, numbers_frontend_exports_csv_base::$formats[$type]['delimiter'], numbers_frontend_exports_csv_base::$formats[$type]['enclosure']);
				}
				exit;
				break;
			case 'html':
			case 'html2':
			default:
				// rendering data
				$table = [
					'options' => []
				];
				$counter = 1;
				foreach ($this->data as $k => $v) {
					$flag_colspan = 0;
					$row = [];
					if (!empty($v['d'])) {
						foreach ($v['d'] as $k2 => $v2) {
							if ($flag_colspan > 0) {
								$flag_colspan--;
								continue;
							}
							$colspan = '';
							if ($v2['c'] ?? null) {
								$colspan = $v2['c'];
								$flag_colspan = $v2['c'] - 1;
							}
							$align = 'left';
							$title = '';
							$style = '';
							if (is_array($v2)) {
								$value = $v2['v'] ?? null;
								if (!empty($v2['h'])) {
									$v2['h']['value'] = $value;
									$value = html::a($v2['h']);
								}
								if (!empty($v2['a'])) $align = $v2['a'];
								if (!empty($v2['l'])) $title = $v2['l'];
								// bold lines
								if ($v2['b'] ?? null) $value = '<b>' . $value . '</b>';
								// todo: convert styles to classes
								if ($v2['s'] ?? null) $style.= 'border-top: 1px solid #000;';
								if ($v2['t'] ?? null) $style.= 'border-top: 3px double #000;';
								if ($v2['u'] ?? null) $style.= 'border-bottom: 1px solid #000;';
							} else {
								$value = $v2;
							}
							$row[$k2] = ['value' => $value, 'align' => $align, 'colspan' => $colspan, 'style' => $style, 'title' => $title, 'nowrap' => true];
						}
					} else {
						$row[0] = ['value' => '&nbsp;'];
					}
					$table['options'][$counter] = $row;
					$counter++;
				}
				$result = html::table($table);
				// printable export
				if ($type == 'html2') {
					$header = [
						'options' => []
					];
					$header['options'][] = [
						0 => format::datetime(format::now()),
						1 => '',
						2 => $session->company_name . ': ' .	$session->book_name, // todo: fix here
						3 => '',
						4 => 'Page 1'
					];
					$controllers = application::get(['mvc', 'controllers']);
					$header['options'][] = [['value' => strip_tags($this->header['name']) . ' (' . implode('-', $controllers) . ')', 'colspan' => 5]];
					if (isset($this->header['description'])) {
						$header['options'][] = [$this->header['description']];
					}
					$header['options'][] = ['&nbsp;'];
					// displaying filter
					if (isset($this->header['filter'])) {
						$temp2 = [];
						foreach ($this->header['filter'] as $k => $v) {
							$header['options'][] = [strip_tags($k) . ':', strip_tags($v)];
						}
						$header['options'][] = ['&nbsp;'];
					}
					$header = html::table($header);
					layout::render_as($header . $result, 'text/html');
				}
		}
		return $result;
	}

	/**
	 * Render legend
	 *
	 * @param array $data
	 * @param int $number_of_columns
	 * @param int $max_columns
	 * @param string $name
	 */
	public function render_legend($data, $number_of_columns, $max_columns, $name = '') {
		// separator
		$data2 = array(
			array('value'=>''),
		);
		$this->add(array(), 'separator');

		// adding name
		if ($name) {
			$this->add(array(array('value'=>$name, 'bold'=>true)));
		}

		// merging rows
		$row_index = 0;
		$data_index = 0;
		$temp = array();
		foreach ($data as $k=>$v) {
			$t = $data_index % $number_of_columns;
			if ($t==0) $row_index++;
			$temp[$row_index][$t] = array('key'=>$k, 'value'=>$v['name']);
			$data_index++;
		}

		// rendering
		foreach ($temp as $k=>$v) {
			$line = array();
			foreach ($v as $k2=>$v2) {
				$line[] = $v2['key'] . ' - ' . $v2['value'];
			}
			// separator
			$data2 = array(
				array('value'=>implode(', ', $line), 'colspan'=>$max_columns),
			);
			$this->add($data2);
		}
	}
}