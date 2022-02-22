<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ListModel;

/**
 * AvailabilityList Model
 *
 * @since  0.0.1
 */
class BramsDataModelAvailability extends ListModel {
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
        // query below is only temporarly and won't work since there is no availability table
		$query->select('*')
                ->from($db->quoteName('#__availability'));

		return $query;
	}
}
