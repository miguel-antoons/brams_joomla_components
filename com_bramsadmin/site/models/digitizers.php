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
 * Digitizers Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * digitizers.
 *
 * @since  0.8.1
 */
class BramsAdminModelDigitizers extends BaseDatabaseModel {
    // array contains various system messages (could be moved to database if a lot of messages are required)
    public $digitizer_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Digitizer was successfully updated.',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Digitizer was successfully created.',
            ('css_class')   => 'success'
        )
    );

    // function connects to the database and returns the database object
    private function connectToDatabase() {
        try {
            /* Below lines are for connecting to production database later on */
	        $database_options = getDatabaseInfo();
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
        $sub_digitizer_query = $db->getQuery(true);

        // query to check if there are any systems for a given digitizer
        $sub_digitizer_query->select($db->quoteName('digitizer_id'));
        $sub_digitizer_query->from($db->quoteName('radsys_system'));
        $sub_digitizer_query->where(
            $db->quoteName('digitizer_id') . ' = ' . $db->quoteName('radsys_digitizer.id') . ' limit 1'
        );

        // SQL query to get all information about the multiple systems
        $digitizer_query->select(
            'distinct ' . $db->quoteName('id')          . ', '
            . $db->quoteName('brand')                   . ', '
            . $db->quoteName('model')                   . ', '
            . $db->quoteName('digitizer_code')          . ' as code, '
            . $db->quoteName('comments')                . ', '
            . 'exists(' . $sub_digitizer_query . ')'    . ' as notDeletable'
        );
        $digitizer_query->from($db->quoteName('radsys_digitizer'));

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
     * Function gets all the available digitizer codes from the database except
     * the digitizer code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the digitizer not to take the digitizer code from
     * @return      int|array   -1 if the function fails, the array with digitizer codes on success
     *
     * @since 0.8.2
     */
    public function getDigitizerCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);

        // query to get all the location codes and ids
        $digitizer_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('digitizer_code')
        );
        $digitizer_query->from($db->quoteName('radsys_digitizer'));

        $db->setQuery($digitizer_query);

        // try to execute the query and return the results
        try {
            return $this->structureDigitizers($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the digitizer whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   digitizer codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all digitizer codes except the one with id = $id
     *
     * @since 0.8.2
     */
    private function structureDigitizers($database_data, $id) {
        $final_digitizer_array = array();
        foreach ($database_data as $digitizer) {
            // if the digitizer id is not equal to $id (arg)
            if ($digitizer->id !== $id) {
                // add the location code to the location codes array
                $final_digitizer_array[] = $digitizer->digitizer_code;
            }
        }

        return $final_digitizer_array;
    }

    /**
     * Function gets all the information from the database related to the digitizer
     * with its id equal to $digitizer_id (arg)
     *
     * @param $digitizer_id int         id of the digitizer to get information about
     * @return              array|int   -1 on fail, array with digitizer info on success
     *
     * @since 0.8.2
     */
    public function getDigitizer($digitizer_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);

        // query to get the digitizer information
        $digitizer_query->select(
            $db->quoteName('digitizer_code')    . ' as code, '
            . $db->quoteName('brand')           . ', '
            . $db->quoteName('model')           . ', '
            . $db->quoteName('comments')
        );
        $digitizer_query->from($db->quoteName('radsys_digitizer'));
        $digitizer_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($digitizer_id)
        );

        $db->setQuery($digitizer_query);

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
     * Function inserts a new digitizer into the database. The attributes of the new
     * value are given as argument ($digitizer_info)
     *
     * @param $digitizer_info   array               array with the attributes of the new digitizer
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.8.2
     */
    public function newDigitizer($digitizer_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);

        // query to insert a new digitizer with data being the $digitizer_info arg
        $digitizer_query
            ->insert($db->quoteName('radsys_digitizer'))
            ->columns(
                $db->quoteName(
                    array(
                        'digitizer_code',
                        'brand',
                        'model',
                        'comments'
                    )
                )
            )
            ->values(
                $db->quote($digitizer_info['code'])     . ', '
                . $db->quote($digitizer_info['brand'])  . ', '
                . $db->quote($digitizer_info['model'])  . ', '
                . $db->quote($digitizer_info['comments'])
            );

        $db->setQuery($digitizer_query);

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
     * Function updates a digitizer from the database with values from the
     * $digitizer_info argument.
     *
     * @param $digitizer_info   array               array with the attributes of the modified digitizer
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.8.2
     */
    public function updateDigitizer($digitizer_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $digitizer_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('digitizer_code'). ' = ' . $db->quote($digitizer_info['code']),
            $db->quoteName('brand')         . ' = ' . $db->quote($digitizer_info['brand']),
            $db->quoteName('model')         . ' = ' . $db->quote($digitizer_info['model']),
            $db->quoteName('comments')      . ' = ' . $db->quote($digitizer_info['comments'])
        );

        // location to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($digitizer_info['id'])
        );

        // update query
        $digitizer_query
            ->update($db->quoteName('radsys_digitizer'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($digitizer_query);

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
