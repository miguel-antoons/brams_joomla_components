<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Log\Log;

/**
 * SystemEdit Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.2
 */
class BramsAdminModelSystemEdit extends ItemModel {
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
            return Factory::getDbo();
        } catch (Exception $e) {
            // if an error occurs, log the error and return false
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

	/**
     * Function gets all the information about one and only system from the database.
     * The $id argument tells the function which sytem to get. The information
     * requested by the function is the following : (system.id, system.name, system.location_id,
     * system.start, system.antenna, system.comments).
     *
     * @param $id int the id of the requested system
     * @return boolean|array false if an error occurred, the array with system info on success
     *
     * @since 0.2.0
     */
	public function getSystemInfo($id) {
        // if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
            return false;
        }
		$system_query = $db->getQuery(true);

		// query to get the system info
		$system_query->select(
			$db->quoteName('id') . ', '
			. $db->quoteName('name') . ', '
			. $db->quoteName('location_id') . ', '
			. $db->quoteName('start') . ', '
			. $db->quoteName('antenna') . ', '
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
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

    /**
     * Function gets all system names except for the system which id is given as argument.
     * The data requested for each system is the followin : (system.name).
     *
     * @param $id int the id of the system not to take the name from. Defaults to -1
     * @return boolean|array false on fail, database results on success
     *
     * @since 0.2.0
     */
	public function getSystemNames($id = -1) {
        // if database connection fails, return false
		if (!$db = $this->connectToDatabase()) {
            return false;
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
            Log::add($e, Log::ERROR, 'error');
            return false;
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
     * @return boolean|array on fail returns false, on success returns the array with all the results
     *
     * @since 0.2.0
     */
	public function getLocations($antenna = -1, $id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return false;
        }
		$locations_query = $db->getQuery(true);

        // query to get all the antennas, location names and location ids
		$locations_query->select(
			$db->quoteName('location.id') . ' as id, '
			. $db->quoteName('location.name') . ' as name, '
			. $db->quoteName('antenna')
		);
		$locations_query->from($db->quoteName('location'));
		$locations_query->from($db->quoteName('system'));
		$locations_query->where(
			$db->quoteName('location_id') . ' = ' . $db->quoteName('location.id')
		);

		$db->setQuery($locations_query);

        // try to execute the query and return the results
        try {
            return $this->structureLocations($db->loadObjectList(), $antenna, $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

    /**
     * Function groups all the data received from the 'getLocations()' function
     * by location (instead of having a row for each antenna, antennas will be
     * added to an array per location id). However, if a row contains
     * (antenna == $antenna (arg) && location->id == $lid (arg)) then is is not added
     * to the array.
     *
     * @param $database_data array results from the databas
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
				$final_location_array[$location->id] = new stdClass();
				$final_location_array[$location->id]->id = $location->id;
				$final_location_array[$location->id]->name = $location->name;
				$final_location_array[$location->id]->selected = '';
				$final_location_array[$location->id]->antennas = array();
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
     * @return boolean|JDatabaseDriver on fail returns false, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
	public function insertSystem($new_system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return false;
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
				$db->quote($new_system_info['name']) . ', '
				. $db->quote($new_system_info['location']) . ', '
				. $db->quote($new_system_info['antenna']) . ', '
				. $db->quote($new_system_info['start']) . ', '
				. $db->quote($new_system_info['comments']) . ', '
				. $db->quote($new_system_info['start']) . ', '
				. $db->quote($new_system_info['start'])
			);

		$db->setQuery($system_query);

        // try to execute the query and return the result
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

    /**
     * Function updates a unique systems attributes with values it receives as
     * arguments.
     *
     * @param $system_info array array with the new system attribute values
     * @return boolean|JDatabaseDriver on fail returns false, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
	public function updateSystem($system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return false;
        }
		$system_query = $db->getQuery(true);
        // attributes to update
		$fields = array(
			$db->quoteName('name') . ' = ' . $db->quote($system_info['name']),
			$db->quoteName('location_id') . ' = ' . $db->quote($system_info['location']),
			$db->quoteName('antenna') . ' = ' . $db->quote($system_info['antenna']),
			$db->quoteName('start') . ' = ' . $db->quote($system_info['start']),
			$db->quoteName('comments') . ' = ' . $db->quote($system_info['comments'])
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
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

    /**
     * Function deletes system with system.id equal to $id (arg)
     *
     * @param $id int id of the system that has to be deleted
     * @return boolean|JDatabaseDriver on fail returns false, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
	public function deleteSystem($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return false;
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
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

	// get today's date in yyyy-mm-dd hh:mm:ss format
	public function getNow() {
		return date('Y-m-d H:i:s');
	}
}
