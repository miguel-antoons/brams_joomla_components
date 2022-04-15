<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * Countings Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * countings.
 * A single counting is a lapse of time where a user can
 * select meteors on a spectrogram. For a counting to exist,
 * the user must first create a campaign.
 *
 * @since  0.1.1
 */
class BramsCampaignModelCountings extends BaseDatabaseModel {
	public $counting_messages = array(
		// default message (0) is empty
		(0) => array(
			('message') 	=> '',
			('css_class') 	=> ''
		),
		(1) => array(
			('message') 	=> 'Counting was successfully updated',
			('css_class') 	=> 'success'
		),
		(2) => array(
			('message') 	=> 'Counting was successfully created',
			('css_class') 	=> 'success'
		)
	);

	// function connects to the database and returns the database object
	private function connectToDatabase() {
		try {
			/* Below lines are for connecting to production database later on */
			// $database_options = array();

			// $database_options['driver'] = $_ENV['DB_DRIVER'];
			// $database_options['host'] = $_ENV['DB_HOST'];
			// $database_options['user'] = $_ENV['DB_USER'];
			// $database_options['password'] = $_ENV['DB_PASSWORD'];
			// $database_options['database'] = $_ENV['DB_NAME'];
			// $database_options['prefix'] = $_ENV['DB_PREFIX'];

			// return JDatabaseDriver::getInstance($database_options);

			/*
			below line is for connecting to default joomla database
			WARNING : this line should be commented/removed for production
			*/
			return $this->getDbo();
		} catch (Exception $e) {
			// if an error occurs, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return false;
		}
	}

	/**
	 * Function retrieves the first file of a campaign. It does so by
	 * querying the database for the file that best matches the campaign
	 * whose system_id is equal to the campaign's system_id.
	 *
	 * @param $start_date   string  string representation of the current time
	 * @param $system_id    int     id of the campaign's system
	 *
	 * @return int  returns -1 if an error occurs, or the file id if everything went well
	 *
	 * @since 0.1.1
	 */
	private function getFirstFile($start_date, $system_id) {
		// if the connection to the database failed, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$file_query = $db->getQuery(true);

		// query to select the first file of the campaign
		$file_query->select($db->quoteName('id'));
		$file_query->from($db->quoteName('file'));
		$file_query->where($db->quoteName('system_id') . ' = ' . $db->quote($system_id));
		$file_query->where($db->quoteName('start') . ' >= ' . $db->quote($start_date));
		$file_query->setLimit(1);

		$db->setQuery($file_query);

		// try to execute the query and return the result
		try {
			return $db->loadObjectList()[0]->id;
		} catch (Exception $e) {
			// if it fails, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	/**
	 * Function creates a new counting. A counting is basically a link
	 * between a user and a campaign. There can only be one counting per
	 * user and campaign combo.
	 * However, there may be several countings per user and also several
	 * countings per campaign.
	 *
	 * @param $counting_info array  array with all the needed info from the new
	 *                              counting (start, system_id, campaign_id)
	 *
	 * @return bool|int returns -1 if an error occurs,
	 *                  returns true if the query was successfully executed
	 *
	 * @throws Exception
	 * @since 0.1.1
	 */
	public function createCounting($counting_info) {
		// if the connection to the database failed, return false
		if (!$db = $this->connectToDatabase()) {
			return -1;
		}
		$current_datetime = $this->getNow();
		$counting_query = $db->getQuery(true);

		// get the first file id. Return -1 if an error occurs
		if (($file_id = $this->getFirstFile($counting_info['start_date'], $counting_info['system_id'])) === -1) {
			return -1;
		}

		// query to create a new counting for the user
		$counting_query
			->insert($db->quoteName('manual_counting'))
			->columns(
				$db->quoteName(
					array(
						'campaign_id',
						'user_id',
						'file_id',
						'time_created',
						'time_updated'
					)
				)
			)
			->values(
				$db->quote($counting_info['campaign_id'])   . ', '
				. $db->quote(Factory::getApplication()->getIdentity()->id)  . ', '
				. $db->quote($file_id) . ', '
				. $db->quote($current_datetime). ', '
				. $db->quote($current_datetime)
			);

		$db->setQuery($counting_query);

		// try to execute the query and return the result
		try {
			return $db->execute();
		} catch (Exception $e) {
			// on fail, log the error and return false
			echo new JResponseJson(array(('message') => $e));
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

	// function returns a string representation of the current datetime
	private function getNow() {
		return date('Y-m-d H:i:s');
	}
}
