<?php

namespace Numbers\Frontend\Components\Calendar\Renderer;
class Base {

	/**
	 * Render
	 *
	 * @param int $type
	 * @param array $data
	 * @param array $holidays
	 * @return string
	 * @throws \Exception
	 */
	public function render(int $type, array $data, array $holidays, string $start_date, array $options = []) : string {
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_Components_Calendar_Renderer_Media_CSS_Calendar.css', 10000);
		switch ($type) {
			case 20: // week
				return $this->renderWeek($data, $holidays, $start_date, $options);
			case 30: // month
				return $this->renderMonth($data, $holidays, $start_date, $options);
			default:
				Throw new \Exception('Type?');
		}
	}

	/**
	 * Render month
	 *
	 * @param array $data
	 * @param array $holidays
	 * @param string $start_date
	 * @return string
	 */
	private function renderMonth(array $data, array $holidays, string $start_date, array $options = []) : string {
		$result = '';
		$result.= '<table class="numbers_account_calendar_holder" width="100%">';
		$week_days = \Numbers\Framework\Helper\Model\Date\WeekDays2::getStatic();
		$date1 = new \DateTime($start_date);
		$date2 = clone $date1;
		$date1->modify('last Sunday');
		$date2->modify('first day of next month');
		$next_month = $date2->format('Y-m-d');
		$now = \Format::now('date');
		// rearrange
		$data_arranged = [
			'holidays' => [],
			'multiple_days' => [],
			'single_day' => []
		];
		$data_slot_counter = 0;
		$data_slot_lock = [];
		foreach ($holidays as $k => $v) {
			$name = $v['name'];
			if (strpos($name, ' - ') !== false) {
				$temp = explode(' - ', $name);
				$name = array_pop($temp);
			}
			$v['slot_name'] = $name;
			$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($name);
			$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
			$data_arranged['holidays'][$v['date']][$k] = $v;
		}
		foreach ($data as $k => $v) {
			$date1a = new \DateTime(\Format::readDate($v['work_starts'], 'datetime'));
			$date2a = new \DateTime(\Format::readDate($v['work_ends'] ?? \Helper\Date::addInterval($v['work_starts'], '+1 hour'), 'datetime'));
			// multi days intervals
			if ($date1a->format('Y-m-d') != $date2a->format('Y-m-d')) {
				$data_slot_counter++;
				while ($date1a->format('Y-m-d') != $date2a->format('Y-m-d')) {
					$v['slot_counter'] = $data_slot_counter;
					$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($v['name'] . '::' . $v['hash_name']);
					$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
					$data_arranged['multiple_days'][$date1a->format('Y-m-d')][$data_slot_counter] = $v;
					$date1a->modify('+1 day');
				}
			} else { // single day intervals
				$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($v['name'] . '::' . $v['hash_name']);
				$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
				$data_arranged['single_day'][$date1a->format('Y-m-d')][$k] = $v;
			}
		}
		for ($i = 1; $i <= 6; $i++) {
			$result.= '<tr>';
				foreach ($week_days as $k => $v) {
					if ($date1->format('Y-m-d') == $now) {
						$result.= '<td class="numbers_account_calendar_td numbers_account_calendar_month_cell numbers_account_calendar_current">';
					} else {
						$result.= '<td class="numbers_account_calendar_td numbers_account_calendar_month_cell">';
					}
						$result.= '<div class="numbers_account_calendar_weekday_name">';
							if ($i == 1) {
								$month = null;
								if ($date1->format('j') == 1) {
									$month = ' / ' . i18n(null, $date1->format('F'));
								}
								$result.= '<h5>' . i18n(null, $date1->format('l')) . $month . '</h5>';
							} else if ($date1->format('j') == 1) {
								$result.= '<h5>' . i18n(null, $date1->format('F')) . '</h5>';
							}
							$result.= '<h5>' . \Format::id($date1->format('j')) . '</h5>';
						$result.= '</div>';
						// render holidays first
						$date1h = $date1->format('Y-m-d');
						if (!empty($data_arranged['holidays'][$date1h])) {
							foreach ($data_arranged['holidays'][$date1h] as $k2 => $v2) {
								$result.= '<div class="numbers_account_calendar_multiday_cell">';
									$result.= '<div class="numbers_account_calendar_multiday_interval" style="color: ' . $v2['slot_text_color']  . '; background-color: ' . $v2['slot_color'] . ';">';
										$result.= $v2['slot_name'];
									$result.= '</div>';
								$result.= '</div>';
							}
						}
						// render multi days
						if (!empty($data_arranged['multiple_days'][$date1h])) {
							foreach ($data_arranged['multiple_days'][$date1h] as $k2 => $v2) {
								// onclick
								$onclick = '';
								if (!empty($options['onclick_renderer'])) {
									$method = \Factory::method($options['onclick_renderer'], null, true);
									$onclick = ' onclick= "' . call_user_func_array([$method[0], $method[1]], [$v2]) . '" ';
								}
								// render
								$result.= '<div class="numbers_account_calendar_multiday_cell">';
									$result.= '<div class="numbers_account_calendar_multiday_interval" style="color: ' . $v2['slot_text_color']  . '; background-color: ' . $v2['slot_color'] . ';" ' . $onclick . '>';
										// cell renderer
										if (!empty($options['cell_renderer'])) {
											$method = \Factory::method($options['cell_renderer'], null, true);
											$result.= call_user_func_array([$method[0], $method[1]], [$v2]);
										} else {
											$result.= $v2['name'];
										}
									$result.= '</div>';
								$result.= '</div>';
							}
						}
						// single day last
						if (!empty($data_arranged['single_day'][$date1h])) {
							foreach ($data_arranged['single_day'][$date1h] as $k2 => $v2) {
								// onclick
								$onclick = '';
								if (!empty($options['onclick_renderer'])) {
									$method = \Factory::method($options['onclick_renderer'], null, true);
									$onclick = ' onclick= "' . call_user_func_array([$method[0], $method[1]], [$v2]) . '" ';
								}
								// render
								$result.= '<div class="numbers_account_calendar_multiday_cell">';
									$result.= '<div class="numbers_account_calendar_multiday_interval" style="color: ' . $v2['slot_text_color']  . '; background-color: ' . $v2['slot_color'] . ';" ' . $onclick . '>';
										// cell renderer
										if (!empty($options['cell_renderer'])) {
											$method = \Factory::method($options['cell_renderer'], null, true);
											$result.= call_user_func_array([$method[0], $method[1]], [$v2]);
										} else {
											$result.= $v2['name'];
										}
									$result.= '</div>';
								$result.= '</div>';
							}
						}
					$result.= '</td>';
					// add one day
					$date1->modify('+1 day');
				}
			$result.= '</tr>';
			// we need to end a loop
			if ($date1->format('Y-m-d') > $next_month) break;
		}
		$result.= '</table>';
		return $result;
	}

