<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\ItemModel;

/**
 * Observers Model
 *
 * Model gets all the data needed to show the observers of the brams
 * network together with their stations. To do this, it queries the
 * database.
 *
 * @since  0.2.1
 */
class BramsNetworkModelObservers extends ItemModel {
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
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
	}

	// get all station locations and their owners from the database
	public function getObserverInfo() {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
		$system_query = $db->getQuery(true);

		// SQL query to get all information about the multiple systems
		$system_query->select(
			$db->quoteName('observer.id') . ' as id, '
			. $db->quoteName('first_name') . ', '
			. $db->quoteName('last_name') . ', '
			. $db->quoteName('name') . ' as location_name'
		);
		$system_query->from($db->quoteName('observer'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('observer.id') . ' = ' . $db->quoteName('observer_id'));

		$db->setQuery($system_query);

        // try to execute the query and return the structured result
        try {
            return $this->structureObserverInfo($db->loadObjectList());
        } catch (RuntimeException $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
	}

	/**
	 * Function structures the data received from the database.
	 * It groups all the locations by owner and returns the newly
	 * created and structured array.
     *
     * @since 0.2.0
	 */
	private function structureObserverInfo($observer_info) {
		$new_observer_array = array();

		foreach ($observer_info as $observer) {
			// if the owner object already exists
			if (array_key_exists($observer->id, $new_observer_array)) {
				// just add the location string to the rest
				$new_observer_array[$observer->id]->locations .= ', ' . $observer->location_name;
			} else {
				// create a new object and set first_name, last_name and temporary locations attributes
				$new_observer_array[$observer->id] = new stdClass();
				$new_observer_array[$observer->id]->first_name = $observer->first_name;
				$new_observer_array[$observer->id]->last_name = $observer->last_name;
				$new_observer_array[$observer->id]->locations = $observer->location_name;
			}
		}

		return $new_observer_array;
	}
}
