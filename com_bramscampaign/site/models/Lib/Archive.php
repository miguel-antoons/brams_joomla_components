<?php
class Archive {
	const DATE_TIME_FORMAT = 'Y-m-d\TH:i';
	const DATE_TIME_FILENAME_FORMAT = 'Ymd\_Hi';

	private $_cache;
	private $_cacheURL;
	private $_cachePath;
	private $_proxy = 'https://brams-data.aeronomie.be/bramsProxy.php';

	public function __construct($cache=true) {
		$this->_cache = $cache;
		$this->_cacheURL = '/img/cache/';
		$this->_cachePath = JPATH_ROOT . '/ProjectDir '. DS . 'img' . DS . 'cache' . DS;

		date_default_timezone_set('UTC');
	}

	public function test($params=array()) {
		$query= '';
		foreach($params as $key => $value) {
			if($value !== null) {
				if(is_array($value)) {
					foreach($value as $v) {
						$query .= $key.'='.urlencode($v).'&';
					}
				} else {
					$query .= $key.'='.urlencode($value).'&';
				}
			}
		}

		$url = $this->_proxy . '?' . $query . 'SENDBY=' .urlencode($_SERVER['SERVER_NAME']);
		return @file_get_contents($url);
	}

	public function get($params=array()) {
		$query = '';
		foreach($params as $key => $value) {
			if ($value !== null) $query .= $key.'='.urlencode($value).'&';
		}
		$url = $this->_proxy.'?'.$query.'SENDBY='.urlencode($_SERVER['SERVER_NAME']);
		error_log($url);

		return @file_get_contents($url);
	}

	public function getSpectrogram($dateTime, $system_code, $options = array()) {
		$filename = 'RAD_BEDOUR_'.$dateTime->format(Archive::DATE_TIME_FILENAME_FORMAT).'_'.$system_code;
		$len = strlen($filename);

		if (isset($options['raw']))         $filename .= '_R';
		if (isset($options['fft']))         $filename .= '_'.$options['fft'];
		if (isset($options['overlap']))     $filename .= '_'.$options['overlap'];
		if (isset($options['color_min']))   $filename .= '_'.$options['color_min'];
		if (isset($options['color_max']))   $filename .= '_'.$options['color_max'];

		$filename .= '.png';
		$len = $len - strlen($filename);

		$path = $this->_cachePath.$filename;
		$url = $this->_cacheURL.$filename;

		$begin = $dateTime->format(Archive::DATE_TIME_FORMAT);
		$dateTime->modify('+5 minute');
		$end = $dateTime->format(Archive::DATE_TIME_FORMAT);

		if (!$this->_cache || !file_exists($path)) {
			$params = array(
				'task'      => 'makeSpectrograms',
				'begin'     => $begin,
				'end'       => $end,
				'station'   => $system_code
			);

			$json = $this->get(array_merge($params, $options));
			if (!$json) {
				return false;
			}

			$spect = json_decode($json, true);
			if ($spect === null) return false;

			$freq_min = $spect[0]['frequency_min'];
			$freq_max = $spect[0]['frequency_max'];

			if (!($str = $this->get(array('task' => 'showImage', 'image' => substr($filename, 0, $len))))) {
				return false;
			}

			if (!@file_put_contents($path, $str, LOCK_EX)) {
				return false;
			}
		} else {
			$freq_min = null;
			$freq_max = null;
		}

		list($width, $height) = getimagesize($path);

		return array(
			'url'           => $url,
			'width'         => $width,
            'height'        => $height,
            'frequency_min' => $freq_min,
            'frequency_max' => $freq_max
		);
	}
}
