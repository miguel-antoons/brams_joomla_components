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
		// contains all the raw availability information coming from the database
		$db_availability = $this->getAvailabilityDB($start_date, $end_date, $selected_stations);
		$final_availability_array = array();			// array will contain all the final availability info

		//debug
		//print_r($db_availability);
		//print_r($selected_stations);

		// create a new array that contains the data grouped per station
		foreach ($selected_stations as $station) {
			$flag = true;													// flag indicates if last addition to '$final_availability_array' was available (flag = 0) or unavailable (flag = 1)
			$expected_start = new DateTime($start_date);					// convert the start date to a DateTime object
			$expected_start = $expected_start->format('Y-m-d H:i:s');		// convert the sart date DateTime object to a string date
			// $objects_added = 0;											// counts the number of elements added to the availability array

			// filter the array coming from the database in order to keep the info
			// from the station stored in the '$station' variable
			$specific_station_availability = array_filter(
				$db_availability,
				function($availability_info) {
					echo 'station : ' . $station;
					print_r($availability_info);
					echo $availability_info->system_id === $station;
					echo '<br>';
					return $availability_info->system_id === $station;
				}
			);

			// debug
			print_r($specific_station_availability);

			$availability_length = count($specific_station_availability);

			// iterate over the array containing all the availability info of one specific station
			for ($index = 0 ; $index < $availability_length ; $index++) {
				// $db_availability[$index + $objects_added]->available = 1;
				$end_time = new DateTime($specific_station_availability[$index]->start);	// convert the start time to a DateTime object
				$end_time->add(new DateInterval('PT5M'));									// add 5 min to the start time -> becomes the end time
	
				// if the effective start time and the expected start time do not match
				if ($specific_station_availability[$index]->start !== $expected_start) {
					// create an object stating that the files following the expected start date are missing
					$temp_object = new stdClass();
					$temp_object->start = $expected_start;
					$temp_object->available = 0;

					// add that object to the final availability array
					$final_availability_array[$station][] = clone $temp_object;

					// set the flag to true indicating that the last element added to the array has availability set to zero
					$flag = true;
	
					// $db_availability = array_merge(
					// 	array_slice($db_availability, 0, ($index + $objects_added)), 
					// 	array($temp_object), 
					// 	array_slice($db_availability, ($index + $objects_added))
					// );
	
					// $objects_added++;
				}
				// if the effective start time and the expected start time match and the previous
				// object added to the array has availability set to 0
				elseif ($flag) {
					// create an object stating that the files following the expected start date are available
					$temp_object = new stdClass();
					$temp_object->start = $expected_start;
					$temp_object->available = 1;

					// add that object to the final availability array
					$final_availability_array[$station][] = clone $temp_object;

					// set the flag to false indicating that the last element added to the array has availability set to one
					$flag = false;
				}
	
				// update the expected start time with the next expected value
				$expected_start = $end_time->format('Y-m-d H:i:s');
			}
	
			$end_datetime = new DateTime($end_date);						// convert the end date to a DateTime object
			$last_object = new stdClass();									// create a new object
			$last_object->start = $end_datetime->format('Y-m-d H:i:s');		// add the end date DateTime object to the newly created object
			array_push($final_availability_array[$station], $last_object);	// add the newly created object to the final array
		}

		return $final_availability_array;
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

		// debug
		echo $availability_query;

		// execute the previously generated query
		$db->setQuery($availability_query);

		// return the data received from the database
		return $db->loadObjectList();
	}
}
