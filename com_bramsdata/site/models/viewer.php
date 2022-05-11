<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Viewer Model
 *
 * Gets all data required for the BRAMS data viewer to work
 *
 * @since  0.4.0
 */
class BramsDataModelViewer extends BaseDatabaseModel {
    // function connects to the database and returns the database object
    private function connectToDatabase() {
        try {
            /* Below lines are for connecting to production database later on */
             $database_options = array();

             $database_options['driver'] = $_ENV['DB_DRIVER'];
             $database_options['host'] = $_ENV['DB_HOST'];
             $database_options['user'] = $_ENV['DB_USER'];
             $database_options['password'] = $_ENV['DB_PASSWORD'];
             $database_options['database'] = $_ENV['DB_NAME'];
             $database_options['prefix'] = $_ENV['DB_PREFIX'];

             return JDatabaseDriver::getInstance($database_options);

            /*
            below line is for connecting to default joomla database
            WARNING : this line should be commented/removed for production
            */
            // return $this->getDbo();
        } catch (Exception $e) {
            // if an error occurs, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    /**
     * Function gets teh following data for all the available stations in the database :
     * (system.id, system.name, location.transfer_type, location.status, ''). If everything
     * goes well, it returns all that data.
     *
     * @return int|array -1 if an error occurs, the array with all the results if everything wen well.
     * @since 0.0.2
     */
    public function getStations() {
        if (!$db = $this->connectToDatabase()) return -1;
        $system_query = $db->getQuery(true);

        // SQL query to get all information about the multiple systems
        $system_query->select(
            $db->quoteName('alias')             . ', '
            . $db->quoteName('system.id')       . ', '
            . $db->quoteName('location_code')   . ', '
            . $db->quoteName('system.name')     . ', '
            . $db->quoteName('transfer_type')   . ', '
            . $db->quoteName('status')          . ', '
            . $db->quote('') . 'as checked'
        );
        $system_query->from($db->quoteName('system'));
        $system_query->from($db->quoteName('location'));
        $system_query->where(
            $db->quoteName('system.location_id') . ' = ' . $db->quoteName('location.id')
        );
        $system_query->order($db->quoteName('system.name'). ' ASC');

        $db->setQuery($system_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // if it fails, log the error and return false
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

	public function getFileStatus($file_start, $sysId) {
		if (!$db = $this->connectToDatabase()) return -1;
		$file_query = $db->getQuery(true);

		// get the status of the requested file
		$file_query->select($db->quoteName('status'));
		$file_query->from($db->quoteName('file'));
		$file_query->where(
			$db->quoteName('system_id') . ' = ' . $db->quote($sysId)
		);
		$file_query->where(
			$db->quoteName('start') . ' = ' . $db->quote($file_start)
		);

		$db->setQuery($file_query);

		// try to execute the query and return the result
		try {
			return $db->loadObjectList()[0]->status;
		} catch (RuntimeException $e) {
			// if it fails, log the error and return false
			Log::add($e, Log::ERROR, 'error');
			return -1;
		}
	}

    // get today's date in yyy-mm-dd format
    public function getToday() {
        return date('Y-m-d') . 'T00:00';
    }
}
