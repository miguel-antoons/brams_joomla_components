<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ItemModel;

/**
 * Observers Model
 * 
 * Model gets all the data needed to show the observers of the brams
 * network together with their stations. To do this, it queries the
 * database.
 *
 * @since  0.2.1
 */
class BramsNetworkModelMap extends ItemModel {
	// fucntion connects to the database and returns the database object
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

	// TODO : change below function according to model needs
	public function getObserverInfo() {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
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

		return $this->structureObserverInfo($db->loadObjectList());
	}

	private function structureObserverInfo($observer_info) {
		$new_observer_array = array();
		print_r($observer_info);
		echo '<br><br>';

		foreach ($observer_info as $observer) {
			if ($new_observer_array[$observer->id]) {
				$new_observer_array[$observer->id]->locations .= ', ' . $observer->location_name;
			} else {
				$new_observer_array[$observer->id]->first_name = $observer->first_name;
				$new_observer_array[$observer->id]->last_name = $observer->last_name;
				$new_observer_array[$observer->id]->locations = $observer->location_name;
			}
		}
		print_r($new_observer_array);
		return $new_observer_array;
	}
}
