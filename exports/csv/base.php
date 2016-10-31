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
	 * This function will convert array into csv file
	 *
	 * @param array $data
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param boolean $as_array
	 * @return mixed
	 */
	public static function array_to_csv($data, $delimiter = ',', $enclosure = '"', $as_array = false) {
		$result = array();
		$outstream = fopen("php://temp", 'r+');
		$sheet_counter = 0;
		foreach ($data as $sheet_name => $sheet_data) {
			if ($sheet_counter > 0) {
				fputcsv($outstream, array('(Begin)', $sheet_name), $delimiter, $enclosure);
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
		if (!$as_array) {
			return implode('', $result);
		} else {
			return $result;
		}
	}

	/**
	 * Read content from csv file into array
	 *
	 * @param string $filename
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param int $max_records
	 * @return array
	 */
	public static function csv_to_array($filename, $delimiter = ',', $enclosure = '"') {
		$temp = false;
		if (($handle = fopen($filename, 'r'))!==false) {
			while (($data = fgetcsv($handle, 0, $delimiter, $enclosure))!==false) {
				$temp[] = $data;
			}
			fclose($handle);
		}
		$data = array();
		$data_index = 'main';
		if (!empty($temp)) {
			foreach ($temp as $k=>$v) {
				if (stripos($v[0], '(Begin)')!==false) {
					$data_index = $v[1];
					continue;
				}
				$data[$data_index][] = $v;
			}
		}
		return $data;
	}
}