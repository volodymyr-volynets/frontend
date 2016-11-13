<?php

class numbers_frontend_html_class_html5 {

	/**
	 * Global attributes
	 *
	 * @var array
	 */
	public static $global = [
		'accesskey',
		'aria-',
		'class',
		'contenteditable',
		'contextmenu',
		'data-',
		'dir',
		'draggable',
		'dropzone',
		'hidden',
		'id',
		'inert',
		'itemid',
		'itemprop',
		'itemref',
		'itemscope',
		'itemtype',
		'lang',
		'role',
		'spellcheck',
		'style',
		'tabindex',
		'title',
		'translate',
	];

	/**
	 * Events
	 *
	 * @var array
	 */
	public static $events = [
		'onabort',
		'onblur',
		'oncanplay',
		'oncanplaythrough',
		'onchange',
		'onclick',
		'oncontextmenu',
		'ondblclick',
		'ondrag',
		'ondragend',
		'ondragenter',
		'ondragleave',
		'ondragover',
		'ondragstart',
		'ondrop',
		'ondurationchange',
		'onemptied',
		'onended',
		'onerror',
		'onfocus',
		'onformchange',
		'onforminput',
		'oninput',
		'oninvalid',
		'onkeydown',
		'onkeypress',
		'onkeyup',
		'onload',
		'onloadeddata',
		'onloadedmetadata',
		'onloadstart',
		'onmousedown',
		'onmousemove',
		'onmouseout',
		'onmouseover',
		'onmouseup',
		'onmousewheel',
		'onpause',
		'onplay',
		'onplaying',
		'onprogress',
		'onratechange',
		'onreset',
		'onreadystatechange',
		'onseeked',
		'onseeking',
		'onselect',
		'onshow',
		'onstalled',
		'onsubmit',
		'onsuspend',
		'ontimeupdate',
		'onvolumechange',
		'onwaiting',
	];

	public static $tag_specific = [
		'a' => ['href', 'target', 'ping', 'rel', 'media', 'hreflang', 'type'],
		'area' => ['alt', 'coords', 'shape', 'href', 'target', 'ping', 'rel', 'media', 'hreflang', 'type'],
		'audio' => ['src', 'crossorigin', 'preload', 'autoplay', 'mediagroup', 'loop', 'muted', 'controls'],
		'base' => ['href', 'target'],
		'blockquote' => ['cite'],
		'body' => ['onafterprint', 'onbeforeprint', 'onbeforeunload', 'onblur', 'onerror', 'onfocus', 'onhashchange', 'onload', 'onmessage', 'onoffline', 'ononline', 'onpagehide', 'onpageshow', 'onpopstate', 'onresize', 'onscroll', 'onstorage', 'onunload'],
		'button' => ['autofocus', 'disabled', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget', 'name', 'type', 'value'],
		'canvas' => ['width', 'height'],
		'col' => ['span'],
		'colgroup' => ['span'],
		'command' => ['type', 'label', 'icon', 'disabled', 'checked', 'radiogroup', 'command'],
		'data' => ['value'],
		'datalist' => ['option'],
		'del' => ['cite', 'datetime'],
		'details' => ['open'],
		'dialog' => ['open'],
		'embed' => ['src', 'type', 'width', 'height'],
		'fieldset' => ['disabled', 'form', 'name'],
		'form' => ['accept-charset', 'action', 'autocomplete', 'enctype', 'method', 'name', 'novalidate', 'target'],
		'html' => ['manifest'],
		'iframe' => ['src', 'srcdoc', 'name', 'sandbox', 'seamless', 'width', 'height'],
		'img' => ['alt', 'src', 'srcset', 'crossorigin', 'usemap', 'ismap', 'width', 'height'],
		'input' => ['accept', 'alt', 'autocomplete', 'autofocus', 'checked', 'dirname', 'disabled', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget', 'height', 'inputmode', 'list', 'max', 'maxlength', 'min', 'multiple', 'name', 'pattern', 'placeholder', 'readonly', 'required', 'size', 'src', 'step', 'type', 'value', 'width'],
		'ins' => ['cite', 'datetime'],
		'keygen' => ['autofocus', 'challenge', 'disabled', 'form', 'keytype', 'name'],
		'label' => ['form', 'for'],
		'li' => ['value'],
		'link' => ['href', 'rel', 'media', 'hreflang', 'type', 'sizes'],
		'map' => ['name'],
		'menu' => ['type', 'label'],
		'meta' => ['name', 'http-equiv', 'content', 'charset'],
		'meter' => ['value', 'min', 'max', 'low', 'high', 'optimum'],
		'object' => ['data', 'type', 'typemustmatch', 'name', 'usemap', 'form', 'width', 'height'],
		'ol' => ['reversed', 'start'],
		'optgroup' => ['disabled', 'label'],
		'option' => ['disabled', 'label', 'selected', 'value'],
		'output' => ['for', 'form name'],
		'param' => ['name', 'value'],
		'progress' => ['value', 'max'],
		'q' => ['cite'],
		'script' => ['src', 'async', 'defer', 'type', 'charset'],
		'select' => ['autofocus', 'disabled', 'form', 'multiple', 'name', 'required', 'size'],
		'source' => ['src', 'type', 'media'],
		'style' => ['media', 'type', 'scoped'],
		'td' => ['colspan', 'rowspan', 'headers'],
		'textarea' => ['autocomplete', 'autofocus', 'cols', 'dirname', 'disabled', 'form', 'inputmode', 'maxlength', 'name', 'placeholder', 'readonly', 'required', 'rows', 'wrap'],
		'th' => ['colspan', 'rowspan', 'headers', 'scope', 'abbr'],
		'time' => ['datetime', 'pubdate'],
		'track' => ['default', 'kind', 'label', 'src', 'srclang'],
		'video' => ['src', 'crossorigin', 'poster', 'preload', 'autoplay', 'mediagroup', 'loop', 'muted', 'controls', 'width', 'height']
	];

	/**
	 * Strip tags from these attributes
	 *
	 * @var array
	 */
	public static $strip_tags = ['title', 'placeholder'];

	/**
	 * Attributes injected by framework
	 *
	 * @var string
	 */
	public static $numbers = [
		'preset', 'searchable', 'tree', 'placeholder', 'color_picker', 'optgroups', 'title', 'field_values_key', 'field_value_hash'
	];

	/**
	 * Check if its valid HTML 5 attribute
	 *
	 * @param string $attribute
	 * @param string $tag
	 */
	public static function is_valid_html5_attribute($attribute, $tag = null) {
		$temp = explode('-', $attribute);
		$attribute = $temp[0];
		if (!empty($temp[1])) {
			$attribute.= '-';
		}
		// global first
		if (in_array($attribute, self::$global)) return true;
		if (in_array($attribute, self::$events)) return true;
		// tag specific first
		foreach (self::$tag_specific as $k => $v) {
			if (!empty($tag) && $tag != $k) continue;
			if (in_array($attribute, $v)) return true;
		}
		// attributes used by framework
		if (in_array($attribute, self::$numbers)) return true;
		return false;
	}
}