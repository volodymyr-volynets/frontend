<?php

class numbers_frontend_html_form_wrapper_report extends numbers_frontend_html_form_wrapper_base {

	/**
	 * Constructor
	 */
	public function __construct($options = []) {
		// add standard report segment
		if (!array_key_exists('segment', $this->options)) {
			$this->options['segment'] = [
				'type' => 'info',
				'header' => [
					'icon' => ['type' => 'bar-chart'],
					'title' => 'Report:'
				]
			];
		}
		$options['initiator_class'] = 'numbers_frontend_html_form_wrapper_report';
		$options['no_actions'] = $options['no_actions'] ?? true;
		parent::__construct($options);
	}
}