<?php

namespace Numbers\Frontend\HTML\Form\Renderers\HTML;
class Base {

	/**
	 * Form object
	 *
	 * @var object
	 */
	private $object;

	/**
	 * Render
	 *
	 * @param \Object\Form\Base $object
	 * @return string
	 */
	public function render(\Object\Form\Base & $object) : string {
		// save object
		$this->object = $object;
		// ajax requests from another form
		if ($this->object->flag_another_ajax_call) {
			return null;
		}
		$this->object->tabindex = 1;
		// css & js
		\Library::add('jsSHA');
		\Library::add('BCMath');
		\Library::add('LoadMask');
		\Layout::addJs('/numbers/media_submodules/Numbers_Frontend_HTML_Form_Renderers_HTML_Media_JS_Base.js', -10000);
		\Layout::addCss('/numbers/media_submodules/Numbers_Frontend_HTML_Form_Renderers_HTML_Media_CSS_Base.css', -10000);
		// include master js
		if (!empty($this->object->master_object) && method_exists($this->object->master_object, 'addJs')) {
			$this->object->master_object->addJs();
		}
		$this->object->misc_settings['extended_js_class'] = 'Numbers.' . str_replace('\\', '', $this->object->form_class);
		// include js
		if (!empty($this->object->options['include_js'])) {
			\Layout::addJs($this->object->options['include_js']);
		}
		// include css
		if (!empty($this->object->options['include_css'])) {
			\Layout::addCss($this->object->options['include_css']);
		}
		// acl on actions
		$this->object->options['actions'] = $this->object->options['actions'] ?? [];
		foreach ($this->object->options['actions'] as $k => $v) {
			if (!is_array($v)) continue;
			if (isset($v['action_code']) && !\Application::$controller->can($v['action_code'], 'Edit')) {
				$this->object->options['actions'][$k] = false;
			}
		}
		// new record action
		$mvc = \Application::get('mvc');
		if (!empty($this->object->options['actions']['new']) && \Application::$controller->can('Record_New', 'Edit')) {
			if ($mvc['action'] == 'Edit') {
				$onclick = 'return confirm(\'' . strip_tags(i18n(null, \Object\Content\Messages::CONFIRM_BLANK)) . '\');';
			} else {
				$onclick = '';
			}
			$params = [];
			$params[$this->object::BUTTON_SUBMIT_BLANK] = 1;
			// we need to pass module #
			if ($this->object->collection_object->primary_model->module ?? false) {
				$params['__module_id'] = $params[$this->object->collection_object->primary_model->module_column] = $this->object->values[$this->object->collection_object->primary_model->module_column];
			}
			$this->object->actions['form_new'] = ['value' => 'New', 'sort' => -31000, 'icon' => 'far fa-file', 'href' => $mvc['controller'] . '/_Edit?' . http_build_query($params), 'onclick' => $onclick, 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['new'])) {
				$this->object->actions['form_new'] = array_merge($this->object->actions['form_new'], $this->object->options['actions']['new']);
			}
		}
		// import action
		if (!empty($this->object->options['actions']['import']) && \Application::$controller->can('Import_Records', 'Import')) {
			$onclick = '';
			$params = [];
			$params[$this->object::BUTTON_SUBMIT_BLANK] = 1;
			// we need to pass module #
			if ($this->object->collection_object->primary_model->module ?? false) {
				$params['__module_id'] = $params[$this->object->collection_object->primary_model->module_column] = $this->object->values[$this->object->collection_object->primary_model->module_column];
			}
			$this->object->actions['form_import'] = ['value' => 'Import', 'sort' => -30900, 'icon' => 'fas fa-upload', 'href' => $mvc['controller'] . '/_Import?' . http_build_query($params), 'onclick' => $onclick, 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['import'])) {
				$this->object->actions['form_import'] = array_merge($this->object->actions['form_import'], $this->object->options['actions']['import']);
			}
		}
		// activate
		if (!empty($this->object->options['actions']['activate']) && \Application::$controller->can('Activate_Data', 'Activate')) {
			$onclick = '';
			$this->object->actions['form_activate'] = ['value' => 'Activate', 'sort' => -30900, 'icon' => 'fas fa-link', 'href' => $mvc['controller'] . '/_Activate', 'onclick' => $onclick, 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['activate'])) {
				$this->object->actions['form_activate'] = array_merge($this->object->actions['form_activate'], $this->object->options['actions']['activate']);
			}
		}
		// back to list/edit
		if (!empty($this->object->options['actions']['back'])) {
			$params = [];
			// we need to pass module #
			if ($this->object->collection_object->primary_model->module ?? false) {
				$params['__module_id'] = $params[$this->object->collection_object->primary_model->module_column] = $this->object->values[$this->object->collection_object->primary_model->module_column];
			}
			// __form_filter_id
			if (!empty($this->object->values['__form_filter_id'])) {
				$params['__form_filter_id'] = $this->object->values['__form_filter_id'];
			}
			$this->object->actions['form_back'] = ['value' => 'Back', 'sort' => -32000, 'icon' => 'fas fa-arrow-left', 'href' => $mvc['controller'] . '/_Index?' . http_build_query($params), 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['back'])) {
				$this->object->actions['form_back'] = array_merge($this->object->actions['form_back'], $this->object->options['actions']['back']);
			}
		}
		// anchor
		$anchor = "form_{$this->object->form_link}_form_anchor";
		// refresh action
		$refresh_params = [];
		$pdf_params = [];
		if ($this->object->values_loaded) {
			$refresh_params = $this->object->pk;
			// remove tenant
			if (!empty($this->object->collection_object->primary_model->tenant)) {
				unset($refresh_params[$this->object->collection_object->primary_model->tenant_column]);
			}
			$pdf_params = $refresh_params;
		}
		if ($this->object->collection_object->primary_model->module ?? false) {
			$refresh_params['__module_id'] = $refresh_params[$this->object->collection_object->primary_model->module_column] = $this->object->values[$this->object->collection_object->primary_model->module_column];
		}
		if (!empty($this->object->options['bypass_hidden_from_input'])) {
			foreach ($this->object->options['bypass_hidden_from_input'] as $v) {
				$refresh_params[$v] = $this->object->options['input'][$v] ?? '';
			}
		}
		if (!empty($this->object->options['collection_current_tab_id'])) {
			$refresh_params[$this->object->options['collection_current_tab_id']] = $this->object->form_link;
		}
		if (isset($this->object->options['actions']['refresh']['preserve_values'])) {
			foreach ($this->object->options['actions']['refresh']['preserve_values'] as $v) {
				$refresh_params[$v] = $this->object->values[$v] ?? null;
			}
		}
		$refresh_params['__refresh'] = rand(1000, 9999) . '_' . rand(1000, 9999) . '_' . rand(1000, 9999);
		$refresh_href = $mvc['full'] . '?' . http_build_query2($refresh_params) . "#" . $anchor;
		if (!empty($this->object->options['actions']['refresh'])) {
			$url = $mvc['full'];
			$this->object->actions['form_refresh'] = ['value' => 'Refresh', 'sort' => 32000, 'icon' => 'fas fa-sync', 'href' => $refresh_href, 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['refresh'])) {
				$this->object->actions['form_refresh'] = array_merge($this->object->actions['form_refresh'], $this->object->options['actions']['refresh']);
			}
		}
		// pdf
		if (!empty($this->object->options['actions']['pdf']) && !empty($pdf_params) && \Application::$controller->can('Record_View', 'Edit')) {
			$onclick = '';
			$crypt = new \Crypt();
			$this->object->actions['form_pdf'] = ['value' => 'Print to PDF', 'sort' => -30900, 'icon' => 'far fa-file-pdf', 'href' => $mvc['controller'] . '/_PDF?token=' . $crypt->tokenCreate(0, 'view.pdf', $pdf_params), 'onclick' => $onclick, 'internal_action' => true];
			// override
			if (is_array($this->object->options['actions']['pdf'])) {
				$this->object->actions['form_pdf'] = array_merge($this->object->actions['form_pdf'], $this->object->options['actions']['pdf']);
			}
		}
		// other actions
		foreach ($this->object->options['actions'] ?? [] as $k => $v) {
			if (in_array($k, ['refresh', 'new', 'back', 'activate'])) continue;
			$this->object->actions['form_custom_' . $k] = $v;
		}
		// assembling everything into result variable
		$result = [];
		// order containers based on order column
		array_key_sort($this->object->data, ['order' => SORT_ASC], ['order' => SORT_NUMERIC]);
		foreach ($this->object->data as $k => $v) {
			if (!$v['flag_child']) {
				if ($v['type'] == 'fields' || $v['type'] == 'details' || $v['type'] == 'trees') {
					// reset tabs
					$this->object->current_tab = [];
					// list container
					if ($k == $this->object::LIST_CONTAINER || $k == $this->object::LIST_LINE_CONTAINER) {
						$preview = $this->object->values['__preview'];
						if ((empty($preview) || $preview == 1) && $k == $this->object::LIST_CONTAINER) {
							$temp = $this->renderListContainer($k);
							if ($temp['success']) {
								$result[$k] = $temp['data'];
							}
						} else if ($preview == 2 && $k == $this->object::LIST_LINE_CONTAINER) {
							$temp = $this->renderListContainer($k);
							if ($temp['success']) {
								$result[$k] = $temp['data'];
							}
						}
					} else { // regular containers
						$temp = $this->renderContainer($k);
						if (!empty($temp['success'])) {
							$result[$k] = $temp['data'];
						}
					}
				} else if ($v['type'] == 'tabs') { // tabs
					$this->renderTabs($k, $v, $result);
				} else if ($v['type'] == 'panels') { // pannels
					$this->renderPanels($k, $v, $result);
				} else if ($v['type'] == 'modal') { // modal windows
					// reset tabs
					$this->object->current_tab = [];
					// render container
					$temp = $this->renderContainer($k);
					if ($temp['success']) {
						$result[$k]['html'] = \HTML::modal([
							'id' => $v['options']['modal_id'],
							'class' => '',
							'title' => isset($v['label_name']) ? i18n(null, $v['label_name']) : '',
							'body' => $temp['data']['html'],
						]);
						// if we have an error we need to display modal
						if (!empty($this->object->misc_settings['errors_in_modal'])) {
							foreach ($this->object->misc_settings['errors_in_modal'] as $v2) {
								\Layout::onLoad("Numbers.Modal.show('{$v2}');");
							}
						}
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
			$value = '<div style="text-align: right;">' . $this->renderActions() . '</div>';
			$value.= '<hr class="simple" />';
			$result = $value . $result;
		}
		// postponed messages
		if (!empty($_SESSION['numbers']['forms'][$this->object->form_link]['messages']) && empty($this->object->misc_settings['form_postponed_messages'])) {
			$this->object->errors['general'] = $_SESSION['numbers']['forms'][$this->object->form_link]['messages'];
			//array_merge($this->object->errors['general'] ?? [], $_SESSION['numbers']['forms'][$this->object->form_link]['messages']);
			unset($_SESSION['numbers']['forms'][$this->object->form_link]['messages']);
		}
		// messages
		if (!empty($this->object->errors['general'])) {
			$messages = '';
			foreach ($this->object->errors['general'] as $k => $v) {
				$messages.= \HTML::message(['options' => $v, 'type' => $k]);
			}
			$result = '<div class="form_message_container">' . $messages . '</div>' . $result;
		} else {
			$result = '<div class="form_message_container">' . '</div>' . $result;
		}
		// couple hidden fields
		$result.= \HTML::hidden(['name' => '__collection_link', 'value' => $this->object->options['collection_link'] ?? '']);
		$result.= \HTML::hidden(['name' => '__collection_screen_link', 'value' => $this->object->options['collection_screen_link'] ?? '']);
		// if we came from the parent
		if (!empty($this->object->options['parent_form_link'])) {
			$result.= \HTML::hidden(['name' => '__form_link', 'value' => $this->object->options['parent_form_link']]);
			$result.= \HTML::hidden(['name' => '__subform_link', 'value' => $this->object->form_link]);
		} else {
			$result.= \HTML::hidden(['name' => '__form_link', 'value' => $this->object->form_link]);
			$result.= \HTML::hidden(['name' => '__subform_link', 'value' => '']);
		}
		$result.= \HTML::hidden(['name' => '__form_values_loaded', 'value' => $this->object->values_loaded]);
		$result.= \HTML::hidden(['name' => '__form_onchange_field_values_key', 'value' => '']);
		// form is within tabs
		if (!empty($this->object->options['collection_current_tab_id'])) {
			$result.= \HTML::hidden(['name' => $this->object->options['collection_current_tab_id'], 'value' => $this->object->form_link]);
		}
		// form data in onload
		$js_data = [
			'submitted' => $this->object->misc_settings['__original_submitted'] ?? $this->object->submitted,
			'refresh' => $this->object->refresh,
			'delete' => $this->object->delete,
			'blank' => $this->object->blank,
			'values_loaded' => $this->object->values_loaded,
			'values_saved' => $this->object->values_saved,
			'values_deleted' => $this->object->values_deleted,
			'values_inserted' => $this->object->values_inserted,
			'values_updated' => $this->object->values_updated,
			'list_rendered' => $this->object->list_rendered,
			'hasErrors' => $this->object->hasErrors(),
			'anchor' => $anchor,
		];
		$js = "Numbers.Form.data['form_{$this->object->form_link}_form'] = " . json_encode($js_data) . ";\n";
		if (!$this->object->hasErrors()) {
			if ($this->object->initiator_class == 'list') {
				$js.= "Numbers.Form.listFilterSortToggle('#form_{$this->object->form_link}_form', true, false);\n";
			} else if ($this->object->initiator_class == 'report' && $this->object->submitted) {
				$js.= "Numbers.Form.listFilterSortToggle('#form_{$this->object->form_link}_form', true, false);\n";
			} else {
				$js.= "Numbers.Form.listFilterSortToggle('#form_{$this->object->form_link}_form', true, true);\n";
			}
			// on success subform
			$submitted = $this->object->misc_settings['__original_submitted'] ?? $this->object->submitted;
			if (!empty($this->object->options['on_success_refresh_collection']) && $submitted && !$this->object->refresh && !$this->object->blank) {
				$js.= "Numbers.Modal.hide('form_subform_{$this->object->form_link}_form');\n";
				if (!empty($this->object->options['collection_link'])) {
					$js.= "Numbers.Form.refreshCollectionForms('{$this->object->options['collection_link']}')\n";
				} else {
					$js.= "$('#form_{$this->object->options['parent_form_link']}_form').submit();\n";
				}
			} else if (!empty($this->object->options['on_success_refresh_parent']) && $submitted && !$this->object->refresh && !$this->object->blank) {
				$js.= "Numbers.Modal.hide('form_subform_{$this->object->form_link}_form');\n";
				$js.= "$('#form_{$this->object->options['parent_form_link']}_form').submit();\n";
			}
		}
		// redirects from links
		if (!empty($_SESSION['numbers']['tokens']['email_token']['data']['onclick'])) {
			if (!empty($this->object->form_parent->subforms)) {
				$js.= <<<TTT
					setTimeout(function() {
						{$_SESSION['numbers']['tokens']['email_token']['data']['onclick']}
					}, 1000);
TTT;
			}
			unset($_SESSION['numbers']['tokens']['email_token']['data']['onclick']);
		}
		\Layout::onLoad($js);
		// onkeypress
		if ($this->object->initiator_class == 'list' || $this->object->initiator_class == 'report') {
			\Layout::onLoad(<<<TTT
				$("#form_{$this->object->form_link}_form input").keypress(function(e) {
					if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
						$("[name='__submit_button']").click();
						return false;
					} else {
						return true;
					}
				});
TTT
);
		}
		// bypass values
		if (!empty($this->object->options['bypass_hidden_values'])) {
			foreach ($this->object->options['bypass_hidden_values'] as $k => $v) {
				$result.= \HTML::hidden(['name' => $k, 'value' => $v]);
			}
		}
		if (!empty($this->object->options['bypass_hidden_from_input'])) {
			foreach ($this->object->options['bypass_hidden_from_input'] as $v) {
				$result.= \HTML::hidden(['name' => $v, 'value' => $this->object->options['input'][$v] ?? '']);
			}
		}
		// js to update counters in tabs
		if (!empty($this->object->errors['tabs'])) {
			foreach ($this->object->errors['tabs'] as $k => $v) {
				\Layout::onload("$('#{$k}').html($v); $('#{$k}').show();");
			}
		}
		// if we have form
		if (empty($this->object->options['skip_form'])) {
			$mvc = \Application::get('mvc');
			$result = \HTML::form([
				'action' => $mvc['full'] . "#" . $anchor,
				'name' => "form_{$this->object->form_link}_form",
				'id' => "form_{$this->object->form_link}_form",
				'class' => 'numbers_frontend_form_class',
				'value' => $result,
				'onsubmit' => empty($this->object->options['no_ajax_form_reload']) ? 'return Numbers.Form.onFormSubmit(this);' : null,
				'data-no_ajax_form_reload' => !empty($this->object->options['no_ajax_form_reload']) ? 1 : '',
			]);
		}
		// active element
		if (!empty($this->object->options['input']['__ajax_form_active_element_id'])) {
			\Layout::onLoad("$('#" . $this->object->options['input']['__ajax_form_active_element_id'] . "').focus();");
		}
		// if we came from ajax we return as json object
		if (!empty($this->object->options['input']['__ajax'])) {
			$onload = '';
			// if we have collection and we inserted into main form we need to force refresh of collecion
			if ($this->object->values_inserted && !empty($this->object->options['__main_form_in_collection'])) {
				$onload.= "window.location.href = '{$refresh_href}';";
			}
			// if we need to redirect
			if (!empty($this->object->misc_settings['redirect'])) {
				$onload.= "window.location.href = '{$this->object->misc_settings['redirect']}';";
			}
			// counter in tabs
			if ($this->object->initiator_class == 'list' && !empty($this->object->options['collection_link'])) {
				$form_k = $this->object->options['parent_form_link'] ?? $this->object->form_link;
				$records_id = "form_collection_tabs_{$this->object->options['collection_link']}_{$form_k}__records";
				$num = \Format::id($this->object->misc_settings['list']['total']);
				\Layout::onLoad("$('#{$records_id}').html({$num}); $('#{$records_id}').show();");
			}
			$result = [
				'success' => true,
				'error' => [],
				'html' => $result,
				'js' => \Layout::$onload . $onload,
				'js_first' => \Layout::$onload_first,
				'media_js' => \Layout::renderJs(['return_list' => true]),
				'media_css' => \Layout::renderCss(['return_list' => true]),
				'anchor' => $anchor,
				'values_changed' => $this->object->values_saved || $this->object->values_deleted || $this->object->values_inserted || $this->object->values_updated
			];
			\Layout::renderAs($result, 'application/json');
		} else {
			if (!empty($this->object->misc_settings['redirect'])) {
				if (empty($this->object->options['on_success_refresh_collection'])) {
					\Request::redirect($this->object->misc_settings['redirect']);
				} else {
					\Layout::$onload = '';
					\Layout::onLoad("window.location.href = '{$this->object->misc_settings['redirect']}';");
				}
			}
		}
		$result = "<div id=\"form_{$this->object->form_link}_form_mask\"><div id=\"form_{$this->object->form_link}_form_wrapper\">" . $result . '</div></div>';
		// if we have segment
		if (isset($this->object->options['segment'])) {
			$temp = is_array($this->object->options['segment']) ? $this->object->options['segment'] : [];
			$temp['value'] = $result;
			if (!empty($temp['include_page_title'])) {
				$temp['header']['icon']['type'] = \Application::$controller->icon;
				$temp['header']['title'] = \Application::$controller->title;
			}
			if (isset($temp['header']['title'])) {
				$temp['header']['title'] = i18n(null, $temp['header']['title']);
			}
			if (isset($temp['footer']['title'])) {
				$temp['footer']['title'] = i18n(null, $temp['footer']['title']);
			}
			$result = \HTML::segment($temp);
			if (!empty($temp['include_page_title'])) {
				$result.= '<style>h3.page_title { display: none; }</style>';
			}
		}
		// anchor
		$result = \HTML::a(['id' => $anchor, 'value' => null]) . $result;
		// if we have a subform we need to render place holder
		if (!empty($this->object->form_parent->subforms)) {
			$result.= \HTML::div(['id' => "form_{$this->object->form_link}_form_subform_holder", 'value' => '']);
		}
		// page title on main form only
		if (in_array($this->object->initiator_class, ['form']) && empty(\Layout::$title_override)) {
			if (!empty($this->object->options['__main_form_in_collection']) || empty($this->object->options['__parent_options'])) {
				\Layout::$title_override = i18n(null, \Application::$controller->title);
				// grab name
				if (!empty($this->object->collection_object->primary_model) && !empty($this->object->values[$this->object->collection_object->primary_model->column_prefix . 'name'])) {
					\Layout::$title_override.= ' - ' . $this->object->values[$this->object->collection_object->primary_model->column_prefix . 'name'];
				}
				// primary key
				if (!empty($this->object->values_loaded)) {
					$pk_params = $this->object->pk;
					if (!empty($this->object->collection_object->primary_model->tenant)) {
						unset($pk_params[$this->object->collection_object->primary_model->tenant_column]);
					}
					if (!empty($this->object->collection_object->primary_model->module)) {
						unset($pk_params[$this->object->collection_object->primary_model->module_column]);
					}
					if (!empty($pk_params)) {
						\Layout::$title_override.= ' (' . implode(', ', $pk_params) . ')';
					}
				} else {
					\Layout::$title_override.= ' (' . i18n(null, \Object\Content\Messages::NEW) . ')';
				}
			}
		}
		return $result;
	}

	/**
	 * Render tabs
	 *
	 * @param string $k
	 * @param array $v
	 * @param array $result
	 */
	private function renderTabs($k, $v, & $result) {
		if (!empty($v['options']['skip_rendering'])) {
			return [];
		}
		$tab_id = "form_tabs_{$this->object->form_link}_{$k}";
		$tab_header = [];
		$tab_values = [];
		$tab_options = [];
		// sort rows
		array_key_sort($v['rows'], ['order' => SORT_ASC]);
		foreach ($v['rows'] as $k2 => $v2) {
			// acl on tabs
			if (!empty($v2['options']['acl'])) {
				if (!\Can::systemFeaturesExist($v2['options']['acl'])) {
					continue;
				}
			}
			$this->object->current_tab[] = "{$tab_id}_{$k2}";
			$labels = '';
			foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
				$labels.= \HTML::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->object->current_tab) . '__' . $v78]);
			}
			$tab_header[$k2] = i18n(null, $v2['options']['label_name']) . $labels;
			$tab_values[$k2] = '';
			// hidden tabs
			if (!empty($v2['options']['hidden'])) {
				$tab_options[$k2]['hidden'] = true;
			} else if (!empty($this->object->wrapper_methods['overrideTabs']['main'])) { // handling override_tabs method
				$tab_options[$k2] = call_user_func_array($this->object->wrapper_methods['overrideTabs']['main'], [& $this->object, & $v2, & $k2, & $this->object->values]);
			}
			// tab index for not hidden tabs
			if (empty($tab_options[$k2]['hidden'])) {
				$tab_options[$k2]['tabindex'] = $this->object->tabindex;
				$this->object->tabindex++;
			}
			// render containers
			array_key_sort($v2['elements'], ['order' => SORT_ASC]);
			foreach ($v2['elements'] as $k3 => $v3) {
				// pannels go first
				if (($this->object->data[$v3['container']]['options']['type'] ?? '') == 'panels') {
					$pannel_result = [];
					$this->renderPanels($k3, $this->object->data[$v3['container']], $pannel_result);
					$tab_values[$k2].= $pannel_result[$k3]['html'];
				} else { // other containers
					$temp = $this->renderContainer($v3['options']['container']);
					if (!empty($temp['success'])) {
						$tab_values[$k2].= $temp['data']['html'];
					}
				}
			}
			// remove last element from an array
			array_pop($this->object->current_tab);
		}
		// if we do not have tabs
		$class = ['form-tabs'];
		if (!empty($v['options']['class'])) $class[] = $v['options']['class'];
		$result[$k]['html'] = \HTML::tabs([
			'id' => $tab_id,
			'class' => implode(' ', $class),
			'header' => $tab_header,
			'options' => $tab_values,
			'tab_options' => $tab_options
		]);
	}

	/**
	 * Render panels
	 *
	 * @param string $k
	 * @param array $v
	 * @param array $result
	 */
	private function renderPanels($k, $v, & $result) {
		array_key_sort($v['rows'], ['order' => SORT_ASC]);
		$grid = [
			'options' => [],
		];
		foreach ($v['rows'] as $k2 => $v2) {
			array_key_sort($v2['elements'], ['order' => SORT_ASC]);
			$temp_panels = '';
			foreach ($v2['elements'] as $k3 => $v3) {
				$temp = $this->renderContainer($v3['options']['container']);
				if ($temp['success']) {
					$temp_panels.= $temp['data']['html'];
				}
			}
			// add all containers
			if (!empty($temp_panels)) {
				$grid['options'][$k][$k2][$k2] = [
					'value' => \HTML::segment([
						'header' => [
							'title' => i18n(null, $v2['options']['label_name']),
							'icon' => $v2['options']['panel_icon'] ?? null,
						],
						'type' => $v2['options']['panel_type'] ?? 'default',
						'value' => $temp_panels,
						'class' => 'numbers_frontend_form_pannel_segment',
						'pannel_skip_segment' => $v2['options']['pannel_skip_segment'] ?? false,
						'style' => 'height: 100%;',
					]),
					'options' => [
						'percent' => $v2['options']['percent'],
					]
				];
			}
		}
		$result[$k]['html'] = \HTML::grid($grid);
	}

	/**
	 * Render actions
	 *
	 * @return string
	 */
	private function renderActions() {
		// sorting first
		array_key_sort($this->object->actions, ['sort' => SORT_ASC], ['sort' => SORT_NUMERIC]);
		// looping through data and building html
		$temp = [];
		foreach ($this->object->actions as $k => $v) {
			$icon = !empty($v['icon']) ? (\HTML::icon(['type' => $v['icon']]) . ' ') : '';
			$onclick = !empty($v['onclick']) ? $v['onclick'] : '';
			$value = !empty($v['value']) ? i18n(null, $v['value']) : '';
			$href = $v['href'] ?? 'javascript:void(0);';
			$temp[] = \HTML::a(array('value' => $icon . $value, 'href' => $href, 'onclick' => $onclick));
		}
		return implode(' ', $temp);
	}

	/**
	 * Render container list
	 *
	 * @param type $container_link
	 * @return array
	 */
	public function renderListContainer(string $container_link) : array {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		if (!$this->object->list_rendered) {
			return $result;
		}
		// merge options
		$data = $this->object->misc_settings['list'] ?? [];
		$options = $this->object->form_parent->list_options ?? [];
		$result['data']['html'].= '<hr class="numbers_form_filter_sort_container" />';
		// render pagination
		if (!empty($options['pagination_top'])) {
			$data['pagination_type'] = 'top';
			$data['form_link'] = $this->object->form_link;
			$result['data']['html'].= \Factory::model($options['pagination_top'])->render($data);
		}
		// render body
		$result['data']['html'].= $this->renderListContainerDefault($data, $options);
		// render pagination
		if (!empty($options['pagination_bottom'])) {
			$data['pagination_type'] = 'bottom';
			$data['form_link'] = $this->object->form_link;
			$result['data']['html'].= \Factory::model($options['pagination_bottom'])->render($data);
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Data default renderer
	 *
	 * @return string
	 */
	private function renderListContainerDefault(& $data, & $options) {
		$result = '';
		$global_class = '';
		// if we have no rows we display a messsage
		if ($data['num_rows'] == 0) {
			return \HTML::message(['type' => 'warning', 'options' => [i18n(null, \Object\Content\Messages::NO_ROWS_FOUND)]]);
		}
		$table = [
			'width' => '100%',
			'options' => []
		];
		// prepare text search strings
		$full_text_search = false;
		if (!empty($data['full_text_search'])) {
			$data['full_text_search'] = preg_replace('/\s\s+/', ' ', trim($data['full_text_search']));
			$full_text_search = explode(' ', $data['full_text_search']);
			usort($full_text_search, function($a, $b) {
				return strlen($b) <=> strlen($a);
			});
		}
		// sort columns
		foreach ($data['columns'] as $k => $v) {
			array_key_sort($v['elements'], ['order' => SORT_ASC]);
			$data['columns'][$k]['elements'] = $v['elements'];
		}
		array_key_sort($data['columns'], ['order' => SORT_ASC]);
		// render list
		if (empty($data['preview'])) {
			// render columns
			$temp_inner = '';
			foreach ($data['columns'] as $k => $v) {
				$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_form_list_header_inner'];
				foreach ($v['elements'] as $k2 => $v2) {
					$width = $v2['options']['width'] ?? ($v2['options']['percent'] . '%');
					$inner_table['options'][1][$k2] = ['value' => i18n(null, $v2['options']['label_name']), 'align' => $v2['options']['align'] ?? 'left', 'nowrap' => true, 'width' => $width, 'tag' => 'th'];
				}
				$temp_inner.= \HTML::table($inner_table);
			}
			if (!empty($this->object->misc_settings['subforms']['url_delete'])) {
				$table['options']['header'][1] = ['value' => '&nbsp;', 'nowrap' => true, 'width' => '1%'];
				$table['options']['header'][2] = ['value' => $temp_inner, 'class' => 'numbers_frontend_form_list_header_inner_td', 'nowrap' => true, 'width' => '98%'];
				$table['options']['header'][3] = ['value' => '&nbsp;', 'nowrap' => true, 'width' => '1%'];
			} else {
				$table['options']['header'][1] = ['value' => '&nbsp;', 'nowrap' => true, 'width' => '1%'];
				$table['options']['header'][2] = ['value' => $temp_inner, 'class' => 'numbers_frontend_form_list_header_inner_td', 'nowrap' => true, 'width' => '99%'];
			}
			// generate rows
			$row_number_final = $data['offset'] + 1;
			$cached_options = [];
			foreach ($data['rows'] as $k0 => $v0) {
				// process all columns first
				$row = [];
				$temp_inner = '';
				foreach ($data['columns'] as $k => $v) {
					$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_form_list_header_inner'];
					foreach ($v['elements'] as $k2 => $v2) {
						$value = $v0[$k2] ?? null;
						// format
						if (!empty($v2['options']['format']) && empty($v2['options']['options_model'])) {
							$method = \Factory::method($v2['options']['format'], 'Format');
							if (!empty($v2['options']['format_depends'])) {
								$this->object->processParamsAndDepends($v2['options']['format_depends'], $v0, $v2['options'], true);
								$v2['options']['format_options'] = array_merge_hard($v2['options']['format_options'] ?? [], $v2['options']['format_depends']);
							}
							$value = call_user_func_array([$method[0], $method[1]], [$value, $v2['options']['format_options'] ?? []]);
						}
						// custom renderer
						if (!empty($v2['options']['custom_renderer'])) {
							$method = \Factory::method($v2['options']['custom_renderer'], $this->object->form_parent, true, [['skip_processing' => true]]);
							$value = call_user_func_array($method, [& $this->object, & $v2, & $value, & $v0]);
						}
						// process options
						if (!empty($v2['options']['options_model'])) {
							$value = $this->object->renderListContainerDefaultOptions($v2['options'], $value, $v0);
						}
						// urls
						if (!empty($v2['options']['url_edit']) && isset($this->object->misc_settings['subforms']['url_edit'])) {
							if (!empty($this->object->misc_settings['subforms']['url_edit'])) {
								$params = $this->renderURLEditHref($v0, ['json' => true]);
								$temp_collection_link = $this->object->options['collection_link'] ?? '';
								$temp_collection_screen_link = $this->object->options['collection_screen_link'] ?? '';
								$value = \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->object->form_link}', '{$this->object->misc_settings['subforms']['url_edit']['subform_link']}', {$params});", 'value' => $value]);
							}
						} else if (!empty($v2['options']['url_edit']) && \Application::$controller->can('Record_View', 'Edit')) {
							// if single record and we need to auto open
							if (!empty($this->object->options['open_edit_if_single']) && count($data['rows']) == 1) {
								if (empty($this->object->values['__list_report_filter_skip_one_record_redirect'])) {
									$this->object->redirect($this->renderURLEditHref($v0, ['skip_form_filter' => true]));
								}
							}
							$link_target = [];
							if (!empty($this->object->options['open_in_new_tab']) || \Can::userFeatureExists('SM::OPEN_RECORD_IN_NEW_TAB')) {
								$link_target['target'] = '_blank';
							}
							$value = \HTML::a(['href' => $this->renderURLEditHref($v0), 'value' => $value] + $link_target);
						}
						$width = $v2['options']['width'] ?? ($v2['options']['percent'] . '%');
						// full text search replaces
						if (!empty($full_text_search) && empty($v2['options']['skip_fts']) && empty($v2['options']['url_edit'])) {
							$this->markTextInStr($value, $full_text_search);
						}
						if (!is_html($value)) {
							$value = nl2br($value . '');
						}
						$inner_table['options'][$k][$k2] = ['value' => $value, 'nowrap' => false, 'width' => $width, 'align' => $v2['options']['align'] ?? 'left', 'class' => 'numbers_word_wrap'];
					}
					$temp_inner.= \HTML::table($inner_table);
				}
				// if we have delete link
				if (!empty($this->object->misc_settings['subforms']['url_delete'])) {
					$table['options'][$row_number_final][1] = ['value' => \Format::id($row_number_final) . '.', 'nowrap' => true, 'width' => '1%'];
					$table['options'][$row_number_final][2] = ['value' => $temp_inner, 'nowrap' => true, 'class' => 'numbers_frontend_form_list_header_inner_td', 'width' => '98%'];
					$params = $this->renderURLEditHref($v0, ['json' => true]);
					$temp_collection_link = $this->object->options['collection_link'] ?? '';
					$temp_collection_screen_link = $this->object->options['collection_screen_link'] ?? '';
					$value = \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "if (confirm('" . strip_tags(i18n(null, \Object\Content\Messages::CONFIRM_DELETE)) . "')) { Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->object->form_link}', '{$this->object->misc_settings['subforms']['url_delete']['subform_link']}', {$params}, {__submit_delete: true, __hide_popup_window: true}); }", 'value' => '<i class="far fa-trash-alt"></i>']);
					$table['options'][$row_number_final][3] = ['value' => $value, 'nowrap' => true, 'width' => '1%'];
				} else {
					$table['options'][$row_number_final][1] = ['value' => \Format::id($row_number_final) . '.', 'nowrap' => true, 'width' => '1%'];
					$table['options'][$row_number_final][2] = ['value' => $temp_inner, 'nowrap' => true, 'class' => 'numbers_frontend_form_list_header_inner_td', 'width' => '99%'];
				}
				$row_number_final++;
			}
		} else if ($data['preview'] == 1) { // preview 1 multiline grid
			// generate rows
			$row_number_final = $data['offset'] + 1;
			$cached_options = [];
			foreach ($data['rows'] as $k0 => $v0) {
				// process all columns first
				$temp_inner = '';
				foreach ($data['columns'] as $k => $v) {
					$inner_table = ['options' => [], 'width' => '100%', 'class' => 'numbers_frontend_form_list_header_inner'];
					foreach ($v['elements'] as $k2 => $v2) {
						if (empty($v2['options']['label_name'])) continue;
						$value = $v0[$k2] ?? null;
						// format
						if (!empty($v2['options']['format']) && empty($v2['options']['options_model'])) {
							$method = \Factory::method($v2['options']['format'], 'Format');
							if (!empty($v2['options']['format_depends'])) {
								$this->object->processParamsAndDepends($v2['options']['format_depends'], $v0, $v2['options'], true);
								$v2['options']['format_options'] = array_merge_hard($v2['options']['format_options'] ?? [], $v2['options']['format_depends']);
							}
							$value = call_user_func_array([$method[0], $method[1]], [$value, $v2['options']['format_options'] ?? []]);
						}
						// custom renderer
						if (!empty($v2['options']['custom_renderer'])) {
							$method = \Factory::method($v2['options']['custom_renderer'], $this->object->form_parent, true, [['skip_processing' => true]]);
							$value = call_user_func_array($method, [& $this->object, & $v2, & $value, & $v0]);
						}
						// process options
						if (!empty($v2['options']['options_model'])) {
							$value = $this->object->renderListContainerDefaultOptions($v2['options'], $value, $v0);
						}
						// urls
						if (!empty($v2['options']['url_edit']) && isset($this->object->misc_settings['subforms']['url_edit'])) {
							if (!empty($this->object->misc_settings['subforms']['url_edit'])) {
								$params = $this->renderURLEditHref($v0, ['json' => true]);
								$temp_collection_link = $this->object->options['collection_link'] ?? '';
								$temp_collection_screen_link = $this->object->options['collection_screen_link'] ?? '';
								$value = \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->object->form_link}', '{$this->object->misc_settings['subforms']['url_edit']['subform_link']}', {$params});", 'value' => $value]);
							}
						} else if (!empty($v2['options']['url_edit']) && \Application::$controller->can('Record_View', 'Edit')) {
							// if single record and we need to auto open
							if (!empty($this->object->options['open_edit_if_single']) && count($data['rows']) == 1) {
								if (empty($this->object->values['__list_report_filter_skip_one_record_redirect'])) {
									$this->object->redirect($this->renderURLEditHref($v0, ['skip_form_filter' => true]));
								}
							}
							$link_target = [];
							if (!empty($this->object->options['open_in_new_tab']) || \Can::userFeatureExists('SM::OPEN_RECORD_IN_NEW_TAB')) {
								$link_target['target'] = '_blank';
							}
							$value = \HTML::a(['href' => $this->renderURLEditHref($v0), 'value' => $value] + $link_target);
						}
						$inner_table['options'][$k . '_' . $k2][1] = ['value' => '<b>' . $v2['options']['label_name'] . ':</b>', 'width' => '15%', 'align' => 'left'];
						// full text search replaces
						if (!empty($full_text_search)) {
							$this->markTextInStr($value, $full_text_search);
						}
						$inner_table['options'][$k . '_' . $k2][2] = ['value' => $value, 'nowrap' => true, 'width' => '85%', 'align' => 'left'];
					}
					$temp_inner.= \HTML::table($inner_table);
				}
				$table['options'][$row_number_final . '_' . $k][1] = ['value' => \Format::id($row_number_final) . '.', 'nowrap' => true, 'width' => '1%'];
				$table['options'][$row_number_final . '_' . $k][2] = ['value' => $temp_inner, 'nowrap' => true, 'width' => '99%'];
				$row_number_final++;
			}
		} else if ($data['preview'] == 2) { // single line grid
			$global_class = 'numbers_frontend_form_list_table_wrapper_single_line';
			// generate rows
			$row_number_final = $data['offset'] + 1;
			$cached_options = [];
			// header
			$columns_counter = 1;
			$table['header'][$columns_counter] = ['value' => '&nbsp;', 'nowrap' => true, 'width' => '1%'];
			$columns_counter++;
			foreach ($data['columns']['row1']['elements'] as $k2 => $v2) {
				$table['header'][$columns_counter] = ['value' => i18n(null, $v2['options']['label_name']), 'nowrap' => 'nowrap'];
				$columns_counter++;
			}
			foreach ($data['rows'] as $k0 => $v0) {
				$columns_counter = 1;
				$table['options'][$row_number_final . '_' . $k][$columns_counter] = ['value' => \Format::id($row_number_final) . '.', 'nowrap' => true, 'width' => '1%'];
				$columns_counter++;
				foreach ($data['columns']['row1']['elements'] as $k2 => $v2) {
					$value = $v0[$k2] ?? null;
					// format
					if (!empty($v2['options']['format']) && empty($v2['options']['options_model'])) {
						$method = \Factory::method($v2['options']['format'], 'Format');
						$value = call_user_func_array([$method[0], $method[1]], [$value, $v2['options']['format_options'] ?? []]);
					}
					// custom renderer
					if (!empty($v2['options']['custom_renderer'])) {
						$method = \Factory::method($v2['options']['custom_renderer'], $this->object->form_parent, true, [['skip_processing' => true]]);
						$value = call_user_func_array($method, [& $this->object, & $v2, & $value, & $v0]);
					}
					// process options
					if (!empty($v2['options']['options_model'])) {
						$value = $this->object->renderListContainerDefaultOptions($v2['options'], $value, $v0);
					}
					// urls
					if (!empty($v2['options']['url_edit']) && isset($this->object->misc_settings['subforms']['url_edit'])) {
						if (!empty($this->object->misc_settings['subforms']['url_edit'])) {
							$params = $this->renderURLEditHref($v0, ['json' => true]);
							$temp_collection_link = $this->object->options['collection_link'] ?? '';
							$temp_collection_screen_link = $this->object->options['collection_screen_link'] ?? '';
							$value = \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "Numbers.Form.openSubformWindow('{$temp_collection_link}', '{$temp_collection_screen_link}', '{$this->object->form_link}', '{$this->object->misc_settings['subforms']['url_edit']['subform_link']}', {$params});", 'value' => $value]);
						}
					} else if (!empty($v2['options']['url_edit']) && \Application::$controller->can('Record_View', 'Edit')) {
						// if single record and we need to auto open
						if (!empty($this->object->options['open_edit_if_single']) && count($data['rows']) == 1) {
							if (empty($this->object->values['__list_report_filter_skip_one_record_redirect'])) {
								$this->object->redirect($this->renderURLEditHref($v0, ['skip_form_filter' => true]));
							}
						}
						$link_target = [];
						if (!empty($this->object->options['open_in_new_tab']) || \Can::userFeatureExists('SM::OPEN_RECORD_IN_NEW_TAB')) {
							$link_target['target'] = '_blank';
						}
						$value = \HTML::a(['href' => $this->renderURLEditHref($v0), 'value' => $value] + $link_target);
					}
					// full text search replaces
					if (!empty($full_text_search)) {
						$this->markTextInStr($value, $full_text_search);
					}
					$nowrap = [];
					if (!empty($v2['options']['nowrap'])) {
						$nowrap['nowrap'] = 'nowrap';
					}
					$table['options'][$row_number_final . '_' . $k][$columns_counter] = ['value' => $value] + $nowrap;
					$columns_counter++;
				}
				$row_number_final++;
			}
		}
		return '<div class="numbers_frontend_form_list_table_wrapper_outer ' . $global_class . '"><div class="numbers_frontend_form_list_table_wrapper_inner">' . \HTML::table($table) . '</div></div>';
	}

	/**
	 * Mark text in a string
	 *
	 * @param mixed $str
	 * @param array $text
	 */
	private function markTextInStr(& $str, array $text) {
		if (isset($str)) {
			$str.= '';
			foreach ($text as $v) {
				$str = preg_replace("/(" . preg_quote($v) . ")/i", '<span class="numbers_frontend_form_list_text_marks">$1</span>', $str);
			}
		}
	}

	/**
	 * Generate edit URL
	 *
	 * @param array $values
	 * @param array $options
	 *	boolean json - return json object
	 *	boolean skip_form_filter
	 * @return string
	 */
	public function renderURLEditHref($values, $options = []) {
		if (!empty($this->object->form_parent->query_primary_model)) {
			$model = \Factory::model($this->object->form_parent->query_primary_model, true);
		} else {
			$model = $this->object->collection_object->primary_model;
		}
		$pk = [];
		foreach ($model->pk as $v) {
			// skip tenant
			if ($model->tenant && $v == $model->tenant_column) continue;
			$pk[$v] = $values[$v];
		}
		// we need to pass module #
		if ($model->module ?? false) {
			$pk['__module_id'] = $values[$model->module_column];
		}
		// __form_filter_id
		if (!empty($this->object->misc_settings['list']['__form_filter_id']) && empty($options['skip_form_filter'])) {
			$pk['__form_filter_id'] = $this->object->misc_settings['list']['__form_filter_id'];
		}
		if (!empty($options['json'])) {
			// bypass variables
			if (!empty($this->object->options['bypass_hidden_from_input'])) {
				foreach ($this->object->options['bypass_hidden_from_input'] as $v) {
					$pk[$v] = $this->object->options['input'][$v] ?? '';
				}
			}
			return json_encode($pk);
		} else {
			return \Application::get('mvc.controller') . '/_Edit?' . http_build_query2($pk);
		}
	}

	/**
	 * Render form component
	 *
	 * @param string $container_link
	 */
	public function renderContainer($container_link) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		// custom html
		if (!empty($this->object->data[$container_link]['options']['__html'])) {
			$result['data']['html'] = & $this->object->data[$container_link]['options']['__html'];
			$result['success'] = true;
			return $result;
		}
		// custom renderer
		if (!empty($this->object->data[$container_link]['options']['custom_renderer'])) {
			$separator = '';
			if (!empty($this->object->data[$container_link]['options']['report_renderer'])) {
				if (!$this->object->hasErrors() && !empty($this->object->process_submit[$this::BUTTON_SUBMIT_SAVE])) {
					// initialize the report
					$this->object->report_object->initialize($this, ['i18n' => true]);
					$separator = '<hr/>';
					goto render_custom_renderer;
				}
			} else {
render_custom_renderer:
				$method = \Factory::method($this->object->data[$container_link]['options']['custom_renderer'], $this->object->form_parent);
				// important to use $this if its the same class
				if ($method[0] == $this->object->form_class) {
					$method[0] = & $this->object->form_parent;
				} else if (!is_object($method[0])) {
					$method[0] = \Factory::model($method[0], true);
				}
				$this->object->misc_settings['currently_rendering'] = $container_link;
				$temp = call_user_func_array($method, [& $this->object]);
				if (is_string($temp)) {
					$result['data']['html'] = $separator . $temp;
					$result['success'] = true;
					return $result;
				} else {
					return $temp;
				}
			}
		}
		// if its details we need to render it differently
		if (($this->object->data[$container_link]['type'] ?? '') == 'details') {
			return $this->renderContainerTypeDetails($container_link);
		}
		// if its tree we need to render it differently
		if (($this->object->data[$container_link]['type'] ?? '') == 'trees') {
			return $this->renderContainerTypeTrees($container_link);
		}
		// render tabs
		if (($this->object->data[$container_link]['type'] ?? '') == 'tabs') { // tabs
			$temp_result = [];
			$this->renderTabs($container_link, $this->object->data[$container_link], $temp_result);
			$result['data']['html'].= $temp_result[$container_link]['html'];
			$result['success'] = true;
			return $result;
		}
		// sorting rows
		if (!isset($this->object->data[$container_link]['rows'])) {
			$this->object->data[$container_link]['rows'] = [];
		}
		array_key_sort($this->object->data[$container_link]['rows'], ['order' => SORT_ASC]);
		// grouping data by row type
		// todo: handle separator
		$grouped = [];
		$index = 0;
		$last_type = null;
		foreach ($this->object->data[$container_link]['rows'] as $k => $v) {
			if (!$last_type) {
				$grouped[$index][] = [
					'type' => $v['type'],
					'key' => $k,
					'value' => $v
				];
				$last_type = $v['type'];
			} else {
				// if row type is different
				if ($last_type != $v['type']) {
					$index++;
				}
				$grouped[$index][] = [
					'type' => $v['type'],
					'key' => $k,
					'value' => $v
				];
				$last_type = $v['type'];
			}
		}
		// rendering
		foreach ($grouped as $k => $v) {
			$first = current($v);
			$result['data']['html'].= $this->{'renderRow' . ucfirst($first['type'])}($v, [
				'class' => $this->object->data[$container_link]['options']['class'] ?? null,
				'container_options' => $this->object->data[$container_link]['options'] ?? []
			]);
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Render container type trees
	 *
	 * @param string $container_link
	 * @return boolean
	 */
	public function renderContainerTypeTrees($container_link) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		// sorting rows
		array_key_sort($this->object->data[$container_link]['rows'], ['order' => SORT_ASC]);
		// get the data
		$key = $this->object->data[$container_link]['options']['details_key'];
		$data = $this->object->values[$key] ?? [];
		// names only
		if ($this->object->data[$container_link]['options']['details_rendering_type'] == 'name_only') {
			$result['data']['html'] = $this->renderContainerTypeTreeNamesOnly($this->object->data[$container_link]['rows'], $data, $this->object->data[$container_link]['options']);
		}
		$result['success'] = true;
		return $result;
	}

	/**
	 * Render Tree Name Only
	 *
	 * @param array $rows
	 * @param array $values
	 * @param array $options
	 * @return string
	 */
	public function renderContainerTypeTreeNamesOnly($rows, $values, $options = []) {
		$result = '';
		$new_data = [];
		$row_number = 1;
		foreach ($values as $k0 => $v0) {
			$key2 = $v0[$options['details_tree_key']];
			$method = \Factory::method($options['details_tree_name_only_custom_renderer'], $this->object->form_parent, false, [['skip_processing' => true]]);
			// important to use $this if its the same class
			if ($method[0] == $this->object->form_class) {
				$method[0] = & $this->object->form_parent;
			} else if (!is_object($method[0])) {
				$method[0] = \Factory::model($method[0], true, [['skip_processing' => true]]);
			}
			$new_data[$key2] = call_user_func_array($method, [& $this->object, & $rows, & $v0]);
			$new_data[$key2]['__parent'] = $v0[$options['details_tree_parent_key']];
			$new_data[$key2]['toolbar'] = $new_data[$key2]['toolbar'] ?? [];
			// we need to process all fields as hidden
			foreach ($rows as $k => $v) {
				// row_id
				if (empty($options['details_parent_key'])) {
					$row_id_temp = str_replace('\\', '_', $options['details_key']);
					$row_id = "form_{$this->object->form_link}_details_{$row_id_temp}_{$row_number}_row";
				} else {
					$row_details_parent_key = str_replace('\\', '_', $options['details_parent_key']);
					$row_details_key = str_replace('\\', '_', $options['details_key']);
					$row_id = "form_{$this->object->form_link}_subdetails_{$row_details_parent_key}_{$options['__parent_row_number']}_{$row_details_key}_{$row_number}_row";
				}
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					if ($first_key != $this->object::SEPARATOR_HORIZONTAL) {
						$first['prepend_to_field'] = ':';
						foreach ($v2 as $k3 => $v3) {
							// generate id, name and error name
							if (empty($options['details_parent_key'])) {
								// 1 to 1
								if (!empty($options['details_11'])) {
									$name = "{$options['details_key']}[{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_details_{$id01}_{$k3}";
									$error_name = "{$options['details_key']}[{$k3}]";
									$values_key = [$options['details_key'], $k3];
									$field_values_key = [$options['details_key'], $k3];
								} else { // 1 to M
									$name = "{$options['details_key']}[{$row_number}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_details_{$id01}_{$row_number}_{$k3}";
									$error_name = "{$options['details_key']}[{$k0}][{$k3}]";
									$values_key = [$options['details_key'], $k0, $k3];
									$field_values_key = [$options['details_key'], $row_number, $k3];
								}
							} else {
								// 1 to 1
								if (!empty($options['details_11'])) {
									$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_parent_key']));
									$id02 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_subdetails_{$id01}_{$options['__parent_row_number']}_{$id02}_{$row_number}_{$k3}";
									$error_name = "{$options['details_parent_key']}[{$options['__parent_key']}][{$options['details_key']}][{$k3}]";
									$values_key = [$options['details_parent_key'], $options['__parent_key'], $options['details_key'], $k3];
									$field_values_key = [$options['details_parent_key'], $options['__parent_row_number'], $options['details_key'], $k3];
								} else {
									$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k0}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_parent_key']));
									$id02 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_subdetails_{$id01}_{$options['__parent_row_number']}_{$id02}_{$row_number}_{$k3}";
									$error_name = "{$options['details_parent_key']}[{$options['__parent_key']}][{$options['details_key']}][{$k0}][{$k3}]";
									$values_key = [$options['details_parent_key'], $options['__parent_key'], $options['details_key'], $k0, $k3];
									$field_values_key = [$options['details_parent_key'], $options['__parent_row_number'], $options['details_key'], $row_number, $k3];
								}
							}
							// hidden row
							$hidden = false;
							if ($k === $this->object::HIDDEN && !\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
								$hidden = true;
							}
							// generate proper element
							$value_options = $v3;
							$value_options['options']['id'] = $id;
							$value_options['options']['name'] = $name;
							$value_options['options']['error_name'] = $error_name;
							$value_options['options']['details_parent_key'] = $options['details_parent_key'] ?? null;
							$value_options['options']['__parent_row_number'] = $options['__parent_row_number'] ?? null;
							$value_options['options']['__row_number'] = $row_number;
							$value_options['options']['__new_row'] = false;
							// need to set values_key
							$value_options['options']['values_key'] = $values_key;
							$value_options['options']['field_values_key'] = $field_values_key;
							$value_options['options']['__detail_values'] = $options['__detail_values'] ?? null;
							// we need to pass proper options
							$result.= $this->renderElementValue($value_options, $v0[$k3] ?? null, $v0);
						}
					}
				}
			}
			$row_number++;
		}
		$new_data = \Helper\Tree::convertByParent($new_data, '__parent');
		$result.= \HTML::tree(['options' => $new_data, 'i18n' => $options['details_tree_i18n'] ?? true]);
		return $result;
	}

	/**
	 * Render container with type details
	 *
	 * @param string $container_link
	 * @return array
	 */
	public function renderContainerTypeDetails($container_link) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		// sorting rows
		array_key_sort($this->object->data[$container_link]['rows'], ['order' => SORT_ASC]);
		// get the data
		$key = $this->object->data[$container_link]['options']['details_key'];
		$data = $this->object->values[$key] ?? [];
		// details_unique_select
		// todo - move to get_all_values
		if (!empty($this->object->misc_settings['details_unique_select'][$key])) {
			foreach ($this->object->misc_settings['details_unique_select'][$key] as $k => $v) {
				foreach ($data as $k2 => $v2) {
					if (!empty($v2[$k])) {
						// process json options
						if (!empty($this->object->detail_fields[$key]['elements'][$k]['options']['json_contains'])) {
							$temp = [];
							foreach ($this->object->detail_fields[$key]['elements'][$k]['options']['json_contains'] as $k01 => $v01) {
								$temp[$k01] = array_key_get($v2, $v01);
							}
							$value = \Object\Table\Options::optionJsonFormatKey($temp);
						} else { // regular values
							$value = $v2[$k];
						}
						$this->object->misc_settings['details_unique_select'][$key][$k][$value] = $value;
					}
				}
			}
		}
		// rendering
		$result['data']['html'] = $this->renderContainerTypeDetailsRows($this->object->data[$container_link]['rows'], $data, $this->object->data[$container_link]['options']);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Render container with type sub details
	 *
	 * @param string $container_link
	 * @param array $options
	 * @return array
	 */
	public function renderContainerTypeSubdetails($container_link, $options = []) {
		$result = [
			'success' => false,
			'error' => [],
			'data' => [
				'html' => '',
				'js' => '',
				'css' => ''
			]
		];
		// sorting rows
		array_key_sort($this->object->data[$container_link]['rows'], ['order' => SORT_ASC]);
		// get the data
		$key = $this->object->data[$container_link]['options']['details_key'];
		$parent_key = $this->object->data[$container_link]['options']['details_parent_key'];
		$data = $options['__values'];
		// details_unique_select
		if (!empty($this->object->misc_settings['details_unique_select'][$parent_key . '::' . $key])) {
			foreach ($this->object->misc_settings['details_unique_select'][$parent_key . '::' . $key] as $k => $v) {
				foreach ($data as $k2 => $v2) {
					// todo process json_contains
					if (!empty($v2[$k])) {
						$this->object->misc_settings['details_unique_select'][$parent_key . '::' . $key][$k][$options['__parent_row_number']][$v2[$k]] = $v2[$k];
					}
				}
			}
		}
		// merge options
		$options2 = array_merge_hard($this->object->data[$container_link]['options'], $options);
		// rendering
		$result['data']['html'] = $this->renderContainerTypeDetailsRows($this->object->data[$container_link]['rows'], $data, $options2);
		$result['success'] = true;
		return $result;
	}

	/**
	 * Details - render table
	 *
	 * @param array $rows
	 * @param array $values
	 * @param array $options
	 */
	public function renderContainerTypeDetailsRows($rows, $values, $options = []) {
		$result = '';
		// building table
		$table = [
			'header' => [
				'row_number' => '',
				'row_data' => '',
				'row_delete' => ''
			],
			'options' => [],
			'skip_header' => true
		];
		if (!empty($options['details_11'])) {
			$table['class'] = 'table grid_table_details_11';
			$table['header'] = [
				'row_data' => ''
			];
		}
		// header rows for table
		if ($options['details_rendering_type'] == 'table') {
			$data = [];
			foreach ($rows as $k => $v) {
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					foreach ($v2 as $k3 => $v3) {
						// hidden row
						if ($k === $this->object::HIDDEN && !\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
						}
						$data['options'][$k][$k2][$k3] = [
							'label' => $this->renderElementName($first),
							'options' => $v3['options'],
							'row_class' => $v3['options']['row_class'] ?? null
						];
					}
				}
			}
			// add a row to a table
			$table['options']['__header'] = [
				'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
				'row_data' => ['value' => \HTML::grid($data), 'width' => (!empty($options['details_11']) ? '100%' : '98%')],
				'row_delete' => ['value' => '&nbsp;', 'width' => '1%'],
			];
		}
		// max_records_warning_message
		if (isset($this->object->misc_settings['max_records'][$options['details_key']])) {
			if ($this->object->misc_settings['max_records'][$options['details_key']] > $options['details_max_records']) {
				$message = i18n(null, $options['details_max_records_warning_message'], ['replace' => [
				    '[number]' => \Format::id($options['details_max_records'])
				]]);
				$table['options'][PHP_INT_MAX] = [
					'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
					'row_data' => \HTML::message(['type' => 'warning', 'options' => [$message]])
				];
				goto render_table;
			}
		}
		// we must sort
		array_key_sort($rows, ['order' => SORT_ASC]);
		// generating rows
		$row_number = 1;
		// 1 to 1
		if (!empty($options['details_11'])) {
			$max_rows = 1;
			$processing_values = 1;
		} else {
			$max_rows = count($values);
			if (empty($this->object->misc_settings['global']['readonly']) && empty($this->object->misc_settings['acl_subresource_locks'][$options['container_link']]['no_new'])) {
				$max_rows+= ($options['details_new_rows'] ?? 0);
			} else {
				$options['details_new_rows'] = 0;
			}
			$processing_values = !empty($values);
		}
		do {
			// we exit if there's no rows and if we have no values
			if ($row_number > $max_rows) break;
			// maximum number of rows
			if (!empty($options['details_max_rows']) && $row_number > $options['details_max_rows']) break;
			// render
			$data = [
				'options' => []
			];
			// grab next element from an array
			if ($processing_values) {
				if (!empty($options['details_11'])) {
					$k0 = null;
					$v0 = $values;
				} else {
					$k0 = key($values);
					$v0 = current($values);
				}
			} else {
				$k0 = $row_number;
				$v0 = [];
				// details_unique_select_field
				if (!empty($options['details_unique_select_field'])) {
					$column = $rows[$options['details_unique_select_field'][0]]['elements'][$options['details_unique_select_field'][1]];
					// options depends & params
					$column['options']['options_depends'] = $column['options']['options_depends'] ?? [];
					$column['options']['options_params'] = $column['options']['options_params'] ?? [];
					$this->object->processParamsAndDepends($column['options']['options_depends'], $v0, $column['options'], true);
					$this->object->processParamsAndDepends($column['options']['options_params'], $v0, $column['options'], false);
					$column['options']['options_params'] = array_merge_hard($column['options']['options_params'], $column['options']['options_depends']);
					$skip_values = [];
					if (!empty($column['options']['details_key'])) {
						if (!empty($column['options']['details_parent_key'])) {
							$temp_key = $column['options']['details_parent_key'] . '::' . $column['options']['details_key'];
							if (!empty($this->object->misc_settings['details_unique_select'][$temp_key][$column['options']['details_field_name']][$options['__parent_row_number']])) {
								$skip_values = array_keys($this->object->misc_settings['details_unique_select'][$temp_key][$column['options']['details_field_name']][$options['__parent_row_number']]);
							}
						} else {
							if (!empty($this->object->misc_settings['details_unique_select'][$column['options']['details_key']][$column['options']['details_field_name']])) {
								$skip_values = array_keys($this->object->misc_settings['details_unique_select'][$column['options']['details_key']][$column['options']['details_field_name']]);
							}
						}
					}
					$options_array_processed = \Object\Data\Common::processOptions($column['options']['options_model'], $this->object, $column['options']['options_params'], null, $skip_values, $column['options']['options_options'] ?? []);
					if (empty($options_array_processed)) {
						break;
					}
				}
			}
			// we need to preset default values
			if (!empty($options['details_parent_key'])) {
				$fields = $this->object->sortFieldsForProcessing($this->object->detail_fields[$options['details_parent_key']]['subdetails'][$options['details_key']]['elements'], $this->object->detail_fields[$options['details_parent_key']]['subdetails'][$options['details_key']]['options']);
			} else {
				$fields = $this->object->sortFieldsForProcessing($this->object->detail_fields[$options['details_key']]['elements'], $this->object->detail_fields[$options['details_key']]['options']);
			}
			// handle changed field for new rows
			if (empty($v0)) {
				foreach ($fields as $k19 => $v19) {
					if (array_key_exists('default', $v19['options']) && !isset($v0[$k19])) {
						$v0[$k19] = null;
						$default = $this->object->processDefaultValue($k19, $v19['options']['default'], null, $v0, true);
						if ($this->object->canProcessDefaultValue(null, $v19)) {
							$v0[$k19] = $default;
						}
					}
				}
			}
			// looping though rows
			foreach ($rows as $k => $v) {
				// row_id
				if (empty($options['details_parent_key'])) {
					$row_id_temp = str_replace('\\', '_', $options['details_key']);
					$row_id = "form_{$this->object->form_link}_details_{$row_id_temp}_{$row_number}_row";
				} else {
					$row_details_parent_key = str_replace('\\', '_', $options['details_parent_key']);
					$row_details_key = str_replace('\\', '_', $options['details_key']);
					$row_id = "form_{$this->object->form_link}_subdetails_{$row_details_parent_key}_{$options['__parent_row_number']}_{$row_details_key}_{$row_number}_row";
				}
				// run override
				foreach ($v['elements'] as $k45 => $v45) {
					if (!empty($this->object->wrapper_methods['overrideDetailValue']['main'])) {
						$temp_value = null;
						// field name and values_key
						$v45['options']['field_name'] = $v45['options']['details_field_name'] ?? $v45['options']['name'];
						$v45['options']['data-field_values_key'] = implode('[::]', $v45['options']['field_values_key'] ?? [$v45['options']['field_name']]);
						call_user_func_array($this->object->wrapper_methods['overrideDetailValue']['main'], [& $this->object, & $v45, & $temp_value, & $v0]);
						$v['elements'][$k45] = $v45;
					}
				}
				// sort
				array_key_sort($v['elements'], ['order' => SORT_ASC]);
				// group by
				$groupped = [];
				foreach ($v['elements'] as $k2 => $v2) {
					$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				}
				foreach ($groupped as $k2 => $v2) {
					$first = current($v2);
					$first_key = key($v2);
					if ($first_key == $this->object::SEPARATOR_HORIZONTAL) {
						$data['options'][$row_number . '_' . $k][$k2][0] = [
							'value' => \HTML::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
							'separator' => true
						];
					} else {
						$first['prepend_to_field'] = ':';
						foreach ($v2 as $k3 => $v3) {
							// generate id, name and error name
							if (empty($options['details_parent_key'])) {
								// 1 to 1
								if (!empty($options['details_11'])) {
									$name = "{$options['details_key']}[{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_details_{$id01}_{$k3}";
									$error_name = "{$options['details_key']}[{$k3}]";
									$values_key = [$options['details_key'], $k3];
									$field_values_key = [$options['details_key'], $k3];
								} else { // 1 to M
									$name = "{$options['details_key']}[{$row_number}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_details_{$id01}_{$row_number}_{$k3}";
									$error_name = "{$options['details_key']}[{$k0}][{$k3}]";
									$values_key = [$options['details_key'], $k0, $k3];
									$field_values_key = [$options['details_key'], $row_number, $k3];
								}
							} else {
								// 1 to 1
								if (!empty($options['details_11'])) {
									$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_parent_key']));
									$id02 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_subdetails_{$id01}_{$options['__parent_row_number']}_{$id02}_{$row_number}_{$k3}";
									$error_name = "{$options['details_parent_key']}[{$options['__parent_key']}][{$options['details_key']}][{$k3}]";
									$values_key = [$options['details_parent_key'], $options['__parent_key'], $options['details_key'], $k3];
									$field_values_key = [$options['details_parent_key'], $options['__parent_row_number'], $options['details_key'], $k3];
								} else {
									$name = "{$options['details_parent_key']}[{$options['__parent_row_number']}][{$options['details_key']}][{$k0}][{$k3}]";
									$id01 = strtolower(str_replace('\\', '_', $options['details_parent_key']));
									$id02 = strtolower(str_replace('\\', '_', $options['details_key']));
									$id = "form_{$this->object->form_link}_subdetails_{$id01}_{$options['__parent_row_number']}_{$id02}_{$row_number}_{$k3}";
									$error_name = "{$options['details_parent_key']}[{$options['__parent_key']}][{$options['details_key']}][{$k0}][{$k3}]";
									$values_key = [$options['details_parent_key'], $options['__parent_key'], $options['details_key'], $k0, $k3];
									$field_values_key = [$options['details_parent_key'], $options['__parent_row_number'], $options['details_key'], $row_number, $k3];
								}
							}
							// error
							$error = $this->object->getFieldErrors([
								'options' => [
									'name' => $error_name,
									'values_key' => $values_key
								]
							]);
							// counter for 1 to M only
							if (!empty($error['counters'])) {
								$this->object->errorInTabs($error['counters']);
							}
							// hidden row
							$hidden = false;
							if ($k === $this->object::HIDDEN && !\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
								$hidden = true;
							}
							// generate proper element
							$value_options = $v3;
							$value_options['options']['id'] = $id;
							$value_options['options']['name'] = $name;
							$value_options['options']['error_name'] = $error_name;
							$value_options['options']['details_parent_key'] = $options['details_parent_key'] ?? null;
							$value_options['options']['__parent_row_number'] = $options['__parent_row_number'] ?? null;
							$value_options['options']['__row_number'] = $row_number;
							$value_options['options']['__new_row'] = !$processing_values;
							// need to set values_key
							$value_options['options']['values_key'] = $values_key;
							$value_options['options']['field_values_key'] = $field_values_key;
							$value_options['options']['__detail_values'] = $options['__detail_values'] ?? null;
							// tabindex but not for subdetails
							if (!$hidden && empty($options['__parent_row_number'])) {
								$value_options['options']['tabindex'] = $this->object->tabindex;
								$this->object->tabindex++;
							}
							// label
							$label = null;
							if ($options['details_rendering_type'] == 'grid_with_label') {
								$label = $this->renderElementName($first);
							}
							// we need to pass proper options
							$rendered_value = $this->renderElementValue($value_options, $v0[$k3] ?? null, $v0);
							// add element to grid
							if (($value_options['options']['percent'] ?? 0) !== -1) {
								$value_options['options']['style'] = $v3['style'] ?? null;
								$data['options'][$row_number . '_' . $k][$k2][$k3] = [
									'error' => $error,
									'label' => $label,
									'value' => $rendered_value,
									'description' => $value_options['options']['description'] ?? null,
									'options' => $value_options['options'],
									'row_class' => ($value_options['options']['row_class'] ?? '') . (!($row_number % 2) ? ' grid_row_even' : ' grid_row_odd')
								];
							}
						}
					}
				}
			}
			// increase counter
			if ($processing_values && empty($options['details_11'])) {
				$this->object->errorInTabs(['records' => 1]);
			}
			// subdetails
			if (!empty($this->object->detail_fields[$options['details_key']]['subdetails'])) {
				$temp = str_replace('\\', '_', $options['details_key']);
				$tab_id = "form_tabs_{$this->object->form_link}_subdetails_{$temp}_{$row_number}";
				$tab_header = [
					'tabs_subdetails_none' => \HTML::icon(['type' => 'fas fa-toggle-on'])
				];
				$tab_values = [
					'tabs_subdetails_none' => ''
				];
				$tab_options = [
					'tabs_subdetails_none' => []
				];
				// sort subdetail tabs
				$tab_sorted = [];
				foreach ($this->object->detail_fields[$options['details_key']]['subdetails'] as $k10 => $v10) {
					$tab_sorted[$k10] = [
						'order' => $v10['options']['order'] ?? 0
					];
				}
				array_key_sort($tab_sorted, ['order' => SORT_ASC]);
				// render tabs
				$have_tabs = false;
				foreach ($tab_sorted as $k10 => $v10) {
					$v10 = $this->object->detail_fields[$options['details_key']]['subdetails'][$k10];
					$tab_k10 = str_replace('\\', '_', $k10);
					$this->object->current_tab[] = "{$tab_id}_{$tab_k10}";
					$labels = '';
					foreach (['records', 'danger', 'warning', 'success', 'info'] as $v78) {
						$labels.= \HTML::label2(['type' => ($v78 == 'records' ? 'primary' : $v78), 'style' => 'display: none;', 'value' => 0, 'id' => implode('__', $this->object->current_tab) . '__' . $v78]);
					}
					$tab_header[$tab_k10] = i18n(null, $v10['options']['label_name']) . $labels;
					$tab_values[$tab_k10] = '';
					// handling overrideTabs method
					if (!empty($this->object->wrapper_methods['overrideTabs']['main'])) {
						$tab_options[$tab_k10] = call_user_func_array($this->object->wrapper_methods['overrideTabs']['main'], [& $this->object, & $v10, & $k10, & $v0]);
						if (empty($tab_options[$tab_k10]['hidden'])) {
							$have_tabs = true;
						}
					} else {
						$have_tabs = true;
					}
					$v10['__values'] = $v0[$v10['options']['details_key']] ?? [];
					$v10['__detail_values'] = $v0;
					$v10['__parent_row_number'] = $row_number;
					$v10['__parent_key'] = $k0;
					$temp = $this->renderContainerTypeSubdetails($v10['options']['container_link'], $v10);
					if ($temp['success']) {
						$tab_values[$tab_k10].= $temp['data']['html'];
					}
					// we must unset it
					array_pop($this->object->current_tab);
				}
				// if we do not have tabs
				if (!$have_tabs) {
					$tab_options['tabs_subdetails_none']['hidden'] = true;
				}
				$subdetails = \HTML::tabs([
					'id' => $tab_id,
					'header' => $tab_header,
					'options' => $tab_values,
					'class' => 'tabs_subdetails',
					'tab_options' => $tab_options
				]);
				// add row to the end
				$data['options'][$row_number . '_subdetails']['subdetails']['subdetails'] = [
					'error' => null,
					'label' => null,
					'value' => $subdetails,
					'description' => null,
					'options' => [
						'percent' => 100
					],
					'row_class' => !($row_number % 2) ? 'grid_row_even' : 'grid_row_odd'
				];
			}
			// delete link
			if (empty($options['details_cannot_delete']) && empty($this->object->misc_settings['global']['readonly']) && empty($this->object->misc_settings['acl_subresource_locks'][$options['container_link']]['no_delete'])) {
				$link = \HTML::a(['href' => 'javascript:void(0);', 'value' => '<i class="far fa-trash-alt"></i>', 'onclick' => "if (confirm('" . strip_tags(i18n(null, \Object\Content\Messages::CONFIRM_DELETE)) . "')) { Numbers.Form.detailsDeleteRow('form_{$this->object->form_link}_form', '{$row_id}'); } return false;"]);
			} else {
				$link = '';
				unset($table['header']['row_delete']);
			}
			// add a row to a table
			$table['options'][$row_number] = [
				'row_number' => ['value' => \Format::id($row_number) . '.', 'width' => '1%', 'row_id' => $row_id],
				'row_data' => ['value' => \HTML::grid($data), 'width' => (!empty($options['details_11']) ? '100%' : '98%')],
				'row_delete' => ['value' => $link, 'width' => '1%'],
			];
			$row_number++;
			// we need to determine if we have values
			if (next($values) === false) {
				$processing_values = false;
			}
		} while(1);
		// empty_warning_message
		if (empty($options['details_new_rows']) && empty($values) && isset($options['details_empty_warning_message'])) {
			if (empty($options['details_empty_warning_message']) || $options['details_empty_warning_message'] === true) {
				$message = \HTML::message(['type' => 'warning', 'options' => [\Object\Content\Messages::NO_ROWS_FOUND]]);
			} else {
				$message = \HTML::message(['type' => 'warning', 'options' => [$options['details_empty_warning_message']]]);
			}
			$table['options'][PHP_INT_MAX] = [
				'row_number' => ['value' => '&nbsp;', 'width' => '1%'],
				'row_data' => $message
			];
		}
