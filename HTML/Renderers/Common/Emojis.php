<?php

namespace Numbers\Frontend\HTML\Renderers\Common;
class Emojis extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = '';
	public $orderby;
	public $columns = [
		'code' => ['name' => 'Code #', 'type' => 'varchar'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'html_code' => ['name' => 'HTML Code', 'type' => 'text'],
		'alternative' => ['name' => 'Altarnative', 'type' => 'mixed'],
	];

	/**
	 * Colors
	 *
	 * @var array
	 */
	public $data = [
		'-<@%' => ['name' => 'honeybee', 'html_code' => '1F41D', 'alternative' => []],
		':(|)' => ['name' => 'monkey_face', 'html_code' => '1F435', 'alternative' => []],
		':(:)' => ['name' => 'pig_face', 'html_code' => '1F437', 'alternative' => []],
		'<\3' => ['name' => 'broken_heart', 'html_code' => '1F494', 'alternative' => ['</3']],
		'<3' => ['name' => 'purple_heart', 'html_code' => '1F49C', 'alternative' => []],
		'~@~' => ['name' => 'pile_of_poo', 'html_code' => '1F4A9', 'alternative' => []],
		':D' => ['name' => 'grinning_face', 'html_code' => '1F600', 'alternative' => [':-D']],
		'^_^' => ['name' => 'grinning_face_with_smiling_eyes', 'html_code' => '1F601', 'alternative' => []],
		':)' => ['name' => 'smiling_face_with_open_mouth', 'html_code' => '1F603', 'alternative' => [':-)', '=)']],
		'=D' => ['name' => 'smiling_face_with_open_mouth_and_smiling_eyes', 'html_code' => '1F604', 'alternative' => []],
		'^_^;;' => ['name' => 'smiling_face_with_open_mouth_and_cold_sweat', 'html_code' => '1F605', 'alternative' => []],
		'O:)' => ['name' => 'smiling_face_with_halo', 'html_code' => '1F607', 'alternative' => ['O:-)', 'O=)']],
		'}:)' => ['name' => 'smiling_face_with_horns', 'html_code' => '1F608', 'alternative' => ['}:-)', '}=)']],
		';)' => ['name' => 'winking_face', 'html_code' => '1F609', 'alternative' => [';-)']],
		'B)' => ['name' => 'smiling_face_with_sunglasses', 'html_code' => '1F60E', 'alternative' => ['B-)']],
		':-|' => ['name' => 'neutral_face', 'html_code' => '1F610', 'alternative' => [':|', '=|']],
		'-_-' => ['name' => 'expressionless_face', 'html_code' => '1F611', 'alternative' => []],
		'o_o;' => ['name' => 'face_with_cold_sweat', 'html_code' => '1F613', 'alternative' => []],
		'u_u' => ['name' => 'pensive_face', 'html_code' => '1F614', 'alternative' => []],
		':\\' => ['name' => 'confused_face', 'html_code' => '1F615', 'alternative' => [':/', ':-\\', ':-/', '=\\', '=/']],
		':S' => ['name' => 'confounded_face', 'html_code' => '1F616', 'alternative' => [':-S', ':s', ':-s']],
		':*' => ['name' => 'kissing_face', 'html_code' => '1F617', 'alternative' => [':-*']],
		';*' => ['name' => 'face_throwing_a_kiss', 'html_code' => '1F618', 'alternative' => [';-*']],
		':P' => ['name' => 'face_with_stuck_out_tongue', 'html_code' => '1F61B', 'alternative' => [':-P', '=P', ':p', ':-p', '=p']],
		';P' => ['name' => 'face_with_stuck_out_tongue_and_winking_eye', 'html_code' => '1F61C', 'alternative' => [';-P', ';p', ';-p']],
		':(' => ['name' => 'disappointed_face', 'html_code' => '1F61E', 'alternative' => [':-(', '=(']],
		'>.<' => ['name' => 'pouting_face', 'html_code' => '1F621', 'alternative' => ['>:(', '>:-(', '>=(']],
		'T_T' => ['name' => 'crying_face', 'html_code' => '1F622', 'alternative' => [':\'(', ';_;',  '=\'(']],
		'>_<' => ['name' => 'persevering_face', 'html_code' => '1F623', 'alternative' => []],
		'D:' => ['name' => 'frowning_face_with_open_mouth', 'html_code' => '1F626', 'alternative' => []],
		'o.o' => ['name' => 'face_with_open_mouth', 'html_code' => '1F62E', 'alternative' => [':o', ':-o', '=o']],
		'O.O' => ['name' => 'astonished_face', 'html_code' => '1F632', 'alternative' => [':O', ':-O', '=O']],
		'x_x' => ['name' => 'dizzy_face', 'html_code' => '1F635', 'alternative' => ['X-O', 'X-o', 'X(', 'X-(']],
		':X)' => ['name' => 'grinning_cat_face_with_smiling_eyes', 'html_code' => '1F638', 'alternative' => [':3', '(=^..^=)', '(=^.^=)', '=^_^=']],
	];

	/**
	 * Determine text color
	 *
	 * @param string $color
	 * @return string
	 */
	public static function replaceEmoji(string $message) : string {
		$data = \Numbers\Frontend\HTML\Renderers\Common\Emojis::getStatic();
		$result = [];
		foreach ($data as $k => $v) {
			$result[$k] = '&#x' . $v['html_code'];
			if (!empty($v['alternative'])) {
				foreach ($v['alternative'] as $v2) {
					$result[$v2] = '&#x' . $v['html_code'];
				}
			}
		}
		return str_replace(array_keys($result), array_values($result), $message);
	}

	/**
	 * Render
	 *
	 * @param array $options
	 * @return string
	 */
	public function renderEmojis(array $options = []) : string {
		$data = $this->get();
		$result = '<div>';
			foreach ($data as $k => $v) {
				$result.= '<span class="emoji-icon" onclick="' . ($options['onclick']) . '" data-symbol="' . htmlentities($k) . '">' . '&#x' . $v['html_code'] .  '</span>';
			}
		$result.= '</div>';
		return $result;
	}
}