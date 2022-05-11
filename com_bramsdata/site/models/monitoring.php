<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Monitoring Model
 *
 * Gets data from the database linked to the file availability of the
 * BRAMS receiving stations.
 *
 * @since  0.4.0
 */
class BramsDataModelMonitoring extends BaseDatabaseModel {
	// function connects to the database and returns the database object
	private function connectToDatabase() {
		try {
			/* Below lines are for connecting to production database later on */
			$database_options = getDatabaseInfo();
			return JDatabaseDriver::getInstance($database_options);

			/*
			below line is for connecting to default joomla database
			WARNING : this line should be commented/removed for production
			*/
			// return $this->getDbo();
		} catch (Exception $e) {
			// if an error occurs, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return false;
		}
	}

	/**
	 * Function gets teh following data for all the available stations in the database :
	 * (system.id, system.name, location.transfer_type, location.status, ''). If everything
	 * goes well, it returns all that data.
	 *
	 * @return int|array -1 if an error occurs, the array with all the results if everything wen well.
	 * @since 0.0.2
	 */
	public function getStations() {
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$system_query = $db->getQuery(true);

		// SQL query to get all information about the multiple systems
		$system_query->select(
			$db->quoteName('system.id')         . ', '
			. $db->quoteName('system.name')     . ', '
			. $db->quoteName('transfer_type')   . ', '
			. $db->quoteName('status')          . ', '
			. $db->quote('') . 'as checked'
		);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('location'));
		$system_query->where(
			$db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id')
		);
		$system_query->order($db->quoteName('system.name'). ' ASC');

		$db->setQuery($system_query);

		// try to execute the query and return the result
		try {
			return $db->loadObjectList();
		} catch (RuntimeException $e) {
			// if it fails, log the error and return false
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	public function getPSD() {
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$psd_query = $db->getQuery(true);

		// SQL query to get all the non null psd values for given stations
		// and in between a certain time range
		$psd_query->select(
			$db->quoteName('system_id') . ', '
			. $db->quoteName('psd')
		);
		$psd_query->from($db->quoteName('file'));
		// ! TODO
		$psd_query->where();
	}

	// get today's date in yyy-mm-dd format
	public function getToday() {
		return date('Y-m-d');
	}

	// get the date from 5 days ago in yyy-mm-dd format
	public function getStartDate() {
		return date('Y-m-d', strtotime("-5 days"));
	}

	// get yesterday's date in yyy-mm-dd format
	public function getYesterday() {
		return date('Y-m-d', strtotime("-1 days"));
	}

	/**
	 * Function takes a string time and adds a certain amount of time to it. It then returns
	 * a string datetime with the added time.
	 *
	 * @param $string_date      string  the initial string date
	 * @param $format           string  the format in which the returned time has to be
	 * @param $string_interval  string  the time to add / subtract
	 * @param $invert           boolean indicates if time has to be added or subtracted
	 *
	 * @return void|string void on fail, string date on success
	 *
	 * @since 0.0.2
	 */
	private function add_time_to_string($string_date, $format='Y-m-d H:i:s', $string_interval='PT5M', $invert=0) {
		try {
			$final_date = new DateTime($string_date);
		} catch (Exception $e) {
			Log::add($e, Log::ERROR, 'error');
			return;
		}
		try {
			$interval_to_add = new DateInterval($string_interval);
		} catch (Exception $e) {
			Log::add($e, Log::ERROR, 'error');
			return;
		}

		$interval_to_add->invert = $invert;
		$final_date->add($interval_to_add);
		return $final_date->format($format);
	}
}
