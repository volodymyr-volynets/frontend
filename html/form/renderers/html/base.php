<?php

class numbers_frontend_html_form_renderers_html_base {

	/**
	 * Form object
	 *
	 * @var object
	 */
	private $object;

	/**
	 * Render
	 *
	 * @param object_form_base $object
	 * @return string
	 */
	public function render(object_form_base $object) : string {
		// save object
		$this->object = $object;
		// ajax requests from another form
		if ($this->object->flag_another_ajax_call) {
			return null;
		}
		$this->object->tabindex = 1;
		// css & js
		numbers_frontend_media_libraries_jssha_base::add();
		layout::add_js('/numbers/media_submodules/numbers_frontend_html_form_renderers_html_media_js_base.js', -10000);
		layout::add_css('/numbers/media_submodules/numbers_frontend_html_form_renderers_html_media_css_base.css', -10000);
		// include master js
		if (!empty($this->object->master_object) && method_exists($this->object->master_object, 'add_js')) {
			$this->object->master_object->add_js();
		}
		// include js
		$filename = str_replace('_form_', '_media_js_', $this->object->form_class) . '.js';
		if (file_exists('./../libraries/vendor/' . str_replace('_', '/', $filename))) {
			layout::add_js('/numbers/media_submodules/' . $filename);
		}
		$this->object->misc_settings['extended_js_class'] = 'numbers.' . $this->object->form_class;
		// include css
		$filename = str_replace('_form_', '_media_css_', $this->object->form_class) . '.css';
		if (file_exists('./../libraries/vendor/' . str_replace('_', '/', $filename))) {
			layout::add_css('/numbers/media_submodules/' . $filename);
		}
		// load mask
		numbers_frontend_media_libraries_loadmask_base::add();
		// new record action
		$mvc = application::get('mvc');
		if (!empty($this->object->options['actions']['new']) && object_controller::can('record_new')) {
			$onclick = 'return confirm(\'' . strip_tags(i18n(null, object_content_messages::confirm_blank)) . '\');';
			$this->object->actions['form_new'] = ['value' => 'New', 'sort' => -31000, 'icon' => 'file-o', 'href' => $mvc['full'] . '?' . $this->object::button_submit_blank . '=1', 'onclick' => $onclick, 'internal_action' => true];
		}
		// back to list
		if (!empty($this->object->options['actions']['back']) && object_controller::can('list_view')) {
			$this->object->actions['form_back'] = ['value' => 'Back', 'sort' => -32000, 'icon' => 'arrow-left', 'href' => $mvc['controller'] . '/_index', 'internal_action' => true];
		}
		// refresh button
		if (!empty($this->object->options['actions']['refresh'])) {
			$url = $mvc['full'];
			if ($this->object->values_loaded) {
				$url.= '?' . http_build_query2($this->object->pk);
			}
			$this->object->actions['form_refresh'] = ['value' => 'Refresh', 'sort' => 32000, 'icon' => 'refresh', 'href' => $url, 'internal_action' => true];
		}
		// assembling everything into result variable
		$result = [];
		// order containers based on order column
		array_key_sort($this->object->data, ['order' => SORT_ASC]);
		foreach ($this->object->data as $k => $v) {
			if (!$v['flag_child']) {
				if ($v['type'] == 'fields' || $v['type'] == 'details') {
					// reset tabs
					$this->object->current_tab = [];
					$temp = $this->object->render_container($k);
					if ($temp['success']) {
						$result[$k] = $temp['data'];
					}
				} else if ($v['type'] == 'tabs') { // tabs
					$tab_id = "form_tabs_{$this->object->form_link}_{$k}";
					$tab_header = [];
					$tab_values = [];
					$tab_options = [];
					$have_tabs = false;
					// sort rows
					array_key_sort($v['rows'], ['order' => SORT_ASC]);
					foreach ($v['rows'] as $k2 => $v2) {
						$this->object->current_tab[] = "{$tab_id}_{$k2}";
						$labels = '';
						foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
							$labels.= html::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->object->current_tab) . '__' . $v78]);
						}
						$tab_header[$k2] = i18n(null, $v2['options']['label_name']) . $labels;
						$tab_values[$k2] = '';
						// handling override_tabs method
						if (!empty($this->object->wrapper_methods['override_tabs']['main'])) {
							$tab_options[$k2] = call_user_func_array($this->object->wrapper_methods['override_tabs']['main'], [& $this, & $v2, & $k2, & $this->object->values]);
							if (empty($tab_options[$k2]['hidden'])) {
								$have_tabs = true;
							}
						} else {
							$have_tabs = true;
						}
						// tab index for not hidden tabs
						if (empty($tab_options[$k2]['hidden'])) {
							$tab_options[$k2]['tabindex'] = $this->object->tabindex;
							$this->object->tabindex++;
						}
						// render containers
						array_key_sort($v2['elements'], ['order' => SORT_ASC]);
						foreach ($v2['elements'] as $k3 => $v3) {
							$temp = $this->object->render_container($v3['options']['container']);
							if ($temp['success']) {
								$tab_values[$k2].= $temp['data']['html'];
							}
						}
						// remove last element from an array
						array_pop($this->object->current_tab);
					}
					// if we do not have tabs
					if ($have_tabs) {
						$result[$k]['html'] = html::tabs([
							'id' => $tab_id,
							'class' => 'form-tabs',
							'header' => $tab_header,
							'options' => $tab_values,
							'tab_options' => $tab_options
						]);
					}
				}
			}
		}
		// formatting data
		$temp = [];
		foreach ($result as $k => $v) {
			$temp[] = $v['html'];
		}
		$result = implode('', $temp);
		// we need to skip internal actions
		if (!empty($this->object->options['skip_actions'])) {
			foreach ($this->object->actions as $k0 => $v0) {
				if (!empty($v0['internal_action'])) {
					unset($this->object->actions[$k0]);
				}
			}
		}
		// rendering actions
		if (!empty($this->object->actions)) {
			$value = '<div style="text-align: right;">' . $this->render_actions() . '</div>';
			$value.= '<hr class="simple" />';
			$result = $value . $result;
		}
		// messages
		if (!empty($this->object->errors['general'])) {
			$messages = '';
			foreach ($this->object->errors['general'] as $k => $v) {
				$messages.= html::message(['options' => $v, 'type' => $k]);
			}
			$result = '<div class="form_message_container">' . $messages . '</div>' . $result;
		}
		// couple hidden fields
		$result.= html::hidden(['name' => '__form_link', 'value' => $this->object->form_link]);
		$result.= html::hidden(['name' => '__form_values_loaded', 'value' => $this->object->values_loaded]);
		$result.= html::hidden(['name' => '__form_onchange_field_values_key', 'value' => '']);
		// bypass values
		if (!empty($this->object->options['bypass_hidden_values'])) {
			foreach ($this->object->options['bypass_hidden_values'] as $k => $v) {
				$result.= html::hidden(['name' => $k, 'value' => $v]);
			}
		}
		if (!empty($this->object->options['bypass_hidden_from_input'])) {
			foreach ($this->object->options['bypass_hidden_from_input'] as $v) {
				$result.= html::hidden(['name' => $v, 'value' => $this->object->options['input'][$v] ?? '']);
			}
		}
		// js to update counters in tabs
		if (!empty($this->object->errors['tabs'])) {
			foreach ($this->object->errors['tabs'] as $k => $v) {
				layout::onload("$('#{$k}').html($v); $('#{$k}').show();");
			}
		}
		// if we have form
		if (empty($this->object->options['skip_form'])) {
			$mvc = application::get('mvc');
			$result = html::form([
				'action' => $mvc['full'],
				'name' => "form_{$this->object->form_link}_form",
				'id' => "form_{$this->object->form_link}_form",
				'value' => $result,
				'onsubmit' => empty($this->object->options['no_ajax_form_reload']) ? 'return numbers.form.on_form_submit(this);' : null
			]);
		}
		// if we came from ajax we return as json object
		if (!empty($this->object->options['input']['__ajax'])) {
			$result = [
				'success' => true,
				'error' => [],
				'html' => $result,
				'js' => layout::$onload
			];
			layout::render_as($result, 'application/json');
		}
		$result = "<div id=\"form_{$this->object->form_link}_form_mask\"><div id=\"form_{$this->object->form_link}_form_wrapper\">" . $result . '</div></div>';
		// if we have segment
		if (isset($this->object->options['segment'])) {
			$temp = is_array($this->object->options['segment']) ? $this->object->options['segment'] : [];
			$temp['value'] = $result;
			$result = html::segment($temp);
		}
		return $result;
	}

	/**
	 * Render actions
	 *
	 * @return string
	 */
	private function render_actions() {
		// sorting first
		array_key_sort($this->object->actions, ['sort' => SORT_ASC], ['sort' => SORT_NUMERIC]);
		// looping through data and building html
		$temp = [];
		foreach ($this->object->actions as $k => $v) {
			$icon = !empty($v['icon']) ? (html::icon(['type' => $v['icon']]) . ' ') : '';
			$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
			$value = !empty($v['value']) ? i18n(null, $v['value']) : '';
			$href = $v['href'] ?? 'javascript:void(0);';
			$temp[] = html::a(array('value' => $icon . $value, 'href' => $href, 'onclick' => $onclick));
		}
		return implode(' ', $temp);
	}
}
