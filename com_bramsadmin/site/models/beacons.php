<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Log\Log;

/**
 * Beacons Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * beacons.
 *
 * @since  0.6.1
 */
class BramsAdminModelBeacons extends ItemModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $beacon_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message') => '',
            ('css_class') => ''
        ),
        (1) => array(
            ('message') => 'Beacon was successfully updated',
            ('css_class') => 'success'
        ),
        (2) => array(
            ('message') => 'Beacon was successfully created',
            ('css_class') => 'success'
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
            return Factory::getDbo();
        } catch (Exception $e) {
            // if an error occurs, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    /**
     * Function gets beacon information from the BRAMS database. The information
     * it requests is the following : (id, name, latitude, longitude, frequency,
     * power).
     *
     * @returns int|array -1 if an error occurred, else the array with beacon info
     * @since 0.6.1
     */
    public function getBeacons() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);

        // SQL query to get all information about the multiple systems
        $beacon_query->select(
            'distinct ' . $db->quoteName('beacon.id') . 'as id, '
            . $db->quoteName('beacon.name') . 'as name, '
            . $db->quoteName('latitude') . ', '
            . $db->quoteName('longitude') . ', '
            . $db->quoteName('frequency') . ', '
            . $db->quoteName('power') . ', '
            . $db->quoteName('beacon_id') . ' as not_deletable'
        );
        $beacon_query->from($db->quoteName('beacon'));
        $beacon_query->join(
            'LEFT',
            $db->quoteName('file')
            . ' ON '
            . $db->quoteName('beacon.id')
            . ' = '
            . $db->quoteName('beacon_id')
        );

        $db->setQuery($beacon_query);

        // try to execute the query and return the system info
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // if an error occurs, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function deletes beacon with beacon.id equal to $id (arg)
     *
     * @param $id   int                 id of the beacon that has to be deleted
     * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
    public function deleteBeacon($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);

        // system to delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $beacon_query->delete($db->quoteName('beacon'));
        $beacon_query->where($condition);

        $db->setQuery($beacon_query);

        // trying to execute the query and return the results
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }
}
