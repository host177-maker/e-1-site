<?php
	function anum ($start) {
		$next = array('0' => '1', '1' => '2', '2' => '3', '3' => '4', '4' => '5', '5' => '6', '6' => '7', '7' => '8', '8' => '9', '9' => 'A', 'A' => 'B', 'B' => 'C', 'C' => 'D', 'D' => 'E', 'E' => 'F', 'F' => 'G', 'G' => 'H', 'H' => 'I', 'I' => 'J', 'J' => 'K', 'K' => 'L', 'L' => 'M', 'M' => 'N', 'N' => 'O', 'O' => 'P', 'P' => 'Q', 'Q' => 'R', 'R' => 'S', 'S' => 'T', 'T' => 'U', 'U' => 'V', 'V' => 'W', 'W' => 'X', 'X' => 'Y', 'Y' => 'Z', 'Z' => '0');

		$arr = str_split($start);
		if ($arr[5] == 'Z') {
			if ($arr[4] == 'Z') {
				if ($arr[3] == 'Z') {
					if ($arr[2] == 'Z') {
						if ($arr[1] == 'Z') {
							if ($arr[0] == 'Z') {
								die('ЁКЛМН!!! Количества кодов, при расходе 14000 в месяц, должно было хватить на 12957 лет!!! КА-А-А-АК?!');
							}
							$arr[0] = $next[$arr[0]];
						}
						$arr[1] = $next[$arr[1]];
					}
					$arr[2] = $next[$arr[2]];
				}
				$arr[3] = $next[$arr[3]];
			}
			$arr[4] = $next[$arr[4]];
		}
		$arr[5] = $next[$arr[5]];
		return implode('', $arr);
	}

	function get_scrath_code ($first_code) {
		$code = md5($first_code . 'Воеводина');

		$code = $first_code . strtoupper(substr($code, 0, 6));

		return implode('-', str_split($code, 3));
	}

	function getStart () {
		return readData('anum.php');
	}

	function readData ($fileName) {
		if (!file_exists($fileName)) {
			return array('error' => true, 'data' => 'Файл автонумерации не найден.');
		}

		$fp = fopen($fileName, 'rt');
		if ($fp) {
			while (!feof($fp)) {
				$text = fgets($fp, 1024);
			}
		}
		fclose($fp);

		if (isset($text)) {
			return array('error' => false, 'data' => strtoupper($text));
		} else {
			return array('error' => true, 'data' => 'Ошибка при открытии файла.');
		}
	}

	function writeData ($fp, $str) {
		$res = fwrite($fp, $str);

		if ($res) {
			return array('error' => false);
		} else {
			return array('error' => true, 'data' => 'Ошибка при записи в файл.');
		}
	}
