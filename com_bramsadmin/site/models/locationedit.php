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
 * LocationEdit Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * locations.
 *
 * @since  0.4.2
 */
class BramsAdminModelLocationEdit extends ItemModel {
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
     * Function gets all the available location codes from the database except
     * the location code whose id is equal to $id (arg
     *
     * @param $id   int         id of the location not to take the location code from
     * @return      int|array   -1 if the function fails, the array with location codes on success
     *
     * @since 0.4.2
     */
    public function getLocationCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $locations_query = $db->getQuery(true);

        // query to get all the location codes and ids
        $locations_query->select(
            $db->quoteName('id') . ', '
            . $db->quoteName('location_code')
        );
        $locations_query->from($db->quoteName('location'));

        $db->setQuery($locations_query);

        // try to execute the query and return the results
        try {
            return $this->structureLocations($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the location whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   location codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all location codes except the one with id = $id
     *
     * @since 0.4.2
     */
    private function structureLocations($database_data, $id) {
        $final_location_array = array();
        foreach ($database_data as $location) {
            // if the location id is not equal to $id (arg)
            if ($location->id !== $id) {
                // add the location code to the location codes array
                $final_location_array[] = $location->location_code;
            }
        }

        return $final_location_array;
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
     * @since 0.4.2
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
     * Function gets all the observer from the database and returns them
     * with an additional 'selected' attribute. This attribute will decide
     * which observer to select by default.
     *
     * @param $current_observer     int          id of the observer to select by default
     * @return                      int|array    -1 if the function fails, the results from the database on success
     *
     * @since 0.4.2
     */
    public function getObservers($current_observer) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $observer_query = $db->getQuery(true);

        // query to get all the available countries and their code
        $observer_query->select(
            $db->quoteName('id') . ', '
            . 'concat(' . $db->quoteName('first_name') . ', \' \', '
            . $db->quoteName('last_name') . ') as name'
        );
        $observer_query->from($db->quoteName('observer'));
        $observer_query->order($db->quoteName('name'));

        $db->setQuery($observer_query);

        // try to execute the query and return the results
        try {
            return $this->structureObservers($db->loadObjectList(), $current_observer);
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
    private function structureObservers($database_data, $current_observer) {
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
     * Function gets all the information from the database related to the location
     * with its id equal to $location_id (arg)
     *
     * @param $location_id  int         id of the location to get information about
     * @return              array|int   -1 on fail, array with location info on success
     *
     * @since 0.4.2
     */
    public function getLocation($location_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $location_query = $db->getQuery(true);

        // query to get the location information
        $location_query->select(
            $db->quoteName('observer_id') . ', '
            . $db->quoteName('location_code') . ', '
            . $db->quoteName('name') . ', '
            . $db->quoteName('status') . ', '
            . $db->quoteName('transfer_type') . ', '
            . $db->quoteName('country_code') . ', '
            . $db->quoteName('longitude') . ', '
            . $db->quoteName('latitude') . ', '
            . $db->quoteName('comments') . ', '
            . $db->quoteName('ftp_password') . ', '
            . $db->quoteName('tv_id') . ', '
            . $db->quoteName('tv_password')
        );
        $location_query->from($db->quoteName('location'));
        $location_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($location_id)
        );

        $db->setQuery($location_query);

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
}
