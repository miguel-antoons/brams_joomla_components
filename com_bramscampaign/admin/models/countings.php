<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Factory;

/**
 * Countings Model
 *
 * @since  0.0.1
 */
class BramsCampaignModelCountings extends ListModel {
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 * @since 0.0.1
	 */
	protected function getListQuery() {
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
        // query below is only temporarily and won't work since there is no availability table
		// $query->select('*')
        //         ->from($db->quoteName('#__availability'));

		return $query;
	}
}
