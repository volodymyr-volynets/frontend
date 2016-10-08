<?php

class numbers_frontend_html_form_wrapper_parent extends object_override_data {

	/**
	 * Separators
	 */
	const SEPARATOR_VERTICAL = '__separator_vertical';
	const SEPARATOR_HORISONTAL = '__separator_horizontal';

	/**
	 * Row for buttons
	 */
	const BUTTONS = '__submit_buttons';

	/**
	 * Submit button
	 */
	const BUTTON_SUBMIT = 'submit_button';
	const BUTTON_SUBMIT_DATA = ['order' => -1, 'button_group' => 'left', 'value' => 'Submit', 'type' => 'primary', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Submit save presets
	 */
	const BUTTON_SUBMIT_SAVE = 'submit_save';
	const BUTTON_SUBMIT_SAVE_DATA = ['order' => 1, 'button_group' => 'left', 'value' => 'Save', 'type' => 'primary', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Submit save and new
	 */
	const BUTTON_SUBMIT_SAVE_AND_NEW = 'submit_save_and_new';
	const BUTTON_SUBMIT_SAVE_AND_NEW_DATA = ['order' => 2, 'button_group' => 'left', 'value' => 'Save & New', 'type' => 'success', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Submit save and close
	 */
	const BUTTON_SUBMIT_SAVE_AND_CLOSE = 'submit_save_and_close';
	const BUTTON_SUBMIT_SAVE_AND_CLOSE_DATA = ['order' => 3, 'button_group' => 'left', 'value' => 'Save & Close', 'type' => 'default', 'method' => 'button2', 'process_submit' => true];

	/**
	 * Delete button
	 */
	const BUTTON_SUBMIT_DELETE = 'submit_delete';
	const BUTTON_SUBMIT_DELETE_DATA = ['order' => 32000, 'button_group' => 'right', 'value' => 'Delete', 'type' => 'danger', 'method' => 'button2', 'process_submit' => true, 'confirm_message' => 'Are you sure you want to delete this record?'];
}