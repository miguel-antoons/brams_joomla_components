<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_availability
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * AvailabilityList Model
 *
 * @since  0.0.1
 */
class AvailabilityModelAvailability extends JModelList
{
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
		$query->select('*')
                ->from($db->quoteName('#__availability'));

		return $query;
	}
}
