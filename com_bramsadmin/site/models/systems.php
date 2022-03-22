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
use \Joomla\CMS\Log\Log;

/**
 * Systems Model
 * 
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.2
 */
class BramsAdminModelSystems extends ItemModel {
	public $system_messages = array(
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
	// fucntion connects to the database and returns the database object
	private function connectToDatabase() {
		/* Below lines are for connecting to production database later on */
		try {
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
		} catch (Exception $e) {
			echo '
				An error occured when trying to connect to the database. 
				Activate Joomla debugging and view the logs for more information.
			';
			JLog::add($e, JLog::ERROR, 'jerror');
			return false;
		}

		return $db;
	}

	// TODO : change this function according to the needs
	public function getSystems() {
		if (!$db = $this->connectToDatabase()) {
			return false;
		}
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

		try {
			return $db->loadObjectList();
		} catch (Exception $e) {
			echo '
				An error occured while requesting data to the database. 
				Activate Joomla debugging and view the logs for more information.
			';
			Log::add($e, JLog::ERROR, 'jerror');
			return false;
		}
	}
}