render_table:
		return \HTML::table($table);
	}

	/**
	 * Render grid rows
	 *
	 * @param array $rows
	 * @param array $options
	 * @return string
	 */
	public function renderRowGrid($rows, $options = []) {
		$data = [
			'class' => $options['class'] ?? null,
			'options' => []
		];
		foreach ($rows as $k => $v) {
			$index = 0;
			array_key_sort($v['value']['elements'], ['order' => SORT_ASC]);
			// processing buttons
			if (in_array($v['key'], [$this->object::BUTTONS, $this->object::TRANSACTION_BUTTONS])) {
				$buttons = [
					'left' => [],
					'center' => [],
					'right' => []
				];
				foreach ($v['value']['elements'] as $k2 => $v2) {
					$button_group = $v2['options']['button_group'] ?? 'left';
					if (!isset($buttons[$button_group])) {
						$buttons[$button_group] = [];
					}
					$v2['options']['tabindex'] = $this->object->tabindex;
					$this->object->tabindex++;
					$buttons[$button_group][] = $this->renderElementValue($v2);
				}
				// render button groups
				foreach ($buttons as $k2 => $v2) {
					$value = implode(' ', $v2);
					$value = '<div class="grid_button_' . $k2 . '">' . $value . '</div>';
					$data['options'][$k][$v['key']][$k2] = [
						'label' => null,
						'value' => $value,
						'description' => null,
						'error' => [],
						'options' => []
					];
				}
				continue;
			}
			// group by
			$groupped = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
			}
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				$first_key = key($v2);
				if ($first_key == $this->object::SEPARATOR_HORIZONTAL) {
					$data['options'][$k][$k2][0] = [
						'value' => \HTML::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
						'separator' => true
					];
				} else {
					$first['prepend_to_field'] = ':';
					foreach ($v2 as $k3 => $v3) {
						// handling errors
						$error = $this->object->getFieldErrors($v3);
						if (!empty($error['counters'])) {
							$this->object->errorInTabs($error['counters']);
						}
						// hidden row
						$hidden = false;
						if ($v['key'] === $this->object::HIDDEN && !\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
							$hidden = true;
						} else if ($v['key'] === $this->object::HIDDEN) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden_testing';
						}
						// we do not show hidden fields
						if (($v3['options']['method'] ?? '') == 'hidden') {
							if (\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['method'] = 'input';
							} else {
								$v3['options']['style'] = ($v3['options']['style'] ?? '') . 'display: none;';
								$hidden = true;
							}
						}
						if (!$hidden) {
							$v3['options']['tabindex'] = $this->object->tabindex;
							$this->object->tabindex++;
						} else if ($hidden && \HTML::getMode()) { // we do not render hidden fields in email
							continue;
						}
						// processing value and neighbouring_values
						if (!empty($v3['options']['detail_11'])) {
							$neighbouring_values = & $this->object->values[$v3['options']['detail_11']];
						} else {
							$neighbouring_values = & $this->object->values;
						}
						$value = array_key_get($this->object->values, $v3['options']['values_key']);
						$data['options'][$k][$k2][$k3] = [
							'error' => $error,
							'label' => $this->renderElementName($first),
							'value' => $this->renderElementValue($v3, $value, $neighbouring_values),
							'description' => $v3['options']['description'] ?? null,
							'options' => $v3['options'],
							'row_class' => $v3['options']['row_class'] ?? null
						];
						// list and report should have filter tab counters
						if (in_array($this->object->initiator_class, ['list', 'report'])) {
							if (!empty($value)) {
								$this->object->errorInTabs(['records' => 1]);
							}
						}
					}
				}
			}
		}
		return \HTML::grid($data);
	}

	/**
	 * Render table rows
	 *
	 * @param array $rows
	 * @param array $options
	 * @return string
	 */
	public function renderRowTable($rows, $options = []) {
		$data = [
			'class' => $options['class'] ?? null,
			'header' => [
				'column' => 'Column'
			],
			'options' => [],
			'skip_header' => true
		];
		$column_name_width_percent = $options['container_options']['column_name_width_percent'] ?? 50;
		foreach ($rows as $k => $v) {
			$index = 0;
			array_key_sort($v['value']['elements'], ['order' => SORT_ASC]);
			// processing buttons
			/*
			if (in_array($v['key'], [$this->object::BUTTONS, $this->object::TRANSACTION_BUTTONS])) {
				$buttons = [
					'left' => [],
					'center' => [],
					'right' => []
				];
				foreach ($v['value']['elements'] as $k2 => $v2) {
					$button_group = $v2['options']['button_group'] ?? 'left';
					if (!isset($buttons[$button_group])) {
						$buttons[$button_group] = [];
					}
					$v2['options']['tabindex'] = $this->object->tabindex;
					$this->object->tabindex++;
					$buttons[$button_group][] = $this->renderElementValue($v2);
				}
				// render button groups
				foreach ($buttons as $k2 => $v2) {
					$value = implode(' ', $v2);
					$value = '<div class="grid_button_' . $k2 . '">' . $value . '</div>';
					$data['options'][$k][$v['key']][$k2] = [
						'label' => null,
						'value' => $value,
						'description' => null,
						'error' => [],
						'options' => []
					];
				}
				continue;
			}
			*/
			// group by
			$groupped = [];
			$percentages = [];
			foreach ($v['value']['elements'] as $k2 => $v2) {
				$groupped[$v2['options']['label_name'] ?? ''][$k2] = $v2;
				$percentages[$k2] = $v2['options']['percent'] ?? null;
			}
			$percentages = \HTML::percentageToGridColumns($percentages);
			foreach ($groupped as $k2 => $v2) {
				$first = current($v2);
				$first_key = key($v2);
				$inner_data = [
					'class' => 'numbers_frontend_form_table_renderer_field_table',
					'header' => [],
					'options' => [],
					'skip_header' => true,
					'width' => '100%'
				];
				if ($first_key == $this->object::SEPARATOR_HORIZONTAL) {
					$data['options'][$k]['column'] = [
						'value' => \HTML::separator(['value' => $first['options']['label_name'], 'icon' => $first['options']['icon'] ?? null]),
						'separator' => true
					];
					continue;
				} else {
					$first['prepend_to_field'] = ':';
					// column name
					$column_counter = 1;
					$column_percentages = 0;
					foreach ($v2 as $k3 => $v3) {
						$column_percentages+= $percentages['percent'][$k3];
					}
					$inner_data['header'][$k2] = $k2;
					if (!empty($first['options']['label_name'])) {
						$column_name_width_percent_oposite = 100 - $column_name_width_percent;
						$inner_data['options'][$k][$k2] = [
							'value' => $this->renderElementName($first),
							'nowrap' => true,
							'width' => \HTML::number(round($column_percentages * ($column_name_width_percent / 100), 2)) . '%'
						];
					} else {
						$column_name_width_percent_oposite = 100;
						$inner_data['options'][$k][$k2] = [
							'value' => '',
							'nowrap' => true,
							'width' => '0%'
						];
					}
					// loop through fields
					foreach ($v2 as $k3 => $v3) {
						// handling errors
						$error = $this->object->getFieldErrors($v3);
						if (!empty($error['counters'])) {
							$this->object->errorInTabs($error['counters']);
						}
						// hidden row
						$hidden = false;
						if ($v['key'] === $this->object::HIDDEN && !\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden';
							$hidden = true;
						} else if ($v['key'] === $this->object::HIDDEN) {
							$v3['options']['row_class'] = ($v3['options']['row_class'] ?? '') . ' grid_row_hidden_testing';
						}
						// we do not show hidden fields
						if (($v3['options']['method'] ?? '') == 'hidden') {
							if (\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
								$v3['options']['method'] = 'input';
							} else {
								$v3['options']['style'] = ($v3['options']['style'] ?? '') . 'display: none;';
								$hidden = true;
							}
						}
						if (!$hidden) {
							$v3['options']['tabindex'] = $this->object->tabindex;
							$this->object->tabindex++;
						}
						// processing value and neighbouring_values
						if (!empty($v3['options']['detail_11'])) {
							$neighbouring_values = & $this->object->values[$v3['options']['detail_11']];
						} else {
							$neighbouring_values = & $this->object->values;
						}
						$value = array_key_get($this->object->values, $v3['options']['values_key']);
						// generate column
						$new_column_width = \HTML::number(round($percentages['percent'][$k3] * ($column_name_width_percent_oposite / 100), 2)) . '%';
						$inner_data['header'][$k3] = $k3;
						$inner_data['options'][$k][$k3] = [
							'value' => $this->renderElementValue($v3, $value, $neighbouring_values),
							'nowrap' => true,
							'width' => $new_column_width
						];
						if (!empty($error)) {
							$inner_data['options'][$k . '_error'][$k3] = [
								'value' => $error['message'],
								'nowrap' => true,
								'width' => $new_column_width
							];
						}
						if (!empty($v3['options']['description'])) {
							$inner_data['options'][$k . '_description'][$k2] = [
								'value' => $v3['options']['description'],
								'nowrap' => true,
								'width' => $new_column_width
							];
						}
					}
				}
				$data['options'][$k]['column'] = [
					'value' => \HTML::table($inner_data),
					'nowrap' => true
				];
			}
		}
		return \HTML::table($data);
	}

	/**
	 * Render elements name
	 *
	 * @param array $options
	 * @return string
	 */
	public function renderElementName($options) {
		if ((isset($options['options']['label_name']) && ($options['options']['label_name'] . '') != '') || isset($options['options']['label_i18n'])) {
			$value = i18n($options['options']['label_i18n'] ?? null, $options['options']['label_name']);
			if ($options['options']['label_name'] == ' ') {
				$prepend = '';
				$value = '<br/>';
			} else {
				$prepend = isset($options['prepend_to_field']) ? $options['prepend_to_field'] : null;
			}
			// todo: preset for attribute label_for = id
			$label_options = array_key_extract_by_prefix($options['options'], 'label_');
			// prepending mandatory string
			if (!empty($options['options']['required'])) {
				if ($options['options']['required'] === true || $options['options']['required'] === '1' || $options['options']['required'] === 1) {
					$options['options']['required'] = 'mandatory';
				} else if ($options['options']['required'] == 'c') {
					$options['options']['required'] = 'conditional';
				}
				$value = \HTML::mandatory([
					'type' => $options['options']['required'],
					'value' => $value,
					'prepend' => $prepend
				]);
			} else {
				$value.= $prepend;
			}
			$label_options['value'] = $value;
			$label_options['class'] = 'control-label';
			return \HTML::label($label_options);
		}
	}

	/**
	 * Render elements value
	 *
	 * @param array $options
	 * @param mixed $value
	 * @param array $neighbouring_values
	 * @return string
	 * @throws Exception
	 */
	public function renderElementValue(& $options, $value = null, & $neighbouring_values = []) {
		// field name and values_key
		$options['options']['field_name'] = $options['options']['details_field_name'] ?? $options['options']['name'];
		$options['options']['data-field_values_key'] = implode('[::]', $options['options']['field_values_key'] ?? [$options['options']['field_name']]);
		// custom renderer
		if (!empty($options['options']['custom_renderer'])) {
			$method = \Factory::method($options['options']['custom_renderer'], $this->object->form_parent, true, [['skip_processing' => true]]);
			$options_custom_renderer = $options;
			$temp = call_user_func_array($method, [& $this->object, & $options, & $value, & $neighbouring_values]);
			if (!is_null($temp)) {
				return $temp;
			}
		}
		// handling override_field_value method
		if (!empty($this->object->wrapper_methods['overrideFieldValue']['main'])) {
			call_user_func_array($this->object->wrapper_methods['overrideFieldValue']['main'], [& $this->object, & $options, & $value, & $neighbouring_values]);
		}
		$result_options = $options['options'];
		// process json_contains
		if (!empty($result_options['json_contains'])) {
			$temp = [];
			foreach ($result_options['json_contains'] as $k => $v) {
				$temp[$k] = array_key_get($neighbouring_values, $v);
			}
			$value = \Object\Table\Options::optionJsonFormatKey($temp);
		}
		$options['options']['value'] = $value;
		array_key_extract_by_prefix($result_options, 'label_');
		$element_expand = !empty($result_options['expand']);
		$html_suffix = $result_options['html_suffix'] ?? '';
		$html_table_description = $result_options['html_table_description'] ?? '';
		// unset certain keys
		unset($result_options['order'], $result_options['required'], $result_options['html_suffix']);
		// processing options
		$flag_select_or_autocomplete = !empty($result_options['options_model']) || !empty($result_options['options']);
		if (!empty($result_options['options_model'])) {
			if (empty($result_options['options_params'])) {
				$result_options['options_params'] = [];
			}
			if (empty($result_options['options_options'])) {
				$result_options['options_options'] = [];
			}
			$result_options['options_options']['i18n'] = $result_options['options_options']['i18n'] ?? true;
			if (empty($result_options['options_depends'])) {
				$result_options['options_depends'] = [];
			}
			// options depends & params
			$this->object->processParamsAndDepends($result_options['options_depends'], $neighbouring_values, $options, true);
			$this->object->processParamsAndDepends($result_options['options_params'], $neighbouring_values, $options, false);
			$result_options['options_params'] = array_merge_hard($result_options['options_params'], $result_options['options_depends']);
			// we do not need options for autocomplete
			if (strpos($result_options['method'] ?? '', 'autocomplete') === false) {
				$skip_values = [];
				if (!empty($options['options']['details_key'])) {
					if (!empty($options['options']['details_parent_key'])) {
						$temp_key = $options['options']['details_parent_key'] . '::' . $options['options']['details_key'];
						if (!empty($this->object->misc_settings['details_unique_select'][$temp_key][$options['options']['details_field_name']][$options['options']['__parent_row_number']])) {
							$skip_values = array_keys($this->object->misc_settings['details_unique_select'][$temp_key][$options['options']['details_field_name']][$options['options']['__parent_row_number']]);
						}
					} else {
						if (!empty($this->object->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']])) {
							$skip_values = array_keys($this->object->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']]);
						}
					}
				}
				// call override method
				if (!empty($this->object->wrapper_methods['processOptionsModels']['main'])) {
					$model = $this->object->wrapper_methods['processOptionsModels']['main'][0];
					$model->{$this->object->wrapper_methods['processOptionsModels']['main'][1]}($this->object, $options['options']['field_name'], $options['options']['details_key'] ?? null, $options['options']['details_parent_key'] ?? null, $result_options['options_params'], $neighbouring_values, $options['options']['__detail_values'] ?? []);
				}
				// multiple column
				if (!empty($options['options']['multiple_column']) && is_array($value)) {
					$current = current($value);
					if (is_array($current) && key($current) == $options['options']['multiple_column']) {
						$value = array_extract_values_by_key($value ?? [], $options['options']['multiple_column']);
					}
				}
				$result_options['options_options']['include_null_filter'] = $options['options']['include_null_filter'] ?? null;
				$options_array_processed = \Object\Data\Common::processOptions($result_options['options_model'], $this->object, $result_options['options_params'], $value, $skip_values, $result_options['options_options']);
				if (strpos($result_options['options_model'], 'optgroups') === false) {
					$result_options['options'] = $options_array_processed;
				} else {
					$result_options['optgroups'] = $options_array_processed;
				}
			} else {
				// we need to inject form id into autocomplete
				$result_options['form_id'] = "form_{$this->object->form_link}_form";
			}
		} else if (!empty($result_options['options'])) {
			// we need to uset unique options
			if (!empty($options['options']['details_key']) &&!empty($this->object->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']])) {
				$skip_values = array_keys($this->object->misc_settings['details_unique_select'][$options['options']['details_key']][$options['options']['details_field_name']]);
				if (!empty($skip_values)) {
					$keep_values = is_array($value) ? $value : [$value];
					foreach ($result_options['options'] as $k => $v) {
						if (in_array($k, $skip_values) && !in_array($k, $keep_values)) {
							unset($result_options['options'][$k]);
						}
					}
				}
			}
		}
		// by default all selects are searchable if not specified otherwise
		if ($flag_select_or_autocomplete) {
			$result_options['searchable'] = $result_options['searchable'] ?? false;
			$result_options['filter_only_selected_options_if_readonly'] = true;
		}
		// different handling for different type
		switch ($options['type']) {
			case 'container';
				$options_container = $options;
				//$options_container['previous_data'] = $v;
				// todo: pass $form_data_key from parent
				$options_container['previous_key'] = $options['previous_key'];
				// render container
				$temp_container_value = $this->renderContainer($data['fm_part_child_container_name'], $parents, $options_container);
				if (!empty($html_expand)) {
					// get part id
					$temp_id = $this->object->id('part_details', [
						'part_name' => $data['fm_part_name'],
						// todo pass $k2 from parent
						'part_id' => $options_container['previous_id']
					]);
					$temp_id_div_inner = $temp_id . '_html_expand_div_inner';
					$temp_expand_div_inner = [
						'id' => $temp_id_div_inner,
						'style' => 'display: none;',
						'value' => $temp_container_value
					];
					$temp_expand_div_a = [
						'href' => 'javascript:void(0);',
						'onclick' => "Numbers.Element.toggle('{$temp_id_div_inner}');",
						'value' => '+ / -'
					];
					$temp_expand_div_outer = [
						'align' => 'left',
						'value' => \HTML::a($temp_expand_div_a) . '<br />' . \HTML::div($temp_expand_div_inner)
					];
					$value = \HTML::div($temp_expand_div_outer);
				} else {
					$value = $temp_container_value;
				}
				$result_options['value'] = $value;
				break;
			case 'field':
				// fix id
				if ($result_options['name'][0] == '\\') {
					$result_options['id'] = str_replace('\\', '_', $result_options['id']);
				}
				$element_method = $result_options['method'] ?? '\HTML::input';
				if (strpos($element_method, '::') === false) {
					$element_method = '\HTML::' . $element_method;
				}
				// value in special order
				$flag_translated = false;
				if (in_array($element_method, ['\HTML::a', '\HTML::submit', '\HTML::button', '\HTML::button2', '\HTML::separator'])) {
					// translate value
					if (empty($result_options['skip_i18n'])) {
						$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value'] ?? null, ['skip_i_symbol' => true]);
						$flag_translated = true;
					}
					// process confirm_message
					$result_options['onclick'] = $result_options['onclick'] ?? '';
					if (!empty($result_options['confirm_message'])) {
						$result_options['onclick'].= 'return confirm(\'' . strip_tags(i18n(null, $result_options['confirm_message'])) . '\');';
					}
					// processing onclick for buttons
					if (in_array($element_method, ['\HTML::submit', '\HTML::button', '\HTML::button2'])) {
						if (!empty($result_options['onclick']) && strpos($result_options['onclick'], 'this.form.submit();') !== false) {
							$result_options['onclick'] = str_replace('this.form.submit();', "Numbers.Form.triggerSubmit(this.form);", $result_options['onclick']) . ' return true;';
						} else if (empty($result_options['onclick'])) {
							$result_options['onclick'].= 'Numbers.Form.triggerSubmitOnButton(this); return true;';
						} else {
							$result_options['onclick'] = 'Numbers.Form.triggerSubmitOnButton(this); ' . $result_options['onclick'];
						}
					}
					// icon
					if (!empty($result_options['icon']) && $element_method != '\HTML::separator') {
						$result_options['value'] = \HTML::icon(['type' => $result_options['icon']]) . ' ' . $result_options['value'];
					}
					// accesskey
					if (isset($result_options['accesskey'])) {
						$accesskey = explode('::', i18n(null, 'accesskey::' . $result_options['name'] . '::' . $result_options['accesskey'], ['skip_translation_symbol' => true]));
						$result_options['accesskey'] = $accesskey[2];
						$result_options['title'] = ($result_options['title'] ?? '') . ' ' . i18n(null, 'Shortcut Key: ') . $accesskey[2];
					}
				} else if (in_array($element_method, ['\HTML::div', '\HTML::span', '\HTML::b'])) {
					if (!empty($value)) {
						$result_options['value'] = $value;
					}
					// format
					if (!empty($result_options['format'])) {
						$result_options['format_options'] = $result_options['format_options'] ?? [];
						if (!empty($result_options['format_depends'])) {
							$this->object->processParamsAndDepends($result_options['format_depends'], $neighbouring_values, $options, true);
							$result_options['format_options'] = array_merge_hard($result_options['format_options'], $result_options['format_depends']);
						}
						$method = \Factory::method($result_options['format'], 'Format');
						$result_options['value'] = call_user_func_array([$method[0], $method[1]], [$result_options['value'], $result_options['format_options']]);
					}
					if (empty($result_options['skip_i18n'])) {
						$result_options['value'] = i18n($result_options['i18n'] ?? null, $result_options['value'] ?? null);
						$flag_translated = true;
					}
					if (!empty($result_options['nl2br']) && !is_html($result_options['value'])) {
						$result_options['value'] = nl2br($result_options['value'] . '', true);
					}
				} else { // editable fields
					// inputs should not be date type, use input_type to override
					if ($element_method == '\HTML::input' && empty($options['options']['static'])) {
						$result_options['type'] = 'text';
					}
					// special handling for
					if ($element_method == '\HTML::radio') {
						$result_options['value'] = '<table width="100%">';
						$radio_counter = 1;
						foreach ($result_options['options'] as $k => $v) {
							$result_options['value'].= '<tr>';
							$result_options['value'].= '<td width="1%">' . \HTML::radio([
								'id' => $result_options['id'] . '_' . $radio_counter,
								'name' => $result_options['name'],
								'value' => $k,
								'checked' => ($k == $value),
								'style' => 'vertical-align: middle;'
							]) . '</td>';
							$result_options['value'].= '</td><td width="99%"><span class="numbers_frontend_form_html_table_description">' . i18n(null, $v['name']) . '</span></td>';
							$result_options['value'].= '</tr>';
							$radio_counter++;
						}
						$result_options['value'].= '</table>';
						$element_method = '\HTML::div';
						goto render_element;
					}
					$result_options['value'] = $value;
					// if we need to empty value, mostly for password fields
					if (!empty($result_options['empty_value'])) {
						$result_options['value'] = '';
					}
					// we need to empty zero integers and sequences, before format
					if (($result_options['php_type'] ?? '') == 'integer' && !empty($result_options['null']) && ($result_options['type'] ?? '') != 'boolean' && ($result_options['domain'] ?? '') != 'counter' && ($result_options['domain'] ?? '') != 'bigcounter' && empty($result_options['value']) && ($result_options['method'] ?? null) != 'hidden') {
						$result_options['value'] = '';
					}
					// format, not for selects/autocompletes/presets
					if (!$flag_select_or_autocomplete) {
						if (!empty($result_options['format'])) {
							if (!empty($this->object->errors['fields'][$result_options['error_name']]) && empty($this->object->errors['formats'][$result_options['error_name']])) {
								// nothing
							} else {
								$result_options['format_options'] = $result_options['format_options'] ?? [];
								if (!empty($result_options['format_depends'])) {
									$this->object->processParamsAndDepends($result_options['format_depends'], $neighbouring_values, $options, true);
									$result_options['format_options'] = array_merge_hard($result_options['format_options'], $result_options['format_depends']);
								}
								$method = \Factory::method($result_options['format'], 'Format');
								$result_options['value'] = call_user_func_array([$method[0], $method[1]], [$result_options['value'], $result_options['format_options']]);
							}
						}
					}
					// align
					if (!empty($result_options['align'])) {
						$result_options['style'] = ($result_options['style'] ?? '') . 'text-align:' . \HTML::align($result_options['align']) . ';';
					}
					// processing persistent
					if (!empty($result_options['persistent']) && $this->object->values_loaded) {
						if ($result_options['persistent'] === 'if_set') {
							$original_value = $detail = array_key_get($this->object->original_values, $result_options['values_key']);
							if (!empty($original_value)) {
								$result_options['readonly'] = true;
							}
						} else if (count($result_options['values_key']) == 1) { // parent record
							$result_options['readonly'] = true;
						} else if (empty($result_options['__new_row'])) { // details
							$temp = $result_options['values_key'];
							array_pop($temp);
							$detail = array_key_get($this->object->original_values, $temp);
							if (!empty($detail)) {
								$result_options['readonly'] = true;
							}
						}
					}
					// maxlength
					if (in_array($result_options['type'] ?? '', ['char', 'varchar']) && !empty($result_options['length'])) {
						$result_options['maxlength'] = $result_options['length'];
					}
					// global readonly
					if (!empty($this->object->misc_settings['global']['readonly']) && empty($result_options['navigation']) && $result_options['name'] != '__module_id') {
						$result_options['readonly'] = true;
					}
					// title
					if (isset($options['options']['label_name'])) {
						$result_options['title'] = ($result_options['title'] ?? '') . ' ' . strip_tags(i18n(null, $options['options']['label_name']));
					}
				}
				// translate place holder
				if (array_key_exists('placeholder', $result_options)) {
					if ($result_options['field_name'] == 'full_text_search' && $result_options['placeholder'] === true) {
						$result_options['placeholder'] = null;
						$temp_placeholder = [];
						foreach ($result_options['full_text_search_columns'] as $v8) {
							if (strpos($v8, '.') !== false) {
								$v8 = explode('.', $v8);
								$v8 = $v8[1];
							}
							if (!empty($this->object->fields[$v8]['options']['label_name'])) {
								$temp_placeholder[] = i18n(null, $this->object->fields[$v8]['options']['label_name']);
							}
						}
						if (!empty($temp_placeholder)) {
							$result_options['placeholder'] = i18n(null, 'Search in [columns]', ['replace' => ['[columns]' => implode(', ', $temp_placeholder)]]);
						}
					} else if (!empty($result_options['placeholder'])) {
						// skip timestamp
						if ($result_options['placeholder'] == 'Format::getDatePlaceholder' && strpos($result_options['method'] ?? '', 'calendar') === false) {
							$result_options['placeholder'] = \Format::getDatePlaceholder(\Format::getDateFormat($result_options['type']));
						} else {
							$result_options['placeholder'] = strip_tags(i18n(null, $result_options['placeholder']));
						}
					}
				} else if (!empty($result_options['validator_method']) && empty($result_options['value']) && empty($result_options['multiple_column'])) {
					$temp = \Object\Validator\Base::method($result_options['validator_method'], $result_options['value'], $result_options['validator_params'] ?? [], $options['options'], $neighbouring_values);
					if ($flag_select_or_autocomplete) {
						$placeholder = $temp['placeholder_select'] ?? null;
					} else {
						$placeholder = $temp['placeholder'] ?? null;
					}
					if (!empty($placeholder)) {
						$result_options['placeholder'] = strip_tags(i18n(null, $placeholder));
					}
				}
				// auto placeholder for selects
				if (in_array($element_method, ['\HTML::select', '\HTML::multiselect']) && !array_key_exists('placeholder', $result_options)) {
					$result_options['placeholder'] = strip_tags(i18n(null, \Object\Content\Messages::PLEASE_CHOOSE));
				}
				// events
				foreach (\Numbers\Frontend\HTML\Renderers\Common\HTML5::$events as $e) {
					if (!empty($result_options['readonly'])) { // important - readonly emenets cannot have events
						unset($result_options[$e]);
					} else if (!empty($result_options[$e])) {
						$result_options[$e] = str_replace('this.form.submit();', 'Numbers.Form.triggerSubmit(this);', $result_options[$e]);
						$result_options[$e] = str_replace('this.form.extended.', $this->object->misc_settings['extended_js_class'] . '.', $result_options[$e]);
					}
				}
				// static
				if (!empty($options['options']['static'])) {
					$element_method = $result_options['method'] = '\HTML::div';
					$hidden_fields = [];
					if (isset($options_array_processed)) {
						if (is_array($result_options['value'])) {
							$temp = [];
							foreach ($result_options['value'] as $k => $v) {
								$temp[] = $options_array_processed[$v]['name'];
								$hidden_fields[] = \HTML::hidden(['name' => $result_options['name'] . '[]', 'value' => $v]);
							}
							$result_options['value'] = implode(\Format::$symbol_semicolon, $temp);
						} else {
							$hidden_fields[] = \HTML::hidden(['name' => $result_options['name'], 'value' => $result_options['value']]);
							if (($result_options['value'] ?? '') != '') {
								$result_options['value'] = $options_array_processed[$result_options['value']]['name'];
							}
						}
					} else {
						if (!empty($result_options['nl2br'])) {
							$hidden_fields[] = \HTML::hidden(['name' => $result_options['name'], 'value' => br2nl($result_options['value'])]);
						} else {
							$hidden_fields[] = \HTML::hidden(['name' => $result_options['name'], 'value' => $result_options['value']]);
						}
					}
					$result_options['value'].= implode('', $hidden_fields);
					$result_options['static'] = true;
				}
				break;
			case 'html':
				$element_method = null;
				break;
			default:
				Throw new Exception('Render detail type: ' . $data['fm_part_type']);
		}
render_element:
		// handling html_method
		if (isset($element_method)) {
			$method = \Factory::method($element_method, 'HTML');
			$field_method_object = \Factory::model($method[0], true);
			// todo: unset non html attributes
			$value = $field_method_object->{$method[1]}($result_options);
			// building navigation
			if (!empty($result_options['navigation'])) {
				$name = 'navigation[' . $result_options['name'] . ']';
				$refresh_id = 'navigation_' . $result_options['id'] . '_refresh';
				$temp = '<table width="100%" dir="ltr">'; // always left to right
					$temp.= '<tr>';
						$temp.= '<td width="1%">' . \HTML::button2(['name' => $name . '[first]', 'value' => \HTML::icon(['type' => 'fas fa-step-backward']), 'onclick' => '$(this.form).attr(\'no_ajax\', 1); Numbers.Form.triggerSubmitOnButton(this);', 'title' => i18n(null, 'First')]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . \HTML::button2(['name' => $name . '[previous]', 'value' => \HTML::icon(['type' => 'fas fa-caret-left']), 'onclick' => '$(this.form).attr(\'no_ajax\', 1); Numbers.Form.triggerSubmitOnButton(this);', 'title' => i18n(null, 'Previous')]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="90%">' . $value . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . \HTML::button2(['name' => $name . '[refresh]', 'id' => $refresh_id, 'value' => \HTML::icon(['type' => 'fas fa-sync']), 'onclick' => '$(this.form).attr(\'no_ajax\', 1); Numbers.Form.triggerSubmitOnButton(this);', 'title' => i18n(null, 'Refresh')]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . \HTML::button2(['name' => $name . '[next]', 'value' => \HTML::icon(['type' => 'fas fa-caret-right']), 'onclick' => '$(this.form).attr(\'no_ajax\', 1); Numbers.Form.triggerSubmitOnButton(this);', 'title' => i18n(null, 'Next')]) . '</td>';
						$temp.= '<td width="1%">&nbsp;</td>';
						$temp.= '<td width="1%">' . \HTML::button2(['name' => $name . '[last]', 'value' => \HTML::icon(['type' => 'fas fa-step-forward']), 'onclick' => '$(this.form).attr(\'no_ajax\', 1); Numbers.Form.triggerSubmitOnButton(this);', 'title' => i18n(null, 'Last')]) . '</td>';
					$temp.= '</tr>';
				$temp.= '</table>';
				$value = $temp;
				\Layout::onLoad(<<<TTT
					$("#{$result_options['id']}").keypress(function(e) {
						if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
							$("#{$refresh_id}").click();
							return false;
						} else {
							return true;
						}
					});
TTT
);
			}
			// if we need to load values on change
			if (!empty($options['options']['refetch_values_on_change'])) {
				$options['options']['track_previous_values'] = true;
			}
			// track previous values
			if (!empty($options['options']['track_previous_values'])) {
				if (is_array($result_options['value'])) {
					foreach ($result_options['value'] as $v) {
						$value.= \HTML::hidden(['name' => '__track_previous_values[' . $result_options['name'] . '][]', 'value' => $v]);
					}
				} else {
					$value.= \HTML::hidden(['name' => '__track_previous_values[' . $result_options['name'] . ']', 'value' => $result_options['value']]);
				}
			}
		}
		// html suffix and prefix
		if (!empty($html_suffix)) {
			$value.= $html_suffix;
		}
		if (!empty($html_table_description)) {
			$value = '<table width="100%"><tr><td width="99%">' . $value . '</td><td width="1%"><span class="numbers_frontend_form_html_table_description">' . $html_table_description . '</span></td></tr></table>';
		}
		// if we need to display settings
		if (\Application::get('flag.numbers.frontend.html.form.show_field_settings')) {
			$id_original = $result_options['id'] . '__settings_original';
			$id_modified = $result_options['id'] . '__settings_modified';
			$value.= \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "$('#{$id_original}').toggle();", 'value' => \HTML::label2(['type' => 'primary', 'value' => count($options['options'])])]);
			$value.= \HTML::a(['href' => 'javascript:void(0);', 'onclick' => "$('#{$id_modified}').toggle();", 'value' => \HTML::label2(['type' => 'warning', 'value' => count($result_options)])]);
			$value.= '<div id="' . $id_original . '" style="display:none; position: absolute; text-align: left; width: 500px; z-index: 32000;">' . print_r2($options['options'], '', true) . '</div>';
			$value.= '<div id="' . $id_modified . '" style="display:none; position: absolute; text-align: left; width: 500px; z-index: 32000;">' . print_r2($result_options, '', true) . '</div>';
		}
		// we need to put original options back
		if (!empty($options['options']['custom_renderer'])) {
			$options = $options_custom_renderer;
		}
		return $value;
	}
}