	/**
	 * Render weeks
	 *
	 * @param array $data
	 * @param array $holidays
	 * @param string $start_date
	 * @return string
	 */
	private function renderWeek(array $data, array $holidays, string $start_date, array $options = []) : string {
		// rearrange
		$data_arranged = [
			'holidays' => [],
			'multiple_days' => [],
			'single_day' => []
		];
		$data_slot_counter = 0;
		$data_slot_lock = [];
		foreach ($holidays as $k => $v) {
			$data_slot_counter++;
			$name = $v['name'];
			if (strpos($name, ' - ') !== false) {
				$temp = explode(' - ', $name);
				$name = array_pop($temp);
			}
			$v['slot_name'] = $name;
			$v['slot_counter'] = $data_slot_counter;
			$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($name);
			$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
			$data_arranged['holidays'][$v['date']][$data_slot_counter] = $v;
		}
		foreach ($data as $k => $v) {
			$date1 = new \DateTime(\Format::readDate($v['work_starts'], 'datetime'));
			$date2 = new \DateTime(\Format::readDate($v['work_ends'] ?? \Helper\Date::addInterval($v['work_starts'], '+1 hour'), 'datetime'));
			// multi days intervals
			if ($date1->format('Y-m-d') != $date2->format('Y-m-d')) {
				$data_slot_counter++;
				while ($date1->format('Y-m-d') != $date2->format('Y-m-d')) {
					$v['slot_counter'] = $data_slot_counter;
					$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($v['name'] . '::' . $v['hash_name']);
					$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
					$data_arranged['multiple_days'][$date1->format('Y-m-d')][$data_slot_counter] = $v;
					$date1->modify('+1 day');
				}
			} else { // single day intervals
				$v['slot_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::colorFromString($v['name'] . '::' . $v['hash_name']);
				$v['slot_text_color'] = \Numbers\Frontend\HTML\Renderers\Common\Colors::determineTextColor($v['slot_color']);
				$data_arranged['single_day'][$date1->format('w')][$date1->format('G')][$k] = $v;
			}
		}
		$result = '';
		$result.= '<table class="numbers_account_calendar_holder" width="100%">';
			// header
			$result.= '<tr>';
				$result.= '<td class="numbers_account_calendar_ampm">&nbsp;</td>';
				$week_days = \Numbers\Framework\Helper\Model\Date\WeekDays2::getStatic();
				$date1 = new \DateTime($start_date);
				$current_date = \Format::now('date');
				$current_week_day = null;
				foreach ($week_days as $k => $v) {
					if ($date1->format('Y-m-d') == $current_date) {
						$result.= '<td class="numbers_account_calendar_td numbers_account_calendar_current">';
						$current_week_day = $k;
					} else {
						$result.= '<td class="numbers_account_calendar_td">';
					}
						$result.= '<div class="numbers_account_calendar_weekday_name">';
							$result.= i18n(null, $v['name']);
							$result.= '<br/>';
							$result.= '<h5>' . \Format::date($date1->format('Y-m-d')) . '</h5>';
						$result.= '</div>';
						// add holidays
						$date1h = $date1->format('Y-m-d');
						if (isset($data_arranged['holidays'][$date1h])) {
							foreach ($data_arranged['holidays'][$date1h] as $k2 => $v2) {
								$result.= '<div class="numbers_account_calendar_multiday_cell">';
									$result.= '<div class="numbers_account_calendar_multiday_interval" style="color: ' . $v2['slot_text_color']  . '; background-color: ' . $v2['slot_color'] . ';">';
										$result.= $v2['slot_name'];
									$result.= '</div>';
								$result.= '</div>';
							}
						}
						// add one day
						$date1->modify('+1 day');
						// multi day intervals
						$date1a = $date1->format('Y-m-d');
						if (!empty($data_arranged['multiple_days'][$date1a])) {
							for ($i = 1; $i <= $data_slot_counter; $i++) {
								if (!empty($data_arranged['holidays'][$date1h][$i])) continue;
								$result.= '<div class="numbers_account_calendar_multiday_cell">';
									if (!empty($data_arranged['multiple_days'][$date1a][$i])) {
										$result.= '<div class="numbers_account_calendar_multiday_interval" style="color: ' . $data_arranged['multiple_days'][$date1a][$i]['slot_text_color']  . '; background-color: ' . $data_arranged['multiple_days'][$date1a][$i]['slot_color'] . ';">';
											if (!empty($data_slot_lock[$i])) {
												$result.= '&nbsp;';
											} else {
												$result.= $data_arranged['multiple_days'][$date1a][$i]['name'];
												$data_slot_lock[$i] = true;
											}
										$result.= '</div>';
									} else {
										$result.= '&nbsp;';
									}
								$result.= '</div>';
							}
						}
					$result.= '</td>';
				}
			$result.= '</tr>';
			// cells
			$data_single_day_lock = [];
			for ($i = 0; $i <= 23; $i++) {
				$result.= '<tr>';
					if ($i == 0) {
						$time = '';
					} else if ($i == 12) {
						$time = $i . i18n(null, 'pm');
					} else if ($i > 12) {
						$time = ($i - 12) . i18n(null, 'pm');
					} else {
						$time = $i . i18n(null, 'am');
					}
					$result.= '<td class="numbers_account_calendar_label_holder">';
						$result.= '<div class="numbers_account_calendar_label">' . \Format::id($time) . '</div>';
					$result.= '</td>';
					foreach ($week_days as $k => $v) {
						if (isset($current_week_day) && $current_week_day == $k) {
							$result.= '<td class="numbers_account_calendar_cell numbers_account_calendar_current">';
						} else {
							$result.= '<td class="numbers_account_calendar_cell">';
						}
							if (!empty($data_arranged['single_day'][$k][$i])) {
								$zindex = 1;
								$width = 100;
								foreach ($data_arranged['single_day'][$k][$i] as $k2 => $v2) {
									$date1 = new \DateTime(\Format::readDate($v2['work_starts'], 'datetime'));
									$date2 = new \DateTime(\Format::readDate($v2['work_ends'] ?? \Helper\Date::addInterval($v2['work_starts'], '+1 hour'), 'datetime'));
									$top = (int) $date1->format('i');
									$height = (int) (($date2->getTimestamp() - $date1->getTimestamp()) / (60 * 60 / 80));
									// see if we have overlaps
									if (!empty($data_single_day_lock[$k])) {
										foreach ($data_single_day_lock[$k] as $v3) {
											if (($date1 >= $v3['start'] && $date1 <= $v3['end']) || $date2 <= $v3['end']) {
												$width-= 5;
											}
										}
									}
									$onclick = "";
									if (!empty($options['onclick_renderer'])) {
										$method = \Factory::method($options['onclick_renderer'], null, true);
										$onclick = ' onclick= "' . call_user_func_array([$method[0], $method[1]], [$v2]) . '" ';
									}
									$result.= '<div class="numbers_account_calendar_singleday_interval" style="top: ' . $top . 'px; height: ' . $height . 'px; width: ' . $width . '%; z-index: ' . $zindex . '; color: ' . $v2['slot_text_color']  . '; background-color: ' . $v2['slot_color'] . ';" ' . $onclick . '>';
										if (!empty($options['cell_renderer'])) {
											$method = \Factory::method($options['cell_renderer'], null, true);
											$result.= call_user_func_array([$method[0], $method[1]], [$v2]);
										} else {
											$result.= $v2['name'];
										}
									$result.= '</div>';
									$zindex++;
									// push
									if (!isset($data_single_day_lock[$k])) $data_single_day_lock[$k] = [];
									$data_single_day_lock[$k][$date1->getTimestamp()] = [
										'start' => $date1,
										'end' => $date2,
									];
								}
							} else {
								$result.= '&nbsp;';
							}
						$result.= '</td>';
					}
				$result.= '</tr>';
			}
		$result.= '</table>';
		$result = '<div class="numbers_frontend_form_list_table_wrapper_outer"><div class="numbers_frontend_form_list_table_wrapper_inner">' . $result . '</div></div>';
		return $result;
	}
}