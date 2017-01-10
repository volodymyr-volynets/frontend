<?php

class numbers_frontend_exports_csv_base {

	/**
	 * Formats
	 *
	 * @var array
	 */
	public static $formats = [
		'csv' => ['name' => 'CSV (Comma Delimited)', 'delimiter' => ',', 'enclosure' => '"', 'file_extension' => 'csv', 'content_type' => 'application/octet-stream'],
		'txt' => ['name' => 'Text (Tab Delimited)', 'delimiter' => "\t", 'enclosure' => '"', 'file_extension' => 'txt', 'content_type' => 'application/octet-stream']
	];

	/**
	 * Export
	 *
	 * @param array $data
	 * @param array $options
	 * 		string delimiter
	 * 		string enclosure
	 * 		boolean as_array
	 * @return mixed
	 */
	public static function export($data, $options = []) {
		$result = array();
		$outstream = fopen("php://temp", 'r+');
		$sheet_counter = 0;
		foreach ($data as $sheet_name => $sheet_data) {
			if ($sheet_counter > 0) {
				fputcsv($outstream, array('(Begin)', $sheet_name), $options['delimiter'] ?? ',', $options['enclosure'] ?? '"');
			}
			foreach ($sheet_data as $k => $v) {
				fputcsv($outstream, $v, $delimiter, $enclosure);
			}
			$sheet_counter++;
		}
		rewind($outstream);
		while (!feof($outstream)) {
			$result[] = fgets($outstream);
		}
		fclose($outstream);
		// return
		if (empty($options['as_array'])) {
			return implode('', $result);
		} else {
			return $result;
		}
	}

	/**
	 * Import
	 *
	 * @param string $filename
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param int $max_records
	 * @return array
	 */
	public static function import($filename, $delimiter = ',', $enclosure = '"') {
		$temp = false;
		if (($handle = fopen($filename, 'r')) !== false) {
			while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
				$temp[] = $data;
			}
			fclose($handle);
		}
		$data = array();
		$data_index = 'main';
		if (!empty($temp)) {
			foreach ($temp as $k => $v) {
				if (stripos($v[0], '(Begin)') !== false) {
					$data_index = $v[1];
					continue;
				}
				$data[$data_index][] = $v;
			}
		}
		return $data;
	}

}
