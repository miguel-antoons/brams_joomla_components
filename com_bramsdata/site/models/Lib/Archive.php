<?php

use Joomla\CMS\Log\Log;

/**
 * Class contains all functions to get
 * spectrograms frm a specific proxy.
 *
 * @package     ${NAMESPACE}
 *
 * @since       0.2.0
 */
class Archive {
	const DATE_TIME_FORMAT = 'Y-m-d\TH:i';
	const DATE_TIME_FILENAME_FORMAT = 'Ymd\_Hi';

	private $_cache;
	private $_cacheURL;
	private $_cachePath;
	private $_proxy = 'https://brams-data.aeronomie.be/bramsProxy.php';

	/**
	 * @param $cache boolean if set to false, a new image will be created
	 *
	 * @since 0.2.0
	 */
	public function __construct($cache = true) {
		$this->_cache = $cache;
		$this->_cacheURL = '/img/cache/';   // url to the cache directory
		$this->_cachePath =                 // path of the cache directory
			JPATH_ROOT      .DIRECTORY_SEPARATOR
			.'ProjectDir'   .DIRECTORY_SEPARATOR
			.'img'          .DIRECTORY_SEPARATOR
			.'cache'        .DIRECTORY_SEPARATOR;

		date_default_timezone_set('UTC');
	}

	// test function
	// ! do not use in production
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

	/**
	 * Function executes a request to the internal proxy. If everything went well
	 * it returns the request result.
	 *
	 * @param $params array array of key-values, these key and values will be part of
	 *                      the url to get the spectrogram images wia the proxy.
	 *
	 * @return false|string false on fail, request results on success
	 *
	 * @since 0.2.0
	 */
	public function get($params = array()) {
		$query = '';

		// add url arguments contained in the $params array
		foreach($params as $key => $value) {
			if ($value !== null) $query .= $key.'='.urlencode($value).'&';
		}

		// create the complete request url
		$url = $this->_proxy.'?'.$query.'SENDBY='.urlencode($_SERVER['SERVER_NAME']);
		error_log($url);

		return @file_get_contents($url);
	}

	/**
	 * Function verifies if the asked spectrogram picture exists. If it exists,
	 * it simply returns the spectrogram info (url, filename, ...).
	 * If it doesn't exist, it requests the image to the internal proxy and stores
	 * it in the cache directory.
	 * In both cases it returns the image information.
	 *
	 * @param $dateTime     datetime    date and time of the requested spectrogram image
	 * @param $system_code  string      station name and antenna number of the requested spectrogram
	 * @param $options      array       additional options for the spectrogram
	 *
	 * @return array|false  false on fail, the array with requested spectrogram info on success
	 *
	 * @since 0.2.0
	 */
	public function getSpectrogram($dateTime, $system_code, $options = array()) {
		// generate the string filename
		$filename = 'RAD_BEDOUR_'.$dateTime->format(Archive::DATE_TIME_FILENAME_FORMAT).'_'.$system_code;
		$len = strlen($filename);

		// adapt the filename according to the set options
		if (isset($options['raw']))         $filename .= '_R';
		if (isset($options['fft']))         $filename .= '_'.$options['fft'];
		if (isset($options['overlap']))     $filename .= '_'.$options['overlap'];
		if (isset($options['color_min']))   $filename .= '_'.$options['color_min'];
		if (isset($options['color_max']))   $filename .= '_'.$options['color_max'];

		$filename .= '.png';
		$len = $len - strlen($filename);

		// set the file path and url
		$path   = $this->_cachePath.$filename;
		$url    = $this->_cacheURL.$filename;

		// set the start and end date of the requested spectrogram image (interval of 5 minutes)
		$begin = $dateTime->format(Archive::DATE_TIME_FORMAT);
		$dateTime->modify('+5 minute');
		$end = $dateTime->format(Archive::DATE_TIME_FORMAT);

		// if the image doesn't exist in the cache directory or if the cache flag is set to false
		if (!$this->_cache || !file_exists($path)) {
			// set the request url arguments
			$params = array(
				'task'      => 'makeSpectrograms',
				'begin'     => $begin,
				'end'       => $end,
				'station'   => $system_code,
				'raw'       => $options['raw'],
				'fft'       => $options['fft'],
				'overlap'   => $options['overlap'],
				'color_min' => $options['color_min'],
				'color_max' => $options['color_max']
			);

			// get info about the spectrogram image trough the internal proxy
			$json = $this->get($params);
			if (!$json) return false;

			$spectrogram = json_decode($json, true);
			if ($spectrogram === null) return false;

			$freq_min = $spectrogram[0]['frequency_min'];
			$freq_max = $spectrogram[0]['frequency_max'];

			// try to get the image itself, return false on fail
			if (!($str = $this->get(array('task' => 'showImage', 'image' => substr($filename, 0, $len))))) {
				return false;
			}

			// some error handling
			if (!is_dir($this->_cachePath)) Log::add('CACHE PATH IS NOT A DIRECTORY', Log::ERROR, 'error');
			else if (!is_writable($this->_cachePath)) Log::add('CACHE DIRECTORY IS NOT WRITABLE', Log::ERROR, 'error');

			// store the image in the cache directory
			if (!@file_put_contents($path, $str, LOCK_EX)) {
				return false;
			}
		} else {
			$freq_min = null;
			$freq_max = null;
		}

		// get the image dimensions
		list($width, $height) = getimagesize($path);

		// return the image information
		return array(
			'url'           => $url,
			'filename'      => $filename,
			'width'         => $width,
			'height'        => $height,
			'frequency_min' => $freq_min,
			'frequency_max' => $freq_max
		);
	}
}
