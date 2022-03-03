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
 * Map Model
 * 
 * Gets data from the database linked to the BRAMS network map. This is a map
 * created dynamically from data in the database. The map should contain all the
 * BRAMS receiving stations on the correct locations.
 *
 * @since  0.0.1
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

    /* WARNING : Below function and database query were directly copied from another
    component and must be modified in order to work with this component */
	// get all the stations and their name from the database
	public function getActiveStationInfo() {
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
		$system_query->where($db->quoteName('date') . ' = ' . $db->quote('2022-02-18'));
		$system_query->where($db->quoteName('location.time_created') . ' < ' . $db->quote('2022-02-18'));

		$db->setQuery($system_query);

		return $db->loadObjectList();
	}

	/* WARNING : Below function and database query were directly copied from another
    component and must be modified in order to work with this component */
	// get all the stations and their name from the database
	public function getinActiveStationInfo() {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
		$system_query->select(
			'distinct '
			. $db->quoteName('location.name') . ', '
			. $db->quoteName('country_code') . ', '
			. $db->quoteName('transfer_type') . ', '
			. $db->quoteName('longitude') . ', '
			. $db->quoteName('latitude') . ', 0 as rate'
			);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id'));
		$system_query->where($db->quoteName('location.time_created') . ' < ' . $db->quote('2022-02-18'));
		$system_query->where(
			$db->quoteName('system.id') . ' not in (
				select system.id 
				from file_availability 
				where date = ' . $db->quote('2022-02-18') . ')'
		);

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
}
