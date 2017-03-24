<?php

class numbers_frontend_system_controller_dev extends object_controller {

	/**
	 * A list of available topics wuld be here
	 *
	 * @var array
	 */
	public static $topics = [
		/*
		'frontend' => [
			'name' => 'Frontend Framework',
			'href' => '/numbers/frontend/system/controller/dev/_frontend'
		],
		'form_editor' => [
			'name' => 'Form Editor',
			'href' => '/numbers/frontend/assemblies/form/controller/editor/_edit'
		],
		*/
		'names' => [
			'name' => 'Naming Conventions',
			'href' => '/numbers/frontend/system/controller/dev/_names',
			'options' => [
				'code' => ['name' => 'Code', 'href' => '/numbers/frontend/system/controller/dev/_names#code'],
				'code_test' => ['name' => 'Code Test Name', 'href' => '/numbers/frontend/system/controller/dev/_names#code_test'],
				'db' => ['name' => 'Database', 'href' => '/numbers/frontend/system/controller/dev/_names#db'],
				'db_test' => ['name' => 'Database Test Name', 'href' => '/numbers/frontend/system/controller/dev/_names#db_test']
			]
		]
	];

	/**
	 * Render legend
	 *
	 * @param string $topic
	 */
	public static function render_topic($topic = null) {
		if (empty($topic)) {
			$data = self::$topics;
		} else {
			$data = [$topic => self::$topics[$topic]];
		}
		$temp = [];
		foreach ($data as $k => $v) {
			if (isset($v['options'])) {
				$value = Html::a(['href' => $v['href'], 'value' => $v['name']]);
				$temp2 = [];
				foreach ($v['options'] as $k2 => $v2) {
					$temp2[] = Html::a(['href' => $v2['href'], 'value' => $v2['name']]);
				}
				$value.= Html::ul(['options' => $temp2]);
				$temp[] = $value;
			} else {
				$temp[] = Html::a(['href' => $v['href'], 'value' => $v['name']]);
			}
		}
		echo Html::ul(['options' => $temp]);
	}

	/**
	 * Index action
	 */
	public function action_index() {
		// rendering
		self::render_topic();
	}

	/**
	 * Frontend action
	 */
	public function action_frontend() {
		$input = request::input();

		// legend
		echo self::render_topic('frontend');

		// processing submit
		$input['name'] = $input['name'] ?? 'numbers.frontend.html.class.base';
		$frontend_frameworks = [
			'numbers.frontend.html.class.base' => ['name' => 'Plain'],
			'numbers.frontend.html.semanticui.base' => ['name' => 'Semantic UI'],
			'numbers.frontend.html.bootstrap.base' => ['name' => 'Bootstrap']
		];
		if (!empty($input['submit_yes'])) {
			$settings = [];
			$libraries = [];
			if ($input['name'] == 'numbers.frontend.html.class.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 16
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = false;
				$libraries['bootstrap']['autoconnect'] = false;
			} else if ($input['name'] == 'numbers.frontend.html.semanticui.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 16
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = true;
				$libraries['bootstrap']['autoconnect'] = false;
			} else if ($input['name'] == 'numbers.frontend.html.bootstrap.base') {
				$settings = [
					'submodule' => $input['name'],
					'options' => [
						'grid_columns' => 12
					],
					'calendar' => [
						'submodule' => 'numbers.frontend.components.calendar.numbers.base'
					]
				];
				$libraries['semanticui']['autoconnect'] = false;
				$libraries['bootstrap']['autoconnect'] = true;
			}
			// we need to merge old and new values
			Session::set('numbers.flag.global.html', array_merge_hard(Session::get('numbers.flag.global.html'), $settings));
			Session::set('numbers.flag.global.library', array_merge_hard(Session::get('numbers.flag.global.library'), $libraries));
			header('Location: /numbers/frontend/system/controller/dev/~frontend?name=' . $input['name']);
			exit;
		}

		// form
		$ms = 'Name: ' . Html::select([
			'name' => 'name',
			'options' => $frontend_frameworks,
			'no_choose' => true,
			'value' => $input['name']
		]) . ' ';
		$ms.= Html::submit(['name' => 'submit_yes']);
		echo Html::form(['name' => 'db', 'action' => '#db_test', 'value' => $ms]);
	}

	/**
	 * Names action
	 */
	public function action_names() {
		$input = request::input();

		// legend
		echo self::render_topic('names');

		// code naming conventions
		echo Html::a(['name' => 'code']);
		echo '<h3>Naming Conventions: Code</h3>';
		echo object_name_code::explain(null, ['html' => true]);

		// testing form
		echo Html::a(['name' => 'code_test']);
		echo '<h3>Test name</h3>';
		if (!empty($input['submit_yes'])) {
			$result = object_name_code::check($input['type'] ?? null, $input['name'] ?? null);
			if (!$result['success']) {
				echo Html::message(['options' => $result['error'], 'type' => 'danger']);
			} else {
				echo Html::message(['options' => 'Name is good!', 'type' => 'success']);
			}
		}
		$ms = 'Name: ' . Html::input(['name' => 'name', 'value' => $input['name'] ?? null]) . ' ';
		$ms.= 'Type: ' . Html::select(['name' => 'type', 'options' => object_name_code::$types, 'value' => $input['type'] ?? null]) . ' ';
		$ms.= Html::submit(['name' => 'submit_yes']);
		echo Html::form(['name' => 'code', 'action' => '#code_test', 'value' => $ms]);

		// database naming convention
		echo '<br/><br/><hr/>';
		echo Html::a(['name' => 'db']);
		echo '<h3>Naming Conventions: Database</h3>';
		echo object_name_db::explain(null, ['html' => true]);

		// testing form
		echo Html::a(['name' => 'db_test']);
		echo '<h3>Test name</h3>';
		if (!empty($input['submit_yes2'])) {
			$result = object_name_db::check($input['type2'] ?? null, $input['name2'] ?? null);
			if (!$result['success']) {
				echo Html::message(['options' => $result['error'], 'type' => 'danger']);
			} else {
				echo Html::message(['options' => 'Name is good!', 'type' => 'success']);
			}
		}
		$ms = 'Name: ' . Html::input(['name' => 'name2', 'value' => $input['name2'] ?? null]) . ' ';
		$ms.= 'Type: ' . Html::select(['name' => 'type2', 'options' => object_name_db::$types, 'value' => $input['type2'] ?? null]) . ' ';
		$ms.= Html::submit(['name' => 'submit_yes2']);
		echo Html::form(['name' => 'db', 'action' => '#db_test', 'value' => $ms]);
	}
}