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
 * SystemEdit Model
 * 
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.2
 */
class BramsAdminModelSystemEdit extends ItemModel {
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

	// TODO : change this function
	public function getSystemInfo($id) {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
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

		return $db->loadObjectList();
	}

	public function getLocations() {
		$db = $this->connectToDatabase();
		$locations_query = $db->getQuery(true);

		$locations_query->select(
			$db->quoteName('id') . ', '
			. $db->quoteName('name')
		);
		$locations_query->from($db->quoteName('location'));

		$db->setQuery($locations_query);

		return $db->loadObjectList();
	}

	public function insertSystem($new_system_info) {
		$db = $this->connectToDatabase();
		$system_query = $db->getQuery(true);

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
		echo $system_query;

		// $db->setQuery($system_query);
		// $db->execute();
	}

	// get today's date in yyyy-mm-dd hh:mm:ss format
	public function getNow() {
		return date('Y-m-d H:i:s');
	}
}
