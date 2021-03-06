<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * Systems Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.2
 */
class BramsAdminModelSystems extends BaseDatabaseModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
	public $system_messages = array(
        // default message (0) is empty
		(0) => array(
			('message') => '',
			('css_class') => ''
		),
		(1) => array(
			('message') => 'System was successfully updated',
			('css_class') => 'success'
		),
		(2) => array(
			('message') => 'System was successfully created',
			('css_class') => 'success'
		)
	);

	// function connects to the database and returns the database object
	private function connectToDatabase() {
		try {
            /* Below lines are for connecting to production database later on */
			$database_options = parse_ini_file(JPATH_ROOT.DIRECTORY_SEPARATOR.'env.ini');
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
     * Function gets system information from the BRAMS database. The information
     * it requests is the following : (system.id, system.name, location.location_code,
     * system.start, system.end).
     *
     * @returns int|array -1 if an error occurred, else the array with system info
     * @since 0.1.0
     */
	public function getSystems() {
        // if the connection to the database failed, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$system_query = $db->getQuery(true);
        $sub_system_query = $db->getQuery(true);

        // query to check if there are any files for a given system
        $sub_system_query->select($db->quoteName('system_id'));
        $sub_system_query->from($db->quoteName('file'));
        $sub_system_query->where(
            $db->quoteName('system_id') . ' = ' . $db->quoteName('system.id') . ' limit 1'
        );

		// SQL query to get all information about the multiple systems
		$system_query->select(
			$db->quoteName('system.id')             . ' as id, '
			. $db->quoteName('system.name')         . ' as name, '
			. $db->quoteName('location_code')       . ' as code, '
			. $db->quoteName('system.start')        . ' as start, '
			. $db->quoteName('system.end')          . ' as end, '
            . 'exists(' . $sub_system_query . ')'   . ' as notDeletable'
		);
        $system_query->from($db->quoteName('location'));
		$system_query->join(
            'INNER',
            $db->quoteName('system')
            . ' ON '
            . $db->quoteName('system.location_id')
            . ' = '
            . $db->quoteName('location.id')
        );

		$db->setQuery($system_query);

        // try to execute the query and return the system info
		try {
			return $db->loadObjectList();
		} catch (RuntimeException $e) {
            // if an error occurs, log the error and return false
            echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

    /**
     * Function deletes system with system.id equal to $id (arg)
     *
     * @param $id int id of the system that has to be deleted
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
    public function deleteSystem($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // system to delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $system_query->delete($db->quoteName('system'));
        $system_query->where($condition);

        $db->setQuery($system_query);

        // trying to execute the query and return the results
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

	/**
     * Function gets all the information about one and only system from the database.
     * The $id argument tells the function which system to get. The information
     * requested by the function is the following : (system.id, system.name, system.location_id,
     * system.start, system.antenna, system.comments).
     *
     * @param $id int the id of the requested system
     * @return int|array -1 if an error occurred, the array with system info on success
     *
     * @since 0.2.0
     */
	public function getSystemInfo($id) {
        // if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$system_query = $db->getQuery(true);

		// query to get the system info
		$system_query->select(
			$db->quoteName('id')            . ', '
			. $db->quoteName('name')        . ', '
			. $db->quoteName('location_id') . ', '
			. $db->quoteName('start')       . ', '
			. $db->quoteName('antenna')     . ', '
			. $db->quoteName('comments')
		);
		$system_query->from($db->quoteName('system'));
		$system_query->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($system_query);

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
     * Function gets all system names except for the system which id is given as argument.
     * The data requested for each system is the following : (system.name).
     *
     * @param $id int the id of the system not to take the name from. Defaults to -1
     * @return int|array -1 on fail, database results on success
     *
     * @since 0.2.0
     */
	public function getSystemNames($id = -1) {
        // if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$system_query = $db->getQuery(true);

		// query to get the system names
		$system_query->select($db->quoteName('name'));
		$system_query->from($db->quoteName('system'));
		$system_query->where('not ' . $db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($system_query);

        // try to execute the query and return its results
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
	}

    /**
     * Function gets all the antennas for each location together with the location name,
     * and the location id.
     * NOTE : if the $antenna and the $id (location id) arguments are set, the antenna number
     * specified in the $antenna argument is not added to the location identified by the $id argument.
     * This has been done to facilitate automatic antenna number selection for front-end.
     *
     * @param $antenna int antenna number of the system shown at front-end, defaults to -1
     * @param $id int  location id of the system shown at front-end, defaults to -1
     * @return int|array on fail returns -1, on success returns the array with all the results
     *
     * @since 0.2.0
     */
	public function getLocations($antenna = -1, $id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$locations_query = $db->getQuery(true);

        // query to get all the antennas, location names and location ids
		$locations_query->select(
			$db->quoteName('location.id')       . ' as id, '
			. $db->quoteName('location.name')   . ' as name, '
			. $db->quoteName('antenna')
		);
		$locations_query->from($db->quoteName('location'));
        $locations_query->join(
            'LEFT',
            $db->quoteName('system')
            . ' ON '
            . $db->quoteName('location_id') . ' = ' . $db->quoteName('location.id')
        );
        $locations_query->order($db->quoteName('name') . ' ASC');

		$db->setQuery($locations_query);

        // try to execute the query and return the results
        try {
            return $this->structureLocations($db->loadObjectList(), $antenna, $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
	}

    /**
     * Function groups all the data received from the 'getLocations()' function
     * by location (instead of having a row for each antenna, antennas will be
     * added to an array per location id). However, if a row contains
     * (antenna == $antenna (arg) && location->id == $lid (arg)) then is is not added
     * to the array.
     *
     * @param $database_data array results from the database
     * @param $antenna int antenna number to ignore if found with $id
     * @param $id int location id for which $antenna will be ignored
     * @return array array with the database data grouped by location id
     *
     * @since 0.2.0
     */
	private function structureLocations($database_data, $antenna, $id) {
		$final_location_array = array();
		foreach ($database_data as $location) {
            // if the location has not yet been added to the final array
			if (!array_key_exists($location->id, $final_location_array)) {
                // add all the basic values
				$final_location_array[$location->id]            = new stdClass();
				$final_location_array[$location->id]->id        = $location->id;
				$final_location_array[$location->id]->name      = $location->name;
				$final_location_array[$location->id]->selected  = '';
				$final_location_array[$location->id]->antennas  = array();
			}

            // if the antenna and location id are not equal to $antenna and $id
			if ($location->id !== $id && $location->antenna !== $antenna) {
                // add the antenna to the location object
				$final_location_array[$location->id]->antennas[] = $location->antenna;
			}
		}

		return $final_location_array;
	}

    /**
     * Function inserts a new system with values received as argument
     *
     * @param $new_system_info array array with all the new system attributes
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
	public function insertSystem($new_system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$system_query = $db->getQuery(true);

        // query to insert the new system with its values
		$system_query
			->insert($db->quoteName('system'))
			->columns(
				$db->quoteName(
					array(
						'name',
						'location_id',
						'antenna',
						'start',
						'comments',
						'time_created',
						'time_updated'
					)
				)
			)
			->values(
				$db->quote($new_system_info['name'])        . ', '
				. $db->quote($new_system_info['location'])  . ', '
				. $db->quote($new_system_info['antenna'])   . ', '
				. $db->quote($new_system_info['start'])     . ', '
				. $db->quote($new_system_info['comments'])  . ', '
				. $db->quote($new_system_info['start'])     . ', '
				. $db->quote($new_system_info['start'])
			);

		$db->setQuery($system_query);

        // try to execute the query and return the result
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
	}

    /**
     * Function updates a unique systems attributes with values it receives as
     * arguments.
     *
     * @param $system_info array array with the new system attribute values
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
	public function updateSystem($system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$system_query = $db->getQuery(true);
        // attributes to update
		$fields = array(
			$db->quoteName('name')          . ' = ' . $db->quote($system_info['name']),
			$db->quoteName('location_id')   . ' = ' . $db->quote($system_info['location']),
			$db->quoteName('antenna')       . ' = ' . $db->quote($system_info['antenna']),
			$db->quoteName('start')         . ' = ' . $db->quote($system_info['start']),
			$db->quoteName('comments')      . ' = ' . $db->quote($system_info['comments'])
		);

        // system that will be updated
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($system_info['id'])
		);

        // update query
		$system_query
			->update($db->quoteName('system'))
			->set($fields)
			->where($conditions);

		$db->setQuery($system_query);

        // trying to execute the query and return the result
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
	}

	// get today's date in yyyy-mm-dd hh:mm:ss format
	public function getNow() {
		return date('Y-m-d H:i:s');
	}
}
