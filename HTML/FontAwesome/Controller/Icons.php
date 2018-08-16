<?php

namespace Numbers\Frontend\HTML\FontAwesome\Controller;
class Icons extends \Object\Controller {

	public $title = 'Icons';

	public function actionIndex() {
		$data = \Numbers\Frontend\HTML\FontAwesome\Model\Icons::getStatic();
		foreach ($data as $k => $v) {
			echo '<i class="' . $k . '"> ' . $v['name'] . '</i>';
			echo '<hr/>';
		}
	}
}