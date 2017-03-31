<?php

class numbers_frontend_html_widgets_comments_base {

	/**
	 * Widget link
	 *
	 * @var string
	 */
	public $widget_link;

	/**
	 * Constructor
	 *
	 * @param string $widget_link
	 * @param array $options
	 */
	public function __construct($widget_link, $options = []) {
		$this->widget_link = $widget_link . '';
		$this->options = $options;
	}

	/**
	 * Render widget
	 *
	 * @return mixed
	 */
	public function render() {
		$result = '';
		// action bar
		$result.= '<div style="text-align: right;">';
			$result.= \HTML::a(array('value' => \HTML::icon(['type' => 'comment']) . ' ' . i18n(null, 'New'), 'href' => 'javascript:void(0);', 'onclick' => "numbers.frontend_form.trigger_submit('#form_numbers_frontend_html_widgets_comments_model_form_comment_form', false, true); numbers.modal.show('widgets_comments_{$this->widget_link}_comment');"));
		$result.= '</div>';
		$result.= '<hr class="simple" />';
		// form
		$pk = http_build_query2($this->options['pk']);
		$js = <<<TTT
			var mask_id = 'widgets_comments_{$this->widget_link}_mask';
			$.ajax({
				url: numbers.controller_full,
				method: 'post',
				data: '__ajax=1&__ajax_form_id=widgets_comments_{$this->widget_link}_list&{$pk}',
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						$('#widgets_comments_{$this->widget_link}_wrapper').html(data.html);
						eval(data.js);
						// remove mask after 100 miliseconds to let js to take affect
						setTimeout(function() {
							$('#' + mask_id).unmask();
							// we need to trigger resize to redraw a screen
							$(window).trigger('resize');
						}, 100);
					}
				}
			});
TTT;
		$form = new numbers_frontend_html_widgets_comments_model_form_comment([
			'input' => $this->options['input'],
			'no_actions' => true,
			'bypass_hidden_values' => $this->options['pk'],
			'other' => [
				'model' => $this->options['model'],
				'pk' => $this->options['pk'],
				'map' => $this->options['map']
			],
			'on_success_js' => "numbers.modal.hide('widgets_comments_{$this->widget_link}_comment');" . $js
		]);
		$body = $form->render();
		$footer = \HTML::button2([
			'name' => 'submit_comment',
			'value' => i18n(null, 'Submit'),
			'type' => 'primary',
			'onclick' => "numbers.frontend_form.trigger_submit('#form_numbers_frontend_html_widgets_comments_model_form_comment_form', true); return false;"
		]);
		$result.= \HTML::modal(['id' => "widgets_comments_{$this->widget_link}_comment", 'class' => 'large', 'title' => i18n(null, 'Add Comment'), 'body' => $body, 'footer' => $footer]);
		// list of comments in descending order
		$where = [];
		foreach ($this->options['map'] as $k => $v) {
			$where[$v] = $this->options['pk'][$k];
		}
		$datasource = new numbers_frontend_html_widgets_comments_model_datasource_comments();
		$data = $datasource->get([
			'model' => $this->options['model'],
			'where' => $where
		]);
		if (!empty($data)) {
			$table = [
				'header' => [
					'id' => ['value' => '#', 'width' => '1%'],
					'inserted' => ['value' => i18n(null, 'Date & Time'), 'width' => '1%', 'nowrap' => true],
					'important' => ['value' => i18n(null, 'Important'), 'width' => '1%'],
					'em_entity_name' => ['value' => i18n(null, 'Entity'), 'width' => '10%'],
					'comment_value' => i18n(null, 'Comment')
				],
				'options' => []
			];
			$row_number = 1;
			foreach ($data as $k => $v) {
				// we need to hide old comments
				$row_style = '';
				if ($row_number > 10) {
					$row_style = 'display: none;';
				}
				$table['options'][$v['id']] = [
					'id' => ['value' => $row_number . '.', 'row_style' => $row_style, 'row_class' => "widgets_comments_{$this->widget_link}_list_hiden " . ($v['important'] ? 'success' : null)],
					'inserted' => Format::datetime($v['inserted']),
					'important' => ['value' => $v['important'] ? i18n(null, 'Yes') : ''],
					'em_entity_name' => ['value' => $v['em_entity_name'], 'width' => '10%', 'nowrap' => true],
					'comment_value' => nl2br($v['comment_value'])
				];
				$row_number++;
			}
			$result_list = \HTML::table($table);
			// link to show all rows
			$total_comments = count($data);
			if ($total_comments > 10) {
				$result_list.= '<div style="text-align: right;">' . \HTML::a(['href' => 'javascript:void(0);', 'value' => i18n(null, '[count] comment(s) are hidden. Show all comments.', ['replace' => ['[count]' => ($total_comments - 10)]]), 'onclick' => "$('.widgets_comments_{$this->widget_link}_list_hiden').show(); $(this).hide();"]) . '</div>';
			}
		} else {
			$result_list = \HTML::message(['type' => 'warning', 'options' => [i18n(null, \Object\Content\Messages::no_rows_found)]]);
		}
		// if we are making an ajax call
		if (!empty($this->options['input']['__ajax']) && ($this->options['input']['__ajax_form_id'] ?? '') == "widgets_comments_{$this->widget_link}_list") {
			Layout::render_as([
				'success' => true,
				'error' => [],
				'html' => $result_list,
				'js' => ''
			], 'application/json');
		}
		// load mask
		\Numbers\Frontend\Media\Libraries\LoadMask\Base::add();
		// put list into result
		$result.= "<div id=\"widgets_comments_{$this->widget_link}_mask\"><div id=\"widgets_comments_{$this->widget_link}_wrapper\">" . $result_list . '</div></div>';
		// wrap everything into segment
		if (isset($this->options['segment'])) {
			$temp = is_array($this->options['segment']) ? $this->options['segment'] : [];
			$temp['value'] = $result;
			$result = \HTML::segment($temp);
		}
		// anchor
		$result = \HTML::a(['name' => "widgets_comments_{$this->widget_link}_anchor"]) . $result;
		return $result;
	}
}