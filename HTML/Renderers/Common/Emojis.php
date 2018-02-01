<?php

namespace Numbers\Frontend\HTML\Renderers\Common;
class Emojis extends \Object\Data {
	public $column_key = 'code';
	public $column_prefix = '';
	public $orderby = [
		'code' => SORT_ASC
	];
	public $columns = [
		'code' => ['name' => 'Code #', 'type' => 'varchar'],
		'name' => ['name' => 'Name', 'type' => 'text'],
		'html_code' => ['name' => 'HTML Code', 'type' => 'text'],
		'alternative' => ['name' => 'Altarnative', 'type' => 'mixed'],
		'group' => ['name' => 'Group', 'type' => 'varchar'],
	];

	/**
	 * Colors
	 *
	 * @var array
	 */
	public $data = [
		'-<@%' => ['name' => 'honeybee', 'html_code' => '1F41D', 'alternative' => [], 'group' => 'Faces'],
		':(|)' => ['name' => 'monkey_face', 'html_code' => '1F435', 'alternative' => [], 'group' => 'Faces'],
		':(:)' => ['name' => 'pig_face', 'html_code' => '1F437', 'alternative' => [], 'group' => 'Faces'],
		'</3' => ['name' => 'broken_heart', 'html_code' => '1F494', 'alternative' => ['<\3'], 'group' => 'Faces'],
		'<3' => ['name' => 'purple_heart', 'html_code' => '1F49C', 'alternative' => [], 'group' => 'Faces'],
		'~@~' => ['name' => 'pile_of_poo', 'html_code' => '1F4A9', 'alternative' => [], 'group' => 'Faces'],
		':D' => ['name' => 'grinning_face', 'html_code' => '1F600', 'alternative' => [':-D'], 'group' => 'Faces'],
		'^_^' => ['name' => 'grinning_face_with_smiling_eyes', 'html_code' => '1F601', 'alternative' => [], 'group' => 'Faces'],
		':)' => ['name' => 'smiling_face_with_open_mouth', 'html_code' => '1F603', 'alternative' => [':-)', '=)'], 'group' => 'Faces'],
		'=D' => ['name' => 'smiling_face_with_open_mouth_and_smiling_eyes', 'html_code' => '1F604', 'alternative' => [], 'group' => 'Faces'],
		'^_^;;' => ['name' => 'smiling_face_with_open_mouth_and_cold_sweat', 'html_code' => '1F605', 'alternative' => [], 'group' => 'Faces'],
		'O:)' => ['name' => 'smiling_face_with_halo', 'html_code' => '1F607', 'alternative' => ['O:-)', 'O=)'], 'group' => 'Faces'],
		'}:)' => ['name' => 'smiling_face_with_horns', 'html_code' => '1F608', 'alternative' => ['}:-)', '}=)'], 'group' => 'Faces'],
		';)' => ['name' => 'winking_face', 'html_code' => '1F609', 'alternative' => [';-)'], 'group' => 'Faces'],
		'B)' => ['name' => 'smiling_face_with_sunglasses', 'html_code' => '1F60E', 'alternative' => ['B-)'], 'group' => 'Faces'],
		':-|' => ['name' => 'neutral_face', 'html_code' => '1F610', 'alternative' => [':|', '=|'], 'group' => 'Faces'],
		'-_-' => ['name' => 'expressionless_face', 'html_code' => '1F611', 'alternative' => [], 'group' => 'Faces'],
		'o_o;' => ['name' => 'face_with_cold_sweat', 'html_code' => '1F613', 'alternative' => [], 'group' => 'Faces'],
		'u_u' => ['name' => 'pensive_face', 'html_code' => '1F614', 'alternative' => [], 'group' => 'Faces'],
		':\\ ' => ['name' => 'confused_face', 'html_code' => '1F615', 'alternative' => [':/', ':-\\', ':-/', '=\\', '=/'], 'group' => 'Faces'],
		':S' => ['name' => 'confounded_face', 'html_code' => '1F616', 'alternative' => [':-S', ':s', ':-s'], 'group' => 'Faces'],
		':*' => ['name' => 'kissing_face', 'html_code' => '1F617', 'alternative' => [':-*'], 'group' => 'Faces'],
		';*' => ['name' => 'face_throwing_a_kiss', 'html_code' => '1F618', 'alternative' => [';-*'], 'group' => 'Faces'],
		':P' => ['name' => 'face_with_stuck_out_tongue', 'html_code' => '1F61B', 'alternative' => [':-P', '=P', ':p', ':-p', '=p'], 'group' => 'Faces'],
		';P' => ['name' => 'face_with_stuck_out_tongue_and_winking_eye', 'html_code' => '1F61C', 'alternative' => [';-P', ';p', ';-p'], 'group' => 'Faces'],
		':(' => ['name' => 'disappointed_face', 'html_code' => '1F61E', 'alternative' => [':-(', '=('], 'group' => 'Faces'],
		'>.<' => ['name' => 'pouting_face', 'html_code' => '1F621', 'alternative' => ['>:(', '>:-(', '>=('], 'group' => 'Faces'],
		'T_T' => ['name' => 'crying_face', 'html_code' => '1F622', 'alternative' => [':\'(', ';_;',  '=\'('], 'group' => 'Faces'],
		'>_<' => ['name' => 'persevering_face', 'html_code' => '1F623', 'alternative' => [], 'group' => 'Faces'],
		'D:' => ['name' => 'frowning_face_with_open_mouth', 'html_code' => '1F626', 'alternative' => [], 'group' => 'Faces'],
		'o.o' => ['name' => 'face_with_open_mouth', 'html_code' => '1F62E', 'alternative' => [':o', ':-o', '=o'], 'group' => 'Faces'],
		'O.O' => ['name' => 'astonished_face', 'html_code' => '1F632', 'alternative' => [':O', ':-O', '=O'], 'group' => 'Faces'],
		'x_x' => ['name' => 'dizzy_face', 'html_code' => '1F635', 'alternative' => ['X-O', 'X-o', 'X(', 'X-('], 'group' => 'Faces'],
		':X)' => ['name' => 'grinning_cat_face_with_smiling_eyes', 'html_code' => '1F638', 'alternative' => [':3', '(=^..^=)', '(=^.^=)', '=^_^='], 'group' => 'Faces'],
		':ok_hand:' => ['name' => 'ok_hand', 'html_code' => '1F44C', 'alternative' => [], 'group' => 'Hands'],
		':thumbsup:' => ['name' => 'thumbsup', 'html_code' => '1F44D', 'alternative' => [':+1:'], 'group' => 'Hands'],
		':call_me:' => ['name' => 'call_me', 'html_code' => '1F919', 'alternative' => [], 'group' => 'Hands'],
		//':v:' => ['name' => 'victory', 'html_code' => 'FE0F', 'alternative' => [], 'group' => 'Hands'],
		':facepunch:' => ['name' => 'facepunch', 'html_code' => '1F44A', 'alternative' => [':punch:'], 'group' => 'Hands'],
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
		// replace longer keys first
		uksort($result, function($a, $b) {
			return strlen($b) <=> strlen($a);
		});
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
			pk(['group', 'code'], $data);
			$first = false;
			foreach ($data as $k => $v) {
				if ($first) $result.= '<br/><hr class="simple" />';
				$first = true;
				foreach ($v as $k2 => $v2) {
					$result.= '<span class="chat_emoji_icon" onclick="' . ($options['onclick']) . '" data-symbol="' . htmlentities($k2,  ENT_HTML5) . '">' . '&#x' . $v2['html_code'] .  ';</span>';
				}
			}
		$result.= '</div>';
		return $result;
	}
}