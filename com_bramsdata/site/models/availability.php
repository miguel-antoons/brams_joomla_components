<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ItemModel;

/**
 * Availability Model
 *
 * @since  0.0.1
 */
class BramsDataModelAvailability extends ItemModel {
	/**
	 * @var string message
	 */
	protected $message;

	/**
	 * Get the message
     *
	 * @return  string  The message to be displayed to the user
	 */
	public function getMsg() {
		if (!isset($this->message))
		{
			$this->message = 'Hello World!';
		}

		return $this->message;
	}

	public function getStations() {
		$db = JFactory::getDbo();
		$system_query = $db->getQuery(true);

		// SQL query to get all inforamtions about the multiple systems
		$system_query->select(
			$db->quoteName('system.id') . ', '
			. $db->quoteName('system.name') . ', '
			. $db->quoteName('transfer_type') . ', '
			. $db->quoteName('status')
			);
		$system_query->from($db->quoteName('system'));
		$system_query->from($db->quoteName('location'));
		$system_query->where($db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id'));

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

	// get all the file information between 2 dates
	public function getAvailability() {
		$db = JFactory::getDbo();
		$availability_query = $db->getQuery(true);

		$availability_query->select($db->quoteName('system_id') . ', ' . $db->quoteName('start'));
		$availability_query->from($db->quoteName('file'));
		$availability_query->where($db->quoteName('start') . ' >= convert(' . $db->quote($this->get('StartDate')) . ', DATETIME)');
		$availability_query->where($db->quoteName('start') . ' < convert(' . $db->quote($this->get('EndDate')) . ', DATETIME)');
		echo $availability_query;
		echo $this->start_date;

		$db->setQuery($availability_query);

		return $db->loadObjectList();
	}
}
