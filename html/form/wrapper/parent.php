<?php

class numbers_frontend_html_form_wrapper_parent extends object_override_data {

	/**
	 * Separators
	 */
	const separator_vertical = '__separator_vertical';
	const separator_horisontal = '__separator_horizontal';

	/**
	 * Row for buttons
	 */
	const buttons = '__submit_buttons';

	/**
	 * Row for batch buttons
	 */
	const batch_buttons = '__submit_batch_buttons';

	/**
	 * Hidden container/row
	 */
	const hidden = '__hidden_row_or_container';

	/**
	 * Submit button
	 */
	const button_submit = '__submit_button';
	const button_submit_data = ['order' => -100, 'button_group' => 'left', 'value' => 'Submit', 'type' => 'primary', 'method' => 'button2', 'accesskey' => 's', 'process_submit' => true];

	/**
	 * Submit save
	 */
	const button_submit_save = '__submit_save';
	const button_submit_save_data = ['order' => 100, 'button_group' => 'left', 'value' => 'Save', 'type' => 'primary', 'method' => 'button2', 'icon' => 'floppy-o', 'accesskey' => 's', 'process_submit' => true];

	/**
	 * Submit save and new
	 */
	const button_submit_save_and_new = '__submit_save_and_new';
	const button_submit_save_and_new_data = ['order' => 200, 'button_group' => 'left', 'value' => 'Save & New', 'type' => 'success', 'method' => 'button2', 'icon' => 'floppy-o', 'process_submit' => true];

	/**
	 * Submit save and close
	 */
	const button_submit_save_and_close = '__submit_save_and_close';
	const button_submit_save_and_close_data = ['order' => 300, 'button_group' => 'left', 'value' => 'Save & Close', 'type' => 'default', 'method' => 'button2', 'icon' => 'floppy-o', 'process_submit' => true];

	/**
	 * Delete button
	 */
	const button_submit_delete = '__submit_delete';
	const button_submit_delete_data = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'type' => 'danger', 'method' => 'button2', 'icon' => 'trash-o', 'accesskey' => 'd', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_delete];

	/**
	 * Reset button
	 */
	const button_submit_reset = '__submit_reset';
	const button_submit_reset_data = ['order' => 31000, 'button_group' => 'right', 'value' => 'Reset', 'type' => 'warning', 'input_type' => 'reset', 'icon' => 'ban', 'accesskey' => 'q', 'method' => 'button2', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_reset];

	/**
	 * Blank button
	 */
	const button_submit_blank = '__submit_blank';
	const button_submit_blank_data = ['order' => 30000, 'button_group' => 'right', 'value' => 'New', 'icon' => 'file-o', 'method' => 'button2', 'accesskey' => 'n', 'process_submit' => true, 'confirm_message' => object_content_messages::confirm_blank];

	/**
	 * Refresh button
	 */
	const button_submit_refresh = '__submit_refresh';
	const button_submit_refresh_data = ['order' => -100, 'button_group' => 'left', 'value' => 'Refresh', 'method' => 'button2', 'icon' => 'refresh', 'accesskey' => 'r', 'process_submit' => true];

	/**
	 * Post button
	 */
	const button_submit_post = '__submit_post';
	const button_submit_post_data = ['order' => 150, 'button_group' => 'left', 'value' => 'Post', 'type' => 'warning', 'method' => 'button2', 'accesskey' => 'p', 'process_submit' => true];

	/**
	 * Post provisionally button
	 */
	const button_submit_post_provisionally = '__submit_post_provisionally';
	const button_submit_post_provisionally_data = ['order' => 151, 'button_group' => 'left', 'value' => 'Post Provisionally', 'type' => 'success', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Ready to post button
	 */
	const button_submit_ready_to_post = '__submit_ready_to_post';
	const button_submit_ready_to_post_data = ['order' => 150, 'button_group' => 'center', 'value' => 'Ready To Post', 'type' => 'info', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Open button
	 */
	const button_submit_open = '__submit_open';
	const button_submit_open_data = ['order' => 151, 'button_group' => 'center', 'value' => 'Open', 'type' => 'info', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Standard buttons
	 */
	const buttons_data_group = [
		self::button_submit_save => self::button_submit_save_data,
		self::button_submit_save_and_new => self::button_submit_save_and_new_data,
		self::button_submit_save_and_close => self::button_submit_save_and_close_data,
		self::button_submit_blank => self::button_submit_blank_data,
		self::button_submit_reset => self::button_submit_reset_data,
		self::button_submit_delete => self::button_submit_delete_data
	];

	/**
	 * Standard buttons for batches
	 */
	const batch_buttons_data_group = [
		self::button_submit_save => self::button_submit_save_data,
		self::button_submit_post => self::button_submit_post_data,
		self::button_submit_post_provisionally => self::button_submit_post_provisionally_data,
		self::button_submit_ready_to_post => self::button_submit_ready_to_post_data,
		self::button_submit_open => self::button_submit_open_data,
		self::button_submit_reset => self::button_submit_reset_data,
		self::button_submit_delete => self::button_submit_delete_data
	];

	/**
	 * Entry attributes
	 */
	const attributes = '__attributes_entries';
	const attribute_data = ['order' => PHP_INT_MAX - 1000, 'label_name' => 'Attributes'];

	/**
	 * Details attributes
	 */
	const attribute_details = '__attributes_deatils';
	const attribute_details_data = [
		'label_name' => 'Attributes',
		'type' => 'subdetails',
		'details_rendering_type' => 'table',
		'details_new_rows' => 3,
		'details_parent_key' => null,
		'details_key' => null,
		'details_pk' => null,
		'order' => PHP_INT_MAX - 1000,
		'required' => false
	];

	/**
	 * Addresses
	 */
	const addresses = '__widget_addresses';
	const addresses_data = ['order' => PHP_INT_MAX - 2000, 'label_name' => 'Addresses'];

	/**
	 * All available widgets
	 */
	const widgets = [self::attributes, self::addresses];
}