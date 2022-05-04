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
 * Availability Model
 *
 * Gets data from the database linked to the file availability of the
 * BRAMS receiving stations.
 *
 * @since  0.0.1
 */
class BramsDataModelAvailability extends BaseDatabaseModel {
    // below contains all the availability categories
    protected $custom_categories_array = array(
        '0%', '100%', '0.1 - 20%', '20.1 - 40%', '40.1 - 60%', '60.1 - 80%', '80.1 - 99.9%'
    );

    // function connects to the database and returns the database object
    private function connectToDatabase() {
        try {
            /* Below lines are for connecting to production database later on */
            // $database_options = array();

            // $database_options['driver'] = $_ENV['DB_DRIVER'];
            // $database_options['host'] = $_ENV['DB_HOST'];
            // $database_options['user'] = $_ENV['DB_USER'];
            // $database_options['password'] = $_ENV['DB_PASSWORD'];
            // $database_options['database'] = $_ENV['DB_NAME'];
            // $database_options['prefix'] = $_ENV['DB_PREFIX'];

            // return JDatabaseDriver::getInstance($database_options);

            /*
            below line is for connecting to default joomla database
            WARNING : this line should be commented/removed for production
            */
            return $this->getDbo();
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
     * Entry-point of the availability feature. This function checks how much time
     * there is in between the $start_date and the $end_date and calls the according
     * functions.
     *
     * @param   string  $start_date         Start date of the availability to be seen
     * @param   string  $end_date           End date of the availability to be seen
     * @param   array   $selected_stations  Array with the selected stations
     * @param   int     $time_interval      Time interval of the availability chart
     *
     * @return  int|array   -1 if a failure happened, array with all the availability information
     *                      of $selected_stations from $start_date to $end_date
     *
     * @since 0.0.2
     */
    public function getAvailability($start_date, $end_date, $selected_stations, &$time_interval) {
        // convert the string date to a DateTime object
        try {
            $start_date = new DateTime($start_date);
        } catch (Exception $e) {
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
        try {
            $end_date = new DateTime($end_date);
        } catch (Exception $e) {
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
        $time_difference 	= $start_date->diff($end_date);				// get the time difference between $start_date and $end_date
        $start_date 		= $start_date->format('Y-m-d H:i:s');
        $end_date 			= $end_date->format('Y-m-d H:i:s');

        // if the time difference is greater than 14 days
        if ($time_difference->days > 14) {
            // set the $time_interval to 1 day (86400 seconds)
            $time_interval = 86400;
            // go get and return the availability information
            return $this->get_availability_general(
                array($this, 'getAvailabilityRateDB'),
                array($this, 'get_unprecise_file_availability'),
                $start_date,
                $end_date,
                $selected_stations
            );
        } else {
            // go get and return the availability information
            return $this->get_availability_general(
                array($this, 'getAvailabilityDB'),
                array($this, 'get_precise_file_availability'),
                $start_date,
                $end_date,
                $selected_stations
            );
        }
    }

    /**
     * Function gets all the data from the database and iterates over the selected
     * stations. On each iteration, it launches the function it received as argument
     * to structure the data and remove unnecessary data.
     *
     * @param array 	$db_function_to_use  	Database query that will be used for data retrieval
     * @param array 	$function_to_use 		Function to use in order to structure the final array
     * @param string 	$start_date				Start date of the availability to be seen
     * @param string 	$end_date				End date of the availability to be seen
     * @param array		$selected_stations	Array with the selected stations
     *
     * @return    int|array on fail returns -1, on success returns an array with all the availability
     *                      information of $selected_stations from $start_date to $end_date
     *
     * @since 0.0.2
     */
    private function get_availability_general(
        $db_function_to_use,
        $function_to_use,
        $start_date,
        $end_date,
        $selected_stations
    ) {
        // contains all the raw availability information coming from the database
        if (($db_availability = $db_function_to_use($start_date, $end_date, $selected_stations)) === -1) {
            return -1;
        }
        $final_availability_array = array();					// array will contain all the final availability info

        // create a new array that contains the data grouped per selected station
        foreach ($selected_stations as $station) {
            $expected_start = $start_date;		// set the initial expected start

            // filter the array coming from the database in order to keep the info
            // from the station stored in the '$station' variable
            $specific_station_availability = array_values(
                array_filter(
                    $db_availability,
                    function($availability_info) use ($station) {
                        return ((int) $availability_info->system_id) === ((int) $station);
                    }
                )
            );

            // launch the structure function
            $function_to_use(
                $specific_station_availability,
                $final_availability_array,
                $expected_start,
                $station,
                $end_date
            );

            $last_object                          = new stdClass();	// create a new object
            $last_object->start                   = $end_date;      // add the end date as DateTime object to the newly created object
            $final_availability_array[$station][] = $last_object;	// add the newly created object to the final array
        }

        return $final_availability_array;
    }

    /**
     * Get file availability per file. This is the precise method as we know for
     * each file if it is there or not.
     *
     * @param array $specific_station_availability	availability info of a specific station
     * @param array $final_availability_array		final array to perform the changes on
     * @param string	$expected_start					first expected start
     * @param int 	$station						id of the currently treated station
     *
     * @since 0.0.2
     */
    private function get_precise_file_availability(
        $specific_station_availability,
        &$final_availability_array,
        $expected_start,
        $station,
        $end_date
    ) {
        $flag = false;  // flag indicates if the previous added time was available (flag = false) or not (flag = true)
        $station_availability_length = count($specific_station_availability);

        if($station_availability_length) {
            // check a first time to set the correct flag value
            if ($specific_station_availability[0]->start === $expected_start) {
                $flag = true;
            }

            $this->add_availability_info($final_availability_array, $expected_start, $station, $flag);
            $expected_start = $this->add_time_to_string($specific_station_availability[0]->start);

            // iterate over the array containing all the availability info of one specific station
            for ($index = 1 ; $index < $station_availability_length ; $index++) {
                // add 5 min to the start time -> becomes the end time
                $end_time = $this->add_time_to_string($specific_station_availability[$index]->start);

                // if the effective start time and the expected start time do not match
                // or if the effective start time and the expected start time match and the previous
                // object added to the array has availability set to 0
                if ($specific_station_availability[$index]->start !== $expected_start || $flag) {
                    $this->add_availability_info($final_availability_array, $expected_start, $station, $flag);

                    if ($specific_station_availability[$index]->start !== $expected_start && !$flag) {
                        $this->add_availability_info($final_availability_array, $expected_start, $station, $flag);
                    }
                }

                // update the expected start time with the next expected value
                $expected_start = $end_time;
            }

            // following code is in case files were missing at the end
            $expected_start = $this->add_time_to_string($end_date, 'Y-m-d H:i:s', 'PT5M', 1);

            // if the last date found in the database data is not the expected date
            if ($specific_station_availability[$station_availability_length - 1]->start !== $expected_start) {
                // add an object to the final array indicating that files are missing at the end
                $flag = false;
                $this->add_availability_info(
                    $final_availability_array,
                    $this->add_time_to_string($specific_station_availability[$station_availability_length - 1]->start),
                    $station,
                    $flag
                );
            }
        } else {
            $this->add_availability_info($final_availability_array, $expected_start, $station, $flag);
        }
    }

    /**
     * Function add a new availability object to the array. This function works together with
     * the get_precise_file_availability function.
     *
     * @param array		$array				contains the final availability array
     * @param string 	$expected_start		contains the expected start, this is the date that will be added to the object
     * @param int 		$station 			contains the current station id
     * @param boolean	$flag				contains the flag value saying if the data is available or not
     *
     * @since 0.0.2
     */
    private function add_availability_info(&$array, $expected_start, $station, &$flag) {
        $temp_object = new stdClass();

        // set availability according to the flag
        if ($flag) {
            $temp_object->available = $this->custom_categories_array[1];
            if (array_key_exists($station, $array)) {
                $expected_start = $this->add_time_to_string($expected_start, 'Y-m-d H:i:s', 'PT5M', 1);
            }
        } else {
            $temp_object->available = $this->custom_categories_array[0];
        }

        // create an object stating that the files following the expected start date are available
        $temp_object->start = $expected_start;

        // add that object to the final availability array
        $array[$station][] = $temp_object;

        // toggle the flag
        $flag = !$flag;
    }

    /**
     * Get file availability rate per day. This is less precise as it is per day
     * and not per file. It indicates a rate in percentage instead of indicating the file
     * exists or not.
     *
     * @param array     $specific_station_availability	availability info of a specific station
     * @param array     $final_availability_array		final array to perform the changes on
     * @param string    $expected_start					first expected start
     * @param int 	    $station						id of the currently treated station
     *
     * @return int|void -1 if an error occurs, nothing if everything goes well
     *
     * @since 0.0.2
     */
    private function get_unprecise_file_availability(
        $specific_station_availability,
        &$final_availability_array,
        $expected_start,
        $station,
        $end_date
    ) {
        $previous_available = -1;	// indicates what was the last category inserted into the array
        $change = false;
        $station_availability_length = count($specific_station_availability);
        try {
            $expected_start = new DateTime($expected_start);
        } catch (Exception $e) {
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
        $expected_start = $expected_start->format('Y-m-d');

        if($station_availability_length) {
            // iterate over the array containing all the availability info of one specific station
            for ($index = 0 ; $index < $station_availability_length ; $index++) {
                $availability_info = &$specific_station_availability[$index];

                // if files are missing, set default value for that time (default value = no data found)
                if ($availability_info->date !== $expected_start) {
                    $temp_object 							= $this->change_category($change, $previous_available, 1);
                    $temp_object->start 					= $expected_start;
                    $final_availability_array[$station][]	= $temp_object;
                    $change = false;
                }

                // add an element ot the array according to the availability rate
                if (intval($availability_info->rate) === 0) {
                    $temp_object = $this->change_category($change, $previous_available, 1);
                } elseif (intval($availability_info->rate) === 1000) {
                    $temp_object = $this->change_category($change, $previous_available, 2);
                } elseif (intval($availability_info->rate) <= 200) {
                    $temp_object = $this->change_category($change, $previous_available, 3);
                } elseif (intval($availability_info->rate) <= 400) {
                    $temp_object = $this->change_category($change, $previous_available, 4);
                } elseif (intval($availability_info->rate) <= 600) {
                    $temp_object = $this->change_category($change, $previous_available, 5);
                } elseif (intval($availability_info->rate) <= 800) {
                    $temp_object = $this->change_category($change, $previous_available, 6);
                } elseif (intval($availability_info->rate) < 1000) {
                    $temp_object = $this->change_category($change, $previous_available, 7);
                }

                // if a change has been performed
                if ($change) {
                    // add the date from the previous iteration to the object
                    $temp_object->start = $availability_info->date;
                    // add the object to the final array
                    $final_availability_array[$station][] = $temp_object;
                    // set the $change flag to false
                    $change = false;
                }

                // update expected start
                $expected_start = $this->add_time_to_string($availability_info->date, 'Y-m-d', 'P1D');
            }

            // following code is in case files were missing at the end
            $expected_start = $this->add_time_to_string($end_date, 'Y-m-d', 'P1D', 1);

            // if the last date found in the database data is not the expected date
            if ($specific_station_availability[$station_availability_length - 1]->date !== $expected_start) {
                // add an object to the final array indicating that files are missing at the end
                $temp_object 		= $this->change_category($change, $previous_available, 1);
                $temp_object->start = $this->add_time_to_string(
                    $specific_station_availability[$station_availability_length - 1]->date, 'Y-m-d', 'P1D'
                );
                $final_availability_array[$station][] = $temp_object;
            }
        } else {
            $temp_object 							= $this->change_category($change, $previous_available, 1);
            $temp_object->start 					= $expected_start;
            $final_availability_array[$station][] 	= $temp_object;
        }
    }

    /**
     * Prepares an object to be added to the final array
     *
     * @param boolean 	$change	 				indicates if a change has to be performed or not
     * @param int		$previous_available		contains the previously added category
     * @param int 		$category				contains the current category to add
     *
     * @return void|stdClass
     *
     * @since 0.0.2
     */
    private function change_category(&$change, &$previous_available, $category) {
        // if the previously added category and the current category are different
        if ($previous_available !== $category) {
            // set the $change flag to true since later changes have to be performed
            $change 			= true;
            $previous_available = $category;

            // prepare and return a new object for the final array
            $temp_object 			= new stdClass();
            $temp_object->available = $this->custom_categories_array[$category - 1];

            return $temp_object;
        }
    }

    // get file availability from database
    private function getAvailabilityDB($start_date, $end_date, $selected_stations) {
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }			// create a database connection
        $availability_query = $db->getQuery(true);

        // generate a database query
        $availability_query->select(
            $db->quoteName('system_id') . ', '
            . $db->quoteName('start')
        );
        $availability_query->from($db->quoteName('file'));
        $availability_query->where(
            $db->quoteName('start') . ' >= convert(' . $db->quote($start_date) . ', DATETIME)');
        $availability_query->where(
            $db->quoteName('start') . ' < convert(' . $db->quote($end_date) . ', DATETIME)');
        $availability_query->where(
            $db->quoteName('system_id') . ' in (' . implode(', ', $selected_stations) . ')');
        $availability_query->order($db->quoteName('start'));

        // execute the previously generated query
        $db->setQuery($availability_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    // get file availability rate from database
    private function getAvailabilityRateDB($start_date, $end_date, $selected_stations) {
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }			// create a database connection
        $availability_query = $db->getQuery(true);

        // generate a database query
        $availability_query->select(
            $db->quoteName('system_id') . ', '
            . $db->quoteName('rate') 	. ', '
            . $db->quoteName('date')
        );
        $availability_query->from($db->quoteName('file_availability'));
        $availability_query->where(
            $db->quoteName('date') . ' >= convert(' . $db->quote($start_date) . ', DATE)');
        $availability_query->where(
            $db->quoteName('date') . ' < convert(' . $db->quote($end_date) . ', DATE)');
        $availability_query->where(
            $db->quoteName('system_id') . ' in (' . implode(', ', $selected_stations) . ')');
        $availability_query->order($db->quoteName('date'));

        // execute the previously generated query
        $db->setQuery($availability_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
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
