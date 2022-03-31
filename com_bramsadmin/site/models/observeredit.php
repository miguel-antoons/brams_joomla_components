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
 * ObserverEdit Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * locations.
 *
 * @since  0.5.2
 */
class BramsAdminModelObserverEdit extends ItemModel {
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
            $db->quoteName('id') . ', '
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
     * Function gets all the countries (country.country_code, country.name) from
     * the database. It then adds a new 'selected' attribute to each returned object.
     * The new attribute will decide which country will be the one selected by default.
     *
     * @param $current_country  string      Country to set to default selected
     * @return                  array|int   -1 if the function fails, the array with all the countries on success
     *
     * @since 0.4.2
     */
    public function getCountries($current_country) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $country_query = $db->getQuery(true);

        // query to get all the available countries and their code
        $country_query->select(
            $db->quoteName('country_code') . ', '
            . $db->quoteName('name')
        );
        $country_query->from($db->quoteName('country'));
        $country_query->order($db->quoteName('name'));

        $db->setQuery($country_query);

        // try to execute the query and return the results
        try {
            return $this->structureCountries($db->loadObjectList(), $current_country);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function adds a new 'selected' attribute to each country received from the database.
     * This 'selected' attribute will decide which country will be selected by default
     * on the front-end of the page.
     *
     * @param $database_data    array   array containing all the countries from the database
     * @param $current_country  string  country to select by default
     * @return                  array   array with the new 'selected' attribute
     *
     * @since 0.5.2
     */
    private function structureCountries($database_data, $current_country) {
        $final_country_array = array();

        // if no value us set for current_country
        if (!$current_country) {
            // set the default one to 'BE'
            $current_country = 'BE';
        }

        foreach ($database_data as $country) {
            // if the country code is not equal to $current_country (arg)
            if ($country->country_code !== $current_country) {
                // the country won't be the one that's selected
                $selected = '';
            } else {
                // if it is equal to $current_country
                // set this country to be the default one
                $selected = 'selected';
            }

            // add all the data to the final array
            $final_country_array[] = array(
                ('country_code')    => $country->country_code,
                ('name')            => $country->name,
                ('selected')        => $selected
            );
        }

        return $final_country_array;
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
            . $db->quoteName('first_name') . ', '
            . $db->quoteName('last_name') . ', '
            . $db->quoteName('email') . ', '
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
                $db->quote($observer_info['code']) . ', '
                . $db->quote($observer_info['first_name']) . ', '
                . $db->quote($observer_info['last_name']) . ', '
                . $db->quote($observer_info['country']) . ', '
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
