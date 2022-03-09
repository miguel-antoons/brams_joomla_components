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
	public function getActiveStationInfo($selected_date) {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
		$system_query->select(
			'distinct '
			. $db->quoteName('location.name') . ', '
			. $db->quoteName('country_code') . ', '
			. $db->quoteName('transfer_type') . ', '
			. $db->quoteName('longitude') . ', '
			. $db->quoteName('latitude') . ', '
			. $db->quoteName('rate')
		);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('file_availability'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id'));
		$system_query->where($db->quoteName('system.id') . ' = ' . $db->quoteName('file_availability.system_id'));
		$system_query->where($db->quoteName('date') . ' = ' . $db->quote($selected_date));
		$system_query->where($db->quoteName('location.time_created') . ' < ' . $db->quote($selected_date));

		$db->setQuery($system_query);

		return $db->loadObjectList();
	}
}
