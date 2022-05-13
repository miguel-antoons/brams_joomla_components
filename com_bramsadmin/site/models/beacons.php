<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * Beacons Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * beacons.
 *
 * @since  0.6.1
 */
class BramsAdminModelBeacons extends BaseDatabaseModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $beacon_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Beacon was successfully updated',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Beacon was successfully created',
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
            // return $this->getDbo();
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
        $sub_beacon_query = $db->getQuery(true);

        // query to check if there are any files for a given system
        $sub_beacon_query->select($db->quoteName('beacon_id'));
        $sub_beacon_query->from($db->quoteName('file'));
        $sub_beacon_query->where(
            $db->quoteName('beacon_id') . ' = ' . $db->quoteName('beacon.id') . ' limit 1'
        );

        // SQL query to get all information about the multiple systems
        $beacon_query->select(
            $db->quoteName('beacon.id')             . 'as id, '
            . $db->quoteName('beacon.name')         . 'as name, '
            . $db->quoteName('latitude')            . ', '
            . $db->quoteName('longitude')           . ', '
            . $db->quoteName('frequency')           . ', '
            . $db->quoteName('power')               . ', '
            . 'exists(' . $sub_beacon_query . ')'   . ' as notDeletable'
        );
        $beacon_query->from($db->quoteName('beacon'));

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

    /**
     * Function gets all the available beacon codes from the database except
     * the beacon code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the beacon not to take the beacon code from
     * @return      int|array   -1 if the function fails, the array with beacon codes on success
     *
     * @since 0.6.2
     */
    public function getBeaconCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);

        // query to get all the location codes and ids
        $beacon_query->select(
            $db->quoteName('id') . ', '
            . $db->quoteName('beacon_code')
        );
        $beacon_query->from($db->quoteName('beacon'));

        $db->setQuery($beacon_query);

        // try to execute the query and return the results
        try {
            return $this->structureBeacons($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the beacon whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   beacon codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all beacon codes except the one with id = $id
     *
     * @since 0.6.2
     */
    private function structureBeacons($database_data, $id) {
        $final_beacon_array = array();
        foreach ($database_data as $beacon) {
            // if the beacon id is not equal to $id (arg)
            if ($beacon->id !== $id) {
                // add the beacon code to the beacon codes array
                $final_beacon_array[] = $beacon->beacon_code;
            }
        }

        return $final_beacon_array;
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
     * Function gets all the information from the database related to the beacon
     * with its id equal to $beacon_id (arg)
     *
     * @param $beacon_id  int           id of the beacon to get information about
     * @return              array|int   -1 on fail, array with beacon info on success
     *
     * @since 0.6.2
     */
    public function getBeacon($beacon_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);

        // query to get the location information
        $beacon_query->select(
            $db->quoteName('id')            . ', '
            . $db->quoteName('beacon_code') . ' as code, '
            . $db->quoteName('name')        . ', '
            . $db->quoteName('longitude')   . ', '
            . $db->quoteName('latitude')    . ', '
            // ? uncomment the following line to add a comments field
            // . $db->quoteName('comment')     . ', '
            . $db->quoteName('frequency')   . ', '
            . $db->quoteName('power')       . ', '
            . $db->quoteName('polarization')
        );
        $beacon_query->from($db->quoteName('beacon'));
        $beacon_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($beacon_id)
        );

        $db->setQuery($beacon_query);

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
     * Function inserts a new beacon into the database. The attributes of the new
     * value are given as argument ($beacon_info)
     *
     * @param $beacon_info      array               array with the attributes of the new beacon
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.6.2
     */
    public function newBeacon($beacon_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);

        // query to insert a new location with data being the $location_info arg
        $beacon_query
            ->insert($db->quoteName('beacon'))
            ->columns(
                $db->quoteName(
                    array(
                        'beacon_code',
                        'name',
                        'latitude',
                        'longitude',
                        'frequency',
                        'power',
                        'polarization'
                        // ? uncomment the following line(s) to add a comments field
                        // 'comments',
                    )
                )
            )
            ->values(
                $db->quote($beacon_info['code'])            . ', '
                . $db->quote($beacon_info['name'])          . ', '
                . $db->quote($beacon_info['latitude'])      . ', '
                . $db->quote($beacon_info['longitude'])     . ', '
                . $db->quote($beacon_info['frequency'])     . ', '
                . $db->quote($beacon_info['power'])         . ', '
                . $db->quote($beacon_info['polarization']) // . ', '
                // ? uncomment the following line(s) to add a comments field
                // . $db->quote($beacon_info['comments'])
            );

        $db->setQuery($beacon_query);

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
     * Function updates a beacon from the database with values from the
     * $beacon_info argument.
     *
     * @param $beacon_info      array               array with the attributes of the modified beacon
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.6.2
     */
    public function updateBeacon($beacon_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $beacon_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('beacon_code')   . ' = ' . $db->quote($beacon_info['code']),
            $db->quoteName('name')          . ' = ' . $db->quote($beacon_info['name']),
            $db->quoteName('latitude')      . ' = ' . $db->quote($beacon_info['latitude']),
            $db->quoteName('longitude')     . ' = ' . $db->quote($beacon_info['longitude']),
            $db->quoteName('frequency')     . ' = ' . $db->quote($beacon_info['frequency']),
            $db->quoteName('power')         . ' = ' . $db->quote($beacon_info['power']),
            $db->quoteName('polarization')  . ' = ' . $db->quote($beacon_info['polarization'])
            // ? uncomment the following line(s) to add a comments field
            // $db->quoteName('comments')      . ' = ' . $db->quote($beacon_info['comments']),
        );

        // location to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($beacon_info['id'])
        );

        // update query
        $beacon_query
            ->update($db->quoteName('beacon'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($beacon_query);

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
