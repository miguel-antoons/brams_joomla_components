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
 * Locations Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * receiving stations.
 *
 * @since  0.0.2
 */
class BramsAdminModelLocations extends ItemModel {
    public $location_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message') => '',
            ('css_class') => ''
        ),
        (1) => array(
            ('message') => 'Location was successfully updated',
            ('css_class') => 'success'
        ),
        (2) => array(
            ('message') => 'Location was successfully created',
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
     * Function gets all the locations and their information from the database. If no
     * error occurs, it returns all the received information from the database.
     *
     * @return int|array -1 if an error occurs, the array with all the locations on success
     * @since 0.4.1
     */
    public function getLocations() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $locations_query = $db->getQuery(true);

        // SQL query to get all the locations and their information
        $locations_query->select(
            $db->quoteName('location.id') . ' as location_id, '
            . $db->quoteName('location_code') . ', '
            . $db->quoteName('name') . ', '
            . $db->quoteName('longitude') . ', '
            . $db->quoteName('latitude') . ', '
            . $db->quoteName('transfer_type') . ', '
            . $db->quoteName('observer.id') . ' as obs_id, '
            . 'concat(' . $db->quoteName('first_name') . ', \' \', '
            . $db->quoteName('last_name') . ') as obs_name, '
            . $db->quoteName('ftp_password') . ', '
            . $db->quoteName('tv_id') . ', '
            . $db->quoteName('tv_password')
        );
        $locations_query->from($db->quoteName('location'));
        $locations_query->from($db->quoteName('observer'));
        $locations_query->where(
            $db->quoteName('location.observer_id') . ' = ' . $db->quoteName('observer.id')
        );

        $db->setQuery($locations_query);

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
     * Function deletes location with location.id equal to $id (arg)
     *
     * @param $id int id of the system that has to be deleted
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.4.1
     */
    public function deleteLocation($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // system to delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $system_query->delete($db->quoteName('location'));
        $system_query->where($condition);

        $db->setQuery($system_query);

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
