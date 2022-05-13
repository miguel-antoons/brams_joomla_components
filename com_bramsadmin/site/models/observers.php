<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * Observers Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * observers.
 *
 * @since  0.5.1
 */
class BramsAdminModelObservers extends BaseDatabaseModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $observer_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Observer was successfully updated',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Observer was successfully created',
            ('css_class')   => 'success'
        )
    );

    // function connects to the database and returns the database object
    private function connectToDatabase() {
        try {
            /* Below lines are for connecting to production database later on */
	        $database_options = parse_ini_file(JPATH_ROOT.DIRECTORY_SEPARATOR.'env.ini');
	        return JDatabaseDriver::getInstance($database_options);

            /*
            below line is for connecting to default joomla database
            WARNING : this line should be commented/removed for production
            */
            // return Factory::getDbo();
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
        $sub_receiver_query = $db->getQuery(true);

        // query to check if there are any systems for a given receiver
        $sub_receiver_query->select($db->quoteName('observer_id'));
        $sub_receiver_query->from($db->quoteName('location'));
        $sub_receiver_query->where(
            $db->quoteName('observer_id') . ' = ' . $db->quoteName('observer.id') . ' limit 1'
        );

        // SQL query to get all the observers and their attributes
        $observer_query->select(
            'distinct ' . $db->quoteName('id')      . ', '
            . $db->quoteName('first_name')          . ', '
            . $db->quoteName('last_name')           . ', '
            . $db->quoteName('email')               . ', '
            . $db->quoteName('observer_code')       . ' as code, '
            . 'exists(' . $sub_receiver_query . ')' . ' as notDeletable'
        );
        $observer_query->from($db->quoteName('observer'));

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
     * Function gets all the observer from the database and returns them
     * with an additional 'selected' attribute. This attribute will decide
     * which observer to select by default.
     *
     * @param $current_observer     int          id of the observer to select by default
     * @return                      int|array    -1 if the function fails, the results from the database on success
     *
     * @since 0.4.2
     */
    public function getObserversSimple($current_observer) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // query to get all the available countries and their code
        $observer_query->select(
            $db->quoteName('id') . ', '
            . 'concat(' . $db->quoteName('first_name')  . ', \' \', '
            . $db->quoteName('last_name')               . ') as name'
        );
        $observer_query->from($db->quoteName('observer'));
        $observer_query->order($db->quoteName('name'));

        $db->setQuery($observer_query);

        // try to execute the query and return the results
        try {
            return $this->structureObserversSimple($db->loadObjectList(), $current_observer);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function adds a selected attribute to each observer object from the
     * database. This attribute will decide which observer will be selected
     * by default.
     * The observer that will be selected by default has its id equal to
     * $current_observer
     *
     * @param $database_data    array   observers coming straight from the database
     * @param $current_observer int     id of the observer to select by default
     * @return                  array   array of observers with the added 'selected' attribute
     *
     * @since 0.4.2
     */
    private function structureObserversSimple($database_data, $current_observer) {
        $final_observer_array = array();

        // if no observer has to be selected by default
        if (!$current_observer) {
            // set a placeholder by default
            $final_observer_array[] = array(
                ('id')       => 0,
                ('name')     => '--select an observer--',
                ('selected') => 'selected'
            );
        }

        // iterate over the database data (all the observers)
        foreach ($database_data as $observer) {
            // if the observers id is equal to $current_observer (arg)
            if ($observer->id === $current_observer) {
                // set this observer to be the default selected one
                $selected = 'selected';
            } else {
                $selected = '';
            }

            // add all the information to the final array
            $final_observer_array[] = array(
                ('id')       => $observer->id,
                ('name')     => $observer->name,
                ('selected') => $selected
            );
        }

        return $final_observer_array;
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

    /**
     * Function gets all the available observer codes from the database except
     * the observer code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the observer not to take the observer code from
     * @return      int|array   -1 if the function fails, the array with observer codes on success
     *
     * @since 0.5.2
     */
    public function getObserverCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // query to get all the location codes and ids
        $observer_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('observer_code')
        );
        $observer_query->from($db->quoteName('observer'));

        $db->setQuery($observer_query);

        // try to execute the query and return the results
        try {
            return $this->structureObservers($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the observer whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   observer codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all observer codes except the one with id = $id
     *
     * @since 0.5.2
     */
    private function structureObservers($database_data, $id) {
        $final_observer_array = array();
        foreach ($database_data as $observer) {
            // if the location id is not equal to $id (arg)
            if ($observer->id !== $id) {
                // add the location code to the location codes array
                $final_observer_array[] = $observer->observer_code;
            }
        }

        return $final_observer_array;
    }

    /**
     * Function gets all the information from the database related to the observer
     * with its id equal to $observer_id (arg)
     *
     * @param $observer_id  int         id of the observer to get information about
     * @return              array|int   -1 on fail, array with location info on success
     *
     * @since 0.4.2
     */
    public function getObserver($observer_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // query to get the location information
        $observer_query->select(
            $db->quoteName('observer_code') . ', '
            . $db->quoteName('first_name')  . ', '
            . $db->quoteName('last_name')   . ', '
            . $db->quoteName('email')       . ', '
            . $db->quoteName('country_code')
        );
        $observer_query->from($db->quoteName('observer'));
        $observer_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($observer_id)
        );

        $db->setQuery($observer_query);

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
     * Function inserts a new observer into the database. The attributes of the new
     * observer are given as argument ($observer_info)
     *
     * @param $observer_info    array               array with the attributes of the new observer
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.5.2
     */
    public function newObserver($observer_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // query to insert a new observer with data being the $observer_info arg
        $observer_query
            ->insert($db->quoteName('observer'))
            ->columns(
                $db->quoteName(
                    array(
                        'observer_code',
                        'first_name',
                        'last_name',
                        'country_code',
                        'email'
                    )
                )
            )
            ->values(
                $db->quote($observer_info['code'])          . ', '
                . $db->quote($observer_info['first_name'])  . ', '
                . $db->quote($observer_info['last_name'])   . ', '
                . $db->quote($observer_info['country'])     . ', '
                . $db->quote($observer_info['email'])
            );

        $db->setQuery($observer_query);

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
     * Function updates an observer from the database with values from the
     * $observer_info argument.
     *
     * @param $observer_info    array               array with the attributes of the modified observer
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.5.2
     */
    public function updateObserver($observer_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('observer_code') . ' = ' . $db->quote($observer_info['code']),
            $db->quoteName('first_name')    . ' = ' . $db->quote($observer_info['first_name']),
            $db->quoteName('last_name')     . ' = ' . $db->quote($observer_info['last_name']),
            $db->quoteName('country_code')  . ' = ' . $db->quote($observer_info['country']),
            $db->quoteName('email')         . ' = ' . $db->quote($observer_info['email'])
        );

        // location to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($observer_info['id'])
        );

        // update query
        $observer_query
            ->update($db->quoteName('observer'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($observer_query);

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
}
