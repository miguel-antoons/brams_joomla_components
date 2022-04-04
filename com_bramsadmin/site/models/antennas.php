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
 * Antennas Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * antennas.
 *
 * @since  0.7.1
 */
class BramsAdminModelAntennas extends ItemModel {
    // array contains various antenna messages (could be moved to database if a lot of messages are required)
    public $antenna_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message') => '',
            ('css_class') => ''
        ),
        (1) => array(
            ('message') => 'Antenna was successfully updated',
            ('css_class') => 'success'
        ),
        (2) => array(
            ('message') => 'Antenna was successfully created',
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
     * Function gets antenna information from the BRAMS database. The information
     * it requests is the following : (id, brand, model and antenna code).
     *
     * @returns int|array -1 if an error occurred, else the array with antenna info
     * @since 0.7.1
     */
    public function getAntennas() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);

        // SQL query to get all information about the multiple systems
        $antenna_query->select(
            'distinct ' . $db->quoteName('radsys_antenna.id') . 'as id, '
            . $db->quoteName('brand') . ', '
            . $db->quoteName('antenna_code') . 'as code, '
            . $db->quoteName('model') . ', '
            . $db->quoteName('antenna_id') . ' as not_deletable'
        );
        $antenna_query->from($db->quoteName('radsys_antenna'));
        $antenna_query->join(
            'LEFT',
            $db->quoteName('radsys_system')
            . ' ON '
            . $db->quoteName('radsys_antenna.id')
            . ' = '
            . $db->quoteName('radsys_system.antenna_id')
        );

        $db->setQuery($antenna_query);

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
     * Function deletes antenna with antenna.id equal to $id (arg)
     *
     * @param $id   int                 id of the antenna that has to be deleted
     * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.7.1
     */
    public function deleteAntenna($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);

        // antenna to delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $antenna_query->delete($db->quoteName('radsys_antenna'));
        $antenna_query->where($condition);

        $db->setQuery($antenna_query);

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
