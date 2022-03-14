<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ItemModel;

/**
 * Systems Model
 * 
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.1
 */
class BramsAdminModelSystems extends ItemModel {
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

	// TODO : change this function according to the needs
	public function getSystems() {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
		$system_query->select(
			$db->quoteName('system.id') . 'as id, '
			. $db->quoteName('system.name') . 'as name, '
			. $db->quoteName('location_code') . 'as code, '
			. $db->quoteName('start') . ', '
			. $db->quoteName('end')
		);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id'));

		$db->setQuery($system_query);

		return $db->loadObjectList();
	}
}
