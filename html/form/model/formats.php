<?php

class numbers_frontend_html_form_model_formats extends object_data {
	public $column_key = 'code';
	public $column_prefix = '';
	public $orderby = ['order' => SORT_ASC];
	public $columns = [
		'code' => ['name' => 'Code', 'domain' => 'type_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'file_extension' => ['name' => 'File Extension', 'type' => 'text'],
		'content_type' => ['name' => 'Content Type', 'type' => 'text'],
		'custom_renderer' => ['name' => 'Custom Renderer', 'type' => 'text'],
		'delimiter' => ['name' => 'Delimiter', 'type' => 'text'],
		'enclosure' => ['name' => 'Enclosure', 'type' => 'text'],
		'order' => ['name' => 'Code', 'domain' => 'order'],
	];
	public $data = [
		'screen' => ['name' => 'Screen', 'order' => 1, 'file_extension' => 'html', 'content_type' => 'text/html', 'custom_renderer' => 'numbers_frontend_html_form_renderer_report_screen'],
		'printable' => ['name' => 'Printable', 'order' => 2, 'file_extension' => 'html', 'content_type' => 'text/html', 'custom_renderer' => 'numbers_frontend_html_form_renderer_report_screen'],
		// todo: implement these
		//'pdf' => ['name' => 'PDF', 'order' => 3, 'file_extension' => 'pdf', 'content_type' => 'application/pdf'],
		//'csv' => ['name' => 'CSV (Comma Delimited)', 'order' => 4, 'delimiter' => ',', 'enclosure' => '"', 'file_extension' => 'csv', 'content_type' => 'application/octet-stream'],
		//'txt' => ['name' => 'Text (Tab Delimited)', 'order' => 5, 'delimiter' => "\t", 'enclosure' => '"', 'file_extension' => 'txt', 'content_type' => 'application/octet-stream']
	];
}