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
 * Observers Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * observers.
 *
 * @since  0.5.1
 */
class BramsAdminModelObservers extends ItemModel
{
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $observer_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message') => '',
            ('css_class') => ''
        ),
        (1) => array(
            ('message') => 'Observer was successfully updated',
            ('css_class') => 'success'
        ),
        (2) => array(
            ('message') => 'Observer was successfully created',
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
     * Function gets all the observers from the BRAMS database. The information
     * it requests is the following : (observer_code, first_name, last_name,
     * email, id, location_id -> if any).
     * location_id is taken to indicate if the front-end can delete the observer.
     * If a location_id has been found for the observer, the observer won't be deletable
     * to prevent database errors (location table references observer table).
     *
     * @returns int|array -1 if an error occurred, else the array with system info
     * @since 0.5.1
     */
    public function getObservers() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // SQL query to get all the observers and their attributes
        $observer_query->select(
            'distinct ' . $db->quoteName('observer.id') . ' as id, '
            . $db->quoteName('first_name') . ', '
            . $db->quoteName('last_name') . ', '
            . $db->quoteName('email') . ', '
            . $db->quoteName('observer_code') . ' as code, '
            . $db->quoteName('location.observer_id') . ' as not_deletable'
        );
        $observer_query->from($db->quoteName('observer'));
        $observer_query->join(
            'LEFT',
            $db->quoteName('location')
            . ' ON '
            . $db->quoteName('observer.id')
            . ' = '
            . $db->quoteName('location.observer_id')
        );

        $db->setQuery($observer_query);

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
     * Function deletes observer with observer.id equal to $id (arg)
     *
     * @param $id   int                 id of the observer that has to be deleted
     * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.5.1
     */
    public function deleteObserver($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // system to delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $observer_query->delete($db->quoteName('observer'));
        $observer_query->where($condition);

        $db->setQuery($observer_query);

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
