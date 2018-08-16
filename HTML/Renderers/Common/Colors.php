<?php

namespace Numbers\Frontend\HTML\Renderers\Common;
class Colors extends \Object\Data {
	public $column_key = 'color';
	public $column_prefix = '';
	public $orderby;
	public $columns = [
		'color' => ['name' => 'Color #', 'domain' => 'html_color_code'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'html_name' => ['name' => 'HTML Name', 'type' => 'text'],
		'optgroup' => ['name' => 'Group', 'type' => 'text'],
	];
	public $optgroups_map = [
		'column' => 'optgroup'
	];

	/**
	 * Colors
	 *
	 * @var array
	 */
	public $data = [
		// Basic
		'FFFFFF' => ['name' => 'White', 'html_name' => 'white', 'optgroup' => 'Basic'],
		'808080' => ['name' => 'Gray', 'html_name' => 'gray', 'optgroup' => 'Basic'],
		'C0C0C0' => ['name' => 'Silver', 'html_name' => 'silver', 'optgroup' => 'Basic'],
		'000000' => ['name' => 'Black', 'html_name' => 'black', 'optgroup' => 'Basic'],
		'800000' => ['name' => 'Maroon', 'html_name' => 'maroon', 'optgroup' => 'Basic'],
		'FF0000' => ['name' => 'Red', 'html_name' => 'red', 'optgroup' => 'Basic'],
		'800080' => ['name' => 'Purple', 'html_name' => 'purple', 'optgroup' => 'Basic'],
		'FF00FF' => ['name' => 'Fuchsia', 'html_name' => 'fuchsia', 'optgroup' => 'Basic'],
		'008000' => ['name' => 'Green', 'html_name' => 'green', 'optgroup' => 'Basic'],
		'00FF00' => ['name' => 'Lime', 'html_name' => 'lime', 'optgroup' => 'Basic'],
		'808000' => ['name' => 'Olive', 'html_name' => 'olive', 'optgroup' => 'Basic'],
		'FFFF00' => ['name' => 'Yellow', 'html_name' => 'yellow', 'optgroup' => 'Basic'],
		'000080' => ['name' => 'Navy', 'html_name' => 'navy', 'optgroup' => 'Basic'],
		'0000FF' => ['name' => 'Blue', 'html_name' => 'blue', 'optgroup' => 'Basic'],
		'008080' => ['name' => 'Teal', 'html_name' => 'teal', 'optgroup' => 'Basic'],
		'00FFFF' => ['name' => 'Aqua', 'html_name' => 'aqua', 'optgroup' => 'Basic'],
		'FFA500' => ['name' => 'Orange', 'html_name' => 'orange', 'optgroup' => 'Basic'],
		// Red(s)
		'CD5C5C' => ['name' => 'Indian Red', 'html_name' => 'indianred', 'optgroup' => 'Red(s)'],
		'F08080' => ['name' => 'Light Coral', 'html_name' => 'lightcoral', 'optgroup' => 'Red(s)'],
		'FA8072' => ['name' => 'Salmon', 'html_name' => 'salmon', 'optgroup' => 'Red(s)'],
		'E9967A' => ['name' => 'Dark Salmon', 'html_name' => 'darksalmon', 'optgroup' => 'Red(s)'],
		'FFA07A' => ['name' => 'Light Salmon', 'html_name' => 'lightsalmon', 'optgroup' => 'Red(s)'],
		'DC143C' => ['name' => 'Crimson', 'html_name' => 'crimson', 'optgroup' => 'Red(s)'],
		'B22222' => ['name' => 'Firebrick', 'html_name' => 'firebrick', 'optgroup' => 'Red(s)'],
		'8B0000' => ['name' => 'Dark Red', 'html_name' => 'darkred', 'optgroup' => 'Red(s)'],
		// Pink(s)
		'FFC0CB' => ['name' => 'Pink', 'html_name' => 'pink', 'optgroup' => 'Pink(s)'],
		'FFB6C1' => ['name' => 'Light Pink', 'html_name' => 'lightpink', 'optgroup' => 'Pink(s)'],
		'FF69B4' => ['name' => 'Hot Pink', 'html_name' => 'hotpink', 'optgroup' => 'Pink(s)'],
		'FF1493' => ['name' => 'Deep Pink', 'html_name' => 'deeppink', 'optgroup' => 'Pink(s)'],
		'C71585' => ['name' => 'Medium Violet Red', 'html_name' => 'mediumvioletred', 'optgroup' => 'Pink(s)'],
		'DB7093' => ['name' => 'Pale Violet Red', 'html_name' => 'palevioletred', 'optgroup' => 'Pink(s)'],
		// Orange(s)
		'FFA07A' => ['name' => 'Light Salmon', 'html_name' => 'lightsalmon', 'optgroup' => 'Orange(s)'],
		'FF7F50' => ['name' => 'Coral', 'html_name' => 'coral', 'optgroup' => 'Orange(s)'],
		'FF6347' => ['name' => 'Tomato', 'html_name' => 'tomato', 'optgroup' => 'Orange(s)'],
		'FF4500' => ['name' => 'Orange Red', 'html_name' => 'orangered', 'optgroup' => 'Orange(s)'],
		'FF8C00' => ['name' => 'Dark Orange', 'html_name' => 'darkorange', 'optgroup' => 'Orange(s)'],
		// Yellow(s)
		'FFD700' => ['name' => 'Gold', 'html_name' => 'gold', 'optgroup' => 'Yellow(s)'],
		'FFFFE0' => ['name' => 'Light Yellow', 'html_name' => 'lightyellow', 'optgroup' => 'Yellow(s)'],
		'FFFACD' => ['name' => 'Lemon Chiffon', 'html_name' => 'lemonchiffon', 'optgroup' => 'Yellow(s)'],
		'FAFAD2' => ['name' => 'Light Goldenrod Yellow', 'html_name' => 'lightgoldenrodyellow', 'optgroup' => 'Yellow(s)'],
		'FFEFD5' => ['name' => 'Papaya Whip', 'html_name' => 'papayawhip', 'optgroup' => 'Yellow(s)'],
		'FFE4B5' => ['name' => 'Moccasin', 'html_name' => 'moccasin', 'optgroup' => 'Yellow(s)'],
		'FFDAB9' => ['name' => 'Peach Puff', 'html_name' => 'peachpuff', 'optgroup' => 'Yellow(s)'],
		'EEE8AA' => ['name' => 'Pale Goldenrod', 'html_name' => 'palegoldenrod', 'optgroup' => 'Yellow(s)'],
		'F0E68C' => ['name' => 'Khaki', 'html_name' => 'khaki', 'optgroup' => 'Yellow(s)'],
		'BDB76B' => ['name' => 'Dark Khaki', 'html_name' => 'darkkhaki', 'optgroup' => 'Yellow(s)'],
		// Purple(s)
		'E6E6FA' => ['name' => 'Lavender', 'html_name' => 'lavender', 'optgroup' => 'Purple(s)'],
		'D8BFD8' => ['name' => 'Thistle', 'html_name' => 'thistle', 'optgroup' => 'Purple(s)'],
		'DDA0DD' => ['name' => 'Plum', 'html_name' => 'plum', 'optgroup' => 'Purple(s)'],
		'EE82EE' => ['name' => 'Violet', 'html_name' => 'violet', 'optgroup' => 'Purple(s)'],
		'DA70D6' => ['name' => 'Orchid', 'html_name' => 'orchid', 'optgroup' => 'Purple(s)'],
		'BA55D3' => ['name' => 'Medium Orchid', 'html_name' => 'mediumorchid', 'optgroup' => 'Purple(s)'],
		'9370DB' => ['name' => 'Medium Purple', 'html_name' => 'mediumpurple', 'optgroup' => 'Purple(s)'],
		'8A2BE2' => ['name' => 'Blue Violet', 'html_name' => 'blueviolet', 'optgroup' => 'Purple(s)'],
		'9400D3' => ['name' => 'Dark Violet', 'html_name' => 'darkviolet', 'optgroup' => 'Purple(s)'],
		'9932CC' => ['name' => 'Dark Orchid', 'html_name' => 'darkorchid', 'optgroup' => 'Purple(s)'],
		'8B008B' => ['name' => 'Dark Magenta', 'html_name' => 'darkmagenta', 'optgroup' => 'Purple(s)'],
		'4B0082' => ['name' => 'Indigo', 'html_name' => 'indigo', 'optgroup' => 'Purple(s)'],
		'6A5ACD' => ['name' => 'Slate Blue', 'html_name' => 'slateblue', 'optgroup' => 'Purple(s)'],
		'483D8B' => ['name' => 'Dark Slate Blue', 'html_name' => 'darkslateblue', 'optgroup' => 'Purple(s)'],
		'7B68EE' => ['name' => 'Medium Slate Blue', 'html_name' => 'mediumslateblue', 'optgroup' => 'Purple(s)'],
		// Green(s)
		'ADFF2F' => ['name' => 'Green Yellow', 'html_name' => 'greenyellow', 'optgroup' => 'Green(s)'],
		'7FFF00' => ['name' => 'Chartreuse', 'html_name' => 'chartreuse', 'optgroup' => 'Green(s)'],
		'7CFC00' => ['name' => 'Lawn Green', 'html_name' => 'lawngreen', 'optgroup' => 'Green(s)'],
		'32CD32' => ['name' => 'Lime Green', 'html_name' => 'limegreen', 'optgroup' => 'Green(s)'],
		'98FB98' => ['name' => 'Pale Green', 'html_name' => 'palegreen', 'optgroup' => 'Green(s)'],
		'90EE90' => ['name' => 'Light Green', 'html_name' => 'lightgreen', 'optgroup' => 'Green(s)'],
		'00FA9A' => ['name' => 'Medium Spring Green', 'html_name' => 'mediumspringgreen', 'optgroup' => 'Green(s)'],
		'00FF7F' => ['name' => 'Spring Green', 'html_name' => 'springgreen', 'optgroup' => 'Green(s)'],
		'3CB371' => ['name' => 'Medium Sea Green', 'html_name' => 'mediumseagreen', 'optgroup' => 'Green(s)'],
		'2E8B57' => ['name' => 'Sea Green', 'html_name' => 'seagreen', 'optgroup' => 'Green(s)'],
		'228B22' => ['name' => 'Forest Green', 'html_name' => 'forestgreen', 'optgroup' => 'Green(s)'],
		'006400' => ['name' => 'Dark Green', 'html_name' => 'darkgreen', 'optgroup' => 'Green(s)'],
		'9ACD32' => ['name' => 'Yellow Green', 'html_name' => 'yellowgreen', 'optgroup' => 'Green(s)'],
		'6B8E23' => ['name' => 'Olive Drab', 'html_name' => 'olivedrab', 'optgroup' => 'Green(s)'],
		'556B2F' => ['name' => 'Dark Olive Green', 'html_name' => 'darkolivegreen', 'optgroup' => 'Green(s)'],
		'66CDAA' => ['name' => 'Medium Aquamarine', 'html_name' => 'mediumaquamarine', 'optgroup' => 'Green(s)'],
		'8FBC8F' => ['name' => 'Dark Sea Green', 'html_name' => 'darkseagreen', 'optgroup' => 'Green(s)'],
		'20B2AA' => ['name' => 'Light Sea Green', 'html_name' => 'lightseagreen', 'optgroup' => 'Green(s)'],
		'008B8B' => ['name' => 'Dark Cyan', 'html_name' => 'darkcyan', 'optgroup' => 'Green(s)'],
		// Blue(s)
		'E0FFFF' => ['name' => 'Light Cyan', 'html_name' => 'lightcyan', 'optgroup' => 'Blue(s)'],
		'AFEEEE' => ['name' => 'Pale Turquoise', 'html_name' => 'paleturquoise', 'optgroup' => 'Blue(s)'],
		'7FFFD4' => ['name' => 'Aquamarine', 'html_name' => 'aquamarine', 'optgroup' => 'Blue(s)'],
		'40E0D0' => ['name' => 'Turquoise', 'html_name' => 'turquoise', 'optgroup' => 'Blue(s)'],
		'48D1CC' => ['name' => 'Medium Turquoise', 'html_name' => 'mediumturquoise', 'optgroup' => 'Blue(s)'],
		'00CED1' => ['name' => 'Dark Turquoise', 'html_name' => 'darkturquoise', 'optgroup' => 'Blue(s)'],
		'5F9EA0' => ['name' => 'Cadet Blue', 'html_name' => 'cadetblue', 'optgroup' => 'Blue(s)'],
		'4682B4' => ['name' => 'Steel Blue', 'html_name' => 'steelblue', 'optgroup' => 'Blue(s)'],
		'B0C4DE' => ['name' => 'Light Steel Blue', 'html_name' => 'lightsteelblue', 'optgroup' => 'Blue(s)'],
		'B0E0E6' => ['name' => 'Powder Blue', 'html_name' => 'powderblue', 'optgroup' => 'Blue(s)'],
		'ADD8E6' => ['name' => 'Light Blue', 'html_name' => 'lightblue', 'optgroup' => 'Blue(s)'],
		'87CEEB' => ['name' => 'Sky Blue', 'html_name' => 'skyblue', 'optgroup' => 'Blue(s)'],
		'87CEFA' => ['name' => 'Light Sky Blue', 'html_name' => 'lightskyblue', 'optgroup' => 'Blue(s)'],
		'00BFFF' => ['name' => 'Deep Sky Blue', 'html_name' => 'deepskyblue', 'optgroup' => 'Blue(s)'],
		'1E90FF' => ['name' => 'Dodger Blue', 'html_name' => 'dodgerblue', 'optgroup' => 'Blue(s)'],
		'6495ED' => ['name' => 'Cornflower Blue', 'html_name' => 'cornflowerblue', 'optgroup' => 'Blue(s)'],
		'7B68EE' => ['name' => 'Medium Slate Blue', 'html_name' => 'mediumslateblue', 'optgroup' => 'Blue(s)'],
		'4169E1' => ['name' => 'Royal Blue', 'html_name' => 'royalblue', 'optgroup' => 'Blue(s)'],
		'0000CD' => ['name' => 'Medium Blue', 'html_name' => 'mediumblue', 'optgroup' => 'Blue(s)'],
		'00008B' => ['name' => 'Dark Blue', 'html_name' => 'darkblue', 'optgroup' => 'Blue(s)'],
		'191970' => ['name' => 'Midnight Blue', 'html_name' => 'midnightblue', 'optgroup' => 'Blue(s)'],
		// Brown(s)
		'FFF8DC' => ['name' => 'Cornsilk', 'html_name' => 'cornsilk', 'optgroup' => 'Brown(s)'],
		'FFEBCD' => ['name' => 'Blanched Almond', 'html_name' => 'blanchedalmond', 'optgroup' => 'Brown(s)'],
		'FFE4C4' => ['name' => 'Bisque', 'html_name' => 'bisque', 'optgroup' => 'Brown(s)'],
		'FFDEAD' => ['name' => 'Navajo White', 'html_name' => 'navajowhite', 'optgroup' => 'Brown(s)'],
		'F5DEB3' => ['name' => 'Wheat', 'html_name' => 'wheat', 'optgroup' => 'Brown(s)'],
		'DEB887' => ['name' => 'Burlywood', 'html_name' => 'burlywood', 'optgroup' => 'Brown(s)'],
		'D2B48C' => ['name' => 'Tan', 'html_name' => 'tan', 'optgroup' => 'Brown(s)'],
		'BC8F8F' => ['name' => 'Rosy Brown', 'html_name' => 'rosybrown', 'optgroup' => 'Brown(s)'],
		'F4A460' => ['name' => 'Sandy Brown', 'html_name' => 'sandybrown', 'optgroup' => 'Brown(s)'],
		'DAA520' => ['name' => 'Goldenrod', 'html_name' => 'goldenrod', 'optgroup' => 'Brown(s)'],
		'B8860B' => ['name' => 'Dark Goldenrod', 'html_name' => 'darkgoldenrod', 'optgroup' => 'Brown(s)'],
		'CD853F' => ['name' => 'Peru', 'html_name' => 'peru', 'optgroup' => 'Brown(s)'],
		'D2691E' => ['name' => 'Chocolate', 'html_name' => 'chocolate', 'optgroup' => 'Brown(s)'],
		'8B4513' => ['name' => 'Saddle Brown', 'html_name' => 'saddlebrown', 'optgroup' => 'Brown(s)'],
		'A0522D' => ['name' => 'Sienna', 'html_name' => 'sienna', 'optgroup' => 'Brown(s)'],
		'A52A2A' => ['name' => 'Brown', 'html_name' => 'brown', 'optgroup' => 'Brown(s)'],
		// White(s)
		'FFFAFA' => ['name' => 'Snow', 'html_name' => 'snow', 'optgroup' => 'White(s)'],
		'F0FFF0' => ['name' => 'Honeydew', 'html_name' => 'honeydew', 'optgroup' => 'White(s)'],
		'F5FFFA' => ['name' => 'Mint Cream', 'html_name' => 'mintcream', 'optgroup' => 'White(s)'],
		'F0FFFF' => ['name' => 'Azure', 'html_name' => 'azure', 'optgroup' => 'White(s)'],
		'F0F8FF' => ['name' => 'Alice Blue', 'html_name' => 'aliceblue', 'optgroup' => 'White(s)'],
		'F8F8FF' => ['name' => 'Ghost White', 'html_name' => 'ghostwhite', 'optgroup' => 'White(s)'],
		'F5F5F5' => ['name' => 'White Smoke', 'html_name' => 'whitesmoke', 'optgroup' => 'White(s)'],
		'FFF5EE' => ['name' => 'Seashell', 'html_name' => 'seashell', 'optgroup' => 'White(s)'],
		'F5F5DC' => ['name' => 'Beige', 'html_name' => 'beige', 'optgroup' => 'White(s)'],
		'FDF5E6' => ['name' => 'Old Lace', 'html_name' => 'oldlace', 'optgroup' => 'White(s)'],
		'FFFAF0' => ['name' => 'Floral White', 'html_name' => 'floralwhite', 'optgroup' => 'White(s)'],
		'FFFFF0' => ['name' => 'Ivory', 'html_name' => 'ivory', 'optgroup' => 'White(s)'],
		'FAEBD7' => ['name' => 'Antique White', 'html_name' => 'antiquewhite', 'optgroup' => 'White(s)'],
		'FAF0E6' => ['name' => 'Linen', 'html_name' => 'linen', 'optgroup' => 'White(s)'],
		'FFF0F5' => ['name' => 'Lavender Blush', 'html_name' => 'lavenderblush', 'optgroup' => 'White(s)'],
		'FFE4E1' => ['name' => 'Misty Rose', 'html_name' => 'mistyrose', 'optgroup' => 'White(s)'],
		// Grey(s)
		'DCDCDC' => ['name' => 'Gainsboro', 'html_name' => 'gainsboro', 'optgroup' => 'Grey(s)'],
		'D3D3D3' => ['name' => 'Light Gray', 'html_name' => 'lightgray', 'optgroup' => 'Grey(s)'],
		'A9A9A9' => ['name' => 'Dark Gray', 'html_name' => 'darkgray', 'optgroup' => 'Grey(s)'],
		'696969' => ['name' => 'Dim Gray', 'html_name' => 'dimgray', 'optgroup' => 'Grey(s)'],
		'778899' => ['name' => 'Light Slate Gray', 'html_name' => 'lightslategray', 'optgroup' => 'Grey(s)'],
		'708090' => ['name' => 'Slate Gray', 'html_name' => 'slategray', 'optgroup' => 'Grey(s)'],
		'2F4F4F' => ['name' => 'Dark Slate Gray', 'html_name' => 'darkslategray', 'optgroup' => 'Grey(s)'],
	];

	/**
	 * Determine text color
	 *
	 * @param string $color
	 * @return string
	 */
	public static function determineTextColor(string $color) : string {
		$color = hex2rgb($color);
		$luma = ($color[0] + $color[1] + $color[2]) / 3;
		if ($luma < 128){
			return '#FFFFFF';
		}else{
			return '#000000';
		}
	}

	/**
	 * Color from string
	 *
	 * @param string $string
	 * @return string
	 */
	public static function colorFromString(string $string) : string {
		return '#' . substr(md5($string), 0, 6);
	}
}