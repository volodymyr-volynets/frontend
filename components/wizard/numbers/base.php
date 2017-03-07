<?php

class numbers_frontend_components_wizard_numbers_base implements numbers_frontend_components_wizard_interface_base {

	/**
	 * see html::wizard();
	 */
	public static function wizard($options = []) {
		// if we have no options we render nothing
		if (empty($options['options'])) return;
		// include js & css files
		layout::add_css('/numbers/media_submodules/numbers_frontend_components_wizard_numbers_media_css_base.css', 10000);
		// font awesome icons
		library::add('fontawesome');
		// render
		$width = round(100 / count($options['options']), 2);
		$result = '<table class="numbers_frontend_components_wizard_numbers_base">';
			$row1 = '<tr>';
			$row2 = '<tr>';
			$row3 = '<tr>';
				$first = key($options['options']);
				end($options['options']);
				$last = key($options['options']);
				if (empty($options['options'][$options['step'] ?? ''])) {
					$options['step'] = $first;
				}
				$flag_step_found = false;
				$flag_description_found = false;
				$type = $options['type'] ?? 'primary';
				foreach ($options['options'] as $k => $v) {
					$row1.= '<th width="' . $width . '%" class="numbers_frontend_components_wizard_numbers_base_header">' . ($v['name'] ?? '') . '</th>';
					// progress bar
					$row2.= '<td>';
						$row2.= '<div class="numbers_frontend_components_wizard_numbers_base_holder">';
							if (!$flag_step_found) {
								$class = 'numbers_frontend_components_wizard_numbers_base_active label-' . $type;
							} else {
								$class = 'numbers_frontend_components_wizard_numbers_base_inactive';
							}
							if ($k != $first) {
								$row2.= '<div class="numbers_frontend_components_wizard_numbers_base_start ' . $class . '">&nbsp;</div>';
							}
							$row2.= '<div class="numbers_frontend_components_wizard_numbers_base_center ' . $class . '">&nbsp;</div>';
							$row2.= '<div class="numbers_frontend_components_wizard_numbers_base_circle ' . $class . '">&nbsp;</div>';
							if ($options['step'] == $k) {
								$flag_step_found = true;
								$class = 'numbers_frontend_components_wizard_numbers_base_inactive';
							}
							if ($k != $last) {
								$row2.= '<div class="numbers_frontend_components_wizard_numbers_base_end ' . $class . '">&nbsp;</div>';
							}
						$row2.= '</div>';
					$row2.= '</td>';
					$row3.= '<td class="numbers_frontend_components_wizard_numbers_base_description">' . ($v['description'] ?? '') . '</td>';
					if (!empty($v['description'])) {
						$flag_description_found = true;
					}
				}
			$row1.= '</tr>';
			$row2.= '</tr>';
			$row3.= '</tr>';
			$result.= $row1 . $row2;
			if ($flag_description_found) {
				$result.= $row3;
			}
		$result.= '</table>';
		return $result;
	}
}