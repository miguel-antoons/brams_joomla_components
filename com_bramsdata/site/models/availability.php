<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ItemModel;

/**
 * Availability Model
 *
 * @since  0.0.1
 */
class BramsDataModelAvailability extends ItemModel {
	/**
	 * @var string message
	 */
	protected $message;

	private function connectToDatabase() {
		/* Below lines are for connecting to production database later on */
		// $database_options = array();

		// $database_options['driver'] = $_ENV['DB_DRIVER'];
		// $database_options['host'] = $_ENV['DB_HOST'];
		// $database_options['user'] = $_ENV['DB_USER'];
		// $database_options['password'] = $_ENV['DB_PASSWORD'];
		// $database_options['database'] = $_ENV['DB_NAME'];
		// $database_options['prefix'] = $_ENV['DB_PREFIX'];

		// return JDatabaseDriver::getInstance($database_options);

		// below line is for connecting to default joomla database
		return JFactory::getDbo();
	}

	/**
	 * Get the message
     *
	 * @return  string  The message to be displayed to the user
	 */
	public function getMsg() {
		if (!isset($this->message))
		{
			$this->message = 'Hello World!';
		}

		return $this->message;
	}

	// get all the stations from the external brams database
	public function getStations() {
		$db = $this->connectToDatabase();
		$db = JFactory::getDbo();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
		$system_query->select(
			$db->quoteName('system.id') . ', '
			. $db->quoteName('system.name') . ', '
			. $db->quoteName('transfer_type') . ', '
			. $db->quoteName('status')
			);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id'));

		$db->setQuery($system_query);

		return $db->loadObjectList();
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

	// get all the file information between 2 dates
	public function getAvailability($start_date, $end_date, $selected_stations) {
		$start_datetime = $this->string_to_datetime($start_date);
		$end_datetime = $this->string_to_datetime($end_date);

		$start = new DateTime($start_datetime);
		$time_difference = $start->diff(new DateTime($end_datetime));

		if ($time_difference->days > 14) {
			echo 'debug';
		}
		else {
			$db_function_to_use = array($this, 'getAvailabilityDB');
			$function_to_use = array($this, 'get_precise_file_availability');
			$start_to_use = $start_datetime;
		}

		// contains all the raw availability information coming from the database
		$db_availability = $db_function_to_use($start_date, $end_date, $selected_stations);
		$final_availability_array = array();			// array will contain all the final availability info

		// create a new array that contains the data grouped per station
		foreach ($selected_stations as $station) {
			$flag = -1;								// flag indicates if last addition to '$final_availability_array' was available (flag = 0) or unavailable (flag = 1)
			$expected_start = $start_to_use;		// set the initial expected start
			
			// filter the array coming from the database in order to keep the info
			// from the station stored in the '$station' variable
			$specific_station_availability = array_filter(
				$db_availability,
				function($availability_info) use ($station) {
					return $availability_info->system_id === $station;
				}
			);

			$function_to_use($specific_station_availability, $final_availability_array, $expected_start, $station);
		}

		$last_object = new stdClass();									// create a new object
		$last_object->start = $end_datetime;							// add the end date as DateTime object to the newly created object
		array_push($final_availability_array[$station], $last_object);	// add the newly created object to the final array

		return $final_availability_array;
	}

	private function get_precise_file_availability($specific_station_availability, &$final_availability_array, $expected_start, $station) {
		// iterate over the array containing all the availability info of one specific station
		for ($index = 0 ; $index < count($specific_station_availability) ; $index++) {
			$end_time = new DateTime($specific_station_availability[$index]->start);	// convert the start time to a DateTime object
			$end_time->add(new DateInterval('PT5M'));									// add 5 min to the start time -> becomes the end time

			// if the effective start time and the expected start time do not match
			// or if the effective start time and the expected start time match and the previous
			// object added to the array has availability set to 0
			if ($specific_station_availability[$index]->start !== $expected_start || $flag) {
				$this->add_availability_info($final_availability_array, $expected_start, $station);
			}

			// update the expected start time with the next expected value
			$expected_start = $end_time->format('Y-m-d H:i:s');
		}
	}

	private function get_unprecise_file_availability($specific_station_availability, &$final_availability_array, $expected_start, $station) {
		// iterate over the array containing all the availability info of one specific station
		for ($index = 0 ; $index < count($specific_station_availability) ; $index++) {
			$availability_info = &$specific_station_availability[$index];

			if ($availability_info->rate === 0 && $temp_available !== 1) {
				$temp_available = 1;
			}
			elseif ($availability_info->rate === 1000 && $temp_available !== 2){
				$temp_available = 2;
			}
			elseif ($availability_info->rate <= 200 && $temp_available !== 3){
				$temp_available = 3;
			}
			elseif ($availability_info->rate <= 400 && $temp_available !== 4){
				$temp_available = 4;
			}
			elseif ($availability_info->rate <= 600 && $temp_available !== 5){
				$temp_available = 5;
			}
			elseif ($availability_info->rate <= 800 && $temp_available !== 6){
				$temp_available = 6;
			}
			elseif ($availability_info->rate <= 1000 && $temp_available !== 7){
				$temp_available = 7;
			}

			$temp_object->start = $expected_start;
			$temp_object->available = $temp_available;
			$final_availability_array[$station][] = $temp_object;
		}
	}

	// add availability info to the availability array
	private function add_availability_info(&$array, $expected_start, $station) {
		// create an object stating that the files following the expected start date are available
		$temp_object->start = $expected_start;

		// set availability according to the flag
		if ($temp_object->available) {
			$temp_object = new stdClass();
			$temp_object->available = 0;
		}
		else {
			$temp_object = new stdClass();
			$temp_object->available = 1;
		}

		// add that object to the final availability array
		$array[$station][] = $temp_object;
	}

	private function string_to_datetime($string_to_convert) {
		$temp_datetime = new DateTime($string_to_convert);
		return $temp_datetime->format('Y-m-d H:i:s');
	}

	// get file availability from database
	private function getAvailabilityDB($start_date, $end_date, $selected_stations) {
		$db = $this->connectToDatabase();			// create a database connection
		$availability_query = $db->getQuery(true);

		// generate a database query
		$availability_query->select($db->quoteName('system_id') . ', ' . $db->quoteName('start'));
		$availability_query->from($db->quoteName('file'));
		$availability_query->where($db->quoteName('start') . ' >= convert(' . $db->quote($start_date) . ', DATETIME)');
		$availability_query->where($db->quoteName('start') . ' < convert(' . $db->quote($end_date) . ', DATETIME)');
		$availability_query->where($db->quoteName('system_id') . ' in (' . implode(', ', $selected_stations) . ')');

		// execute the previously generated query
		$db->setQuery($availability_query);

		// return the data received from the database
		return $db->loadObjectList();
	}
}
