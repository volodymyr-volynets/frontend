<?php

class numbers_frontend_components_wysiwyg_tinymce_base implements numbers_frontend_components_wysiwyg_interface_base {

	/**
	 * see html::wysiwyg();
	 */
	public static function wysiwyg($options = []) {
		// tinymce library
		library::add('tinymce');
		$options['class'] = $options['class'] ?? '';
		$options['class'].= ' wysiwyg';
		layout::onload("tinymce.init({selector: 'textarea.wysiwyg', height: 500, plugins: ['advlist autolink lists link image charmap print preview hr anchor pagebreak','searchreplace wordcount visualblocks visualchars code fullscreen','insertdatetime media nonbreaking save table contextmenu directionality','emoticons template paste textcolor colorpicker textpattern imagetools'],toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',toolbar2: 'print preview media | forecolor backcolor emoticons'});");
		return html::textarea($options);
	}
}