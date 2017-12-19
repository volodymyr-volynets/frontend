<?php

namespace Numbers\Frontend\Components\Wysiwyg\TinyMCE;
class Base implements \Numbers\Frontend\Components\Wysiwyg\Interface2\Base {

	/**
	 * see \HTML::wysiwyg();
	 */
	public static function wysiwyg($options = []) {
		// tinymce library
		\Library::add('TinyMCE');
		$options['class'] = $options['class'] ?? '';
		$options['class'].= ' wysiwyg';
		\Layout::onload("tinymce.init({selector: 'textarea.wysiwyg', height: 500, plugins: ['advlist autolink lists link image charmap print preview hr anchor pagebreak','searchreplace wordcount visualblocks visualchars code fullscreen','insertdatetime media nonbreaking save table contextmenu directionality','emoticons template paste textcolor colorpicker textpattern imagetools'],toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',toolbar2: 'print preview media | forecolor backcolor emoticons'});");
		return \HTML::textarea($options);
	}
}