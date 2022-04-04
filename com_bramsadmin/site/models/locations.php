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
            'distinct ' . $db->quoteName('location.id') . ' as id, '
            . $db->quoteName('location_code') . ', '
            . $db->quoteName('location.name') . ' as name, '
            . $db->quoteName('longitude') . ', '
            . $db->quoteName('latitude') . ', '
            . $db->quoteName('transfer_type') . ', '
            . $db->quoteName('observer.id') . ' as obs_id, '
            . 'concat(' . $db->quoteName('first_name') . ', \' \', '
            . $db->quoteName('last_name') . ') as obs_name, '
            . $db->quoteName('ftp_password') . ', '
            . $db->quoteName('tv_id') . ', '
            . $db->quoteName('tv_password') . ', '
            . $db->quoteName('system.location_id') . ' as not_deletable'
        );
        $locations_query->from($db->quoteName('observer'));
        $locations_query->join(
            'INNER',
            $db->quoteName('location')
            . ' ON '
            . $db->quoteName('observer.id')
            . ' = '
            . $db->quoteName('location.observer_id')
        );
        $locations_query->join(
            'LEFT',
            $db->quoteName('system')
            . ' ON '
            . $db->quoteName('location.id')
            . ' = '
            . $db->quoteName('system.location_id')
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

    /**
     * Function inserts a new location int the database. The attributes of the new
     * value are given as argument ($location_info)
     *
     * @param $location_info    array               array with the attributes of the new location
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.4.3
     */
    public function newLocation($location_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $location_query = $db->getQuery(true);

        // query to insert a new location with data being the $location_info arg
        $location_query
            ->insert($db->quoteName('location'))
            ->columns(
                $db->quoteName(
                    array(
                        'observer_id',
                        'location_code',
                        'name',
                        'status',
                        'transfer_type',
                        'country_code',
                        'longitude',
                        'latitude',
                        'comments',
                        'ftp_password',
                        'tv_id',
                        'tv_password'
                    )
                )
            )
            ->values(
                $db->quote($location_info['observer_id']) . ', '
                . $db->quote($location_info['code']) . ', '
                . $db->quote($location_info['name']) . ', '
                . $db->quote($location_info['status']) . ', '
                . $db->quote($location_info['transfer_type']) . ', '
                . $db->quote($location_info['country']) . ', '
                . $db->quote($location_info['longitude']) . ', '
                . $db->quote($location_info['latitude']) . ', '
                . $db->quote($location_info['comments']) . ', '
                . $db->quote($location_info['ftp_pass']) . ', '
                . $db->quote($location_info['tv_id']) . ', '
                . $db->quote($location_info['tv_pass'])
            );

        $db->setQuery($location_query);

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
     * Function updates a location from the database with values from the
     * $location_info argument.
     *
     * @param $location_info    array               array with the attributes of the modified location
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.4.3
     */
    public function updateLocation($location_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $location_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('observer_id')   . ' = ' . $db->quote($location_info['observer_id']),
            $db->quoteName('location_code') . ' = ' . $db->quote($location_info['code']),
            $db->quoteName('name')          . ' = ' . $db->quote($location_info['name']),
            $db->quoteName('status')        . ' = ' . $db->quote($location_info['status']),
            $db->quoteName('transfer_type') . ' = ' . $db->quote($location_info['transfer_type']),
            $db->quoteName('country_code')  . ' = ' . $db->quote($location_info['country']),
            $db->quoteName('longitude')     . ' = ' . $db->quote($location_info['longitude']),
            $db->quoteName('latitude')      . ' = ' . $db->quote($location_info['latitude']),
            $db->quoteName('comments')      . ' = ' . $db->quote($location_info['comments']),
            $db->quoteName('ftp_password')  . ' = ' . $db->quote($location_info['ftp_pass']),
            $db->quoteName('tv_id')         . ' = ' . $db->quote($location_info['tv_id']),
            $db->quoteName('tv_password')   . ' = ' . $db->quote($location_info['tv_pass'])
        );

        // location to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($location_info['id'])
        );

        // update query
        $location_query
            ->update($db->quoteName('location'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($location_query);

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
