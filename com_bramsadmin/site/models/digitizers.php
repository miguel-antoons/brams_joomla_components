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
 * Digitizers Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * digitizers.
 *
 * @since  0.8.1
 */
class BramsAdminModelDigitizers extends ItemModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $digitizer_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message') => '',
            ('css_class') => ''
        ),
        (1) => array(
            ('message') => 'Digitizer was successfully updated.',
            ('css_class') => 'success'
        ),
        (2) => array(
            ('message') => 'Digitizer was successfully created.',
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
     * Function gets digitizer information from the BRAMS database. The information
     * it requests is the following : (id, code, brand, model).
     *
     * @returns int|array -1 if an error occurred, else the array with digitizer info
     * @since 0.8.1
     */
    public function getDigitizers() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);

        // SQL query to get all information about the multiple systems
        $digitizer_query->select(
            'distinct ' . $db->quoteName('radsys_digitizer.id') . ' as id, '
            . $db->quoteName('brand') . ', '
            . $db->quoteName('model') . ', '
            . $db->quoteName('digitizer_code') . ' as code, '
            . $db->quoteName('radsys_digitizer.comments') . ' as comments, '
            . $db->quoteName('digitizer_id') . ' as not_deletable'
        );
        $digitizer_query->from($db->quoteName('radsys_digitizer'));
        $digitizer_query->join(
            'LEFT',
            $db->quoteName('radsys_system')
            . ' ON '
            . $db->quoteName('radsys_digitizer.id')
            . ' = '
            . $db->quoteName('digitizer_id')
        );

        $db->setQuery($digitizer_query);

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
     * Function deletes digitizer with digitizer.id equal to $id (arg)
     *
     * @param $id int id of the digitizer that has to be deleted
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.8.1
     */
    public function deleteDigitizer($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);

        // delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $digitizer_query->delete($db->quoteName('radsys_digitizer'));
        $digitizer_query->where($condition);

        $db->setQuery($digitizer_query);

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

    /**
     * Function gets all the information about one and only system from the database.
     * The $id argument tells the function which system to get. The information
     * requested by the function is the following : (system.id, system.name, system.location_id,
     * system.start, system.antenna, system.comments).
     *
     * @param $id int the id of the requested system
     * @return int|array -1 if an error occurred, the array with system info on success
     *
     * @since 0.2.0
     */
    public function getSystemInfo($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // query to get the system info
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

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function gets all system names except for the system which id is given as argument.
     * The data requested for each system is the following : (system.name).
     *
     * @param $id int the id of the system not to take the name from. Defaults to -1
     * @return int|array -1 on fail, database results on success
     *
     * @since 0.2.0
     */
    public function getSystemNames($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // query to get the system names
        $system_query->select($db->quoteName('name'));
        $system_query->from($db->quoteName('system'));
        $system_query->where('not ' . $db->quoteName('id') . ' = ' . $db->quote($id));

        $db->setQuery($system_query);

        // try to execute the query and return its results
        try {
            return $db->loadObjectList();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }


    /**
     * Function inserts a new system with values received as argument
     *
     * @param $new_system_info array array with all the new system attributes
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
    public function insertSystem($new_system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // query to insert the new system with its values
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

        $db->setQuery($system_query);

        // try to execute the query and return the result
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function updates a unique systems attributes with values it receives as
     * arguments.
     *
     * @param $system_info array array with the new system attribute values
     * @return int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.2.0
     */
    public function updateSystem($system_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);
        // attributes to update
        $fields = array(
            $db->quoteName('name')          . ' = ' . $db->quote($system_info['name']),
            $db->quoteName('location_id')   . ' = ' . $db->quote($system_info['location']),
            $db->quoteName('antenna')       . ' = ' . $db->quote($system_info['antenna']),
            $db->quoteName('start')         . ' = ' . $db->quote($system_info['start']),
            $db->quoteName('comments')      . ' = ' . $db->quote($system_info['comments'])
        );

        // system that will be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($system_info['id'])
        );

        // update query
        $system_query
            ->update($db->quoteName('system'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($system_query);

        // trying to execute the query and return the result
        try {
            return $db->execute();
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    // get today's date in yyyy-mm-dd hh:mm:ss format
    public function getNow() {
        return date('Y-m-d H:i:s');
    }
}
