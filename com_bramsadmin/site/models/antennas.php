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
 * Antennas Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * antennas.
 *
 * @since  0.7.1
 */
class BramsAdminModelAntennas extends BaseDatabaseModel {
    // array contains various antenna messages (could be moved to database if a lot of messages are required)
    public $antenna_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Antenna was successfully updated',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Antenna was successfully created',
            ('css_class')   => 'success'
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
        $sub_receiver_query = $db->getQuery(true);

        // query to check if there are any systems for a given antenna
        $sub_receiver_query->select($db->quoteName('antenna_id'));
        $sub_receiver_query->from($db->quoteName('radsys_system'));
        $sub_receiver_query->where(
            $db->quoteName('antenna_id') . ' = ' . $db->quoteName('radsys_antenna.id') . ' limit 1'
        );

        // SQL query to get all information about the multiple antennas
        $antenna_query->select(
            'distinct ' . $db->quoteName('id')      . ', '
            . $db->quoteName('brand')               . ', '
            . $db->quoteName('antenna_code')        . 'as code, '
            . $db->quoteName('model')               . ', '
            . 'exists(' . $sub_receiver_query . ')' . ' as notDeletable'
        );
        $antenna_query->from($db->quoteName('radsys_antenna'));

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

    /**
     * Function gets all the available antenna codes from the database except
     * the antenna code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the antenna not to take the antenna code from
     * @return      int|array   -1 if the function fails, the array with antenna codes on success
     *
     * @since 0.7.2
     */
    public function getAntennaCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);

        // query to get all the antenna codes and ids
        $antenna_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('antenna_code')
        );
        $antenna_query->from($db->quoteName('radsys_antenna'));

        $db->setQuery($antenna_query);

        // try to execute the query and return the results
        try {
            return $this->structureAntennas($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the antenna whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   antenna codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all antennas codes except the one with id = $id
     *
     * @since 0.7.2
     */
    private function structureAntennas($database_data, $id) {
        $final_antenna_array = array();
        foreach ($database_data as $antenna) {
            // if the antenna id is not equal to $id (arg)
            if ($antenna->id !== $id) {
                // add the antenna code to the codes array
                $final_antenna_array[] = $antenna->antenna_code;
            }
        }

        return $final_antenna_array;
    }

    /**
     * Function gets all the information from the database related to the antenna
     * with its id equal to $antenna_id (arg)
     *
     * @param $antenna_id   int         id of the antenna to get information about
     * @return              array|int   -1 on fail, array with antenna info on success
     *
     * @since 0.7.2
     */
    public function getAntenna($antenna_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);

        // query to get the antenna information
        $antenna_query->select(
            $db->quoteName('antenna_code')  . ' as code, '
            . $db->quoteName('brand')       . ', '
            . $db->quoteName('model')       . ', '
            . $db->quoteName('comments')
        );
        $antenna_query->from($db->quoteName('radsys_antenna'));
        $antenna_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($antenna_id)
        );

        $db->setQuery($antenna_query);

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
     * Function inserts a new antenna into the database. The attributes of the new
     * value are given as argument ($antenna_info)
     *
     * @param $antenna_info     array               array with the attributes of the new antenna
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.7.2
     */
    public function newAntenna($antenna_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);

        // query to insert a new antenna with data being the $antenna_info arg
        $antenna_query
            ->insert($db->quoteName('radsys_antenna'))
            ->columns(
                $db->quoteName(
                    array(
                        'antenna_code',
                        'brand',
                        'model',
                        'comments'
                    )
                )
            )
            ->values(
                $db->quote($antenna_info['code'])       . ', '
                . $db->quote($antenna_info['brand'])    . ', '
                . $db->quote($antenna_info['model'])    . ', '
                . $db->quote($antenna_info['comments'])
            );

        $db->setQuery($antenna_query);

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
     * Function updates an antenna from the database with values from the
     * $antenna_info argument.
     *
     * @param $antenna_info     array               array with the attributes of the modified antenna
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.7.2
     */
    public function updateAntenna($antenna_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $antenna_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('antenna_code')  . ' = ' . $db->quote($antenna_info['code']),
            $db->quoteName('brand')         . ' = ' . $db->quote($antenna_info['brand']),
            $db->quoteName('model')         . ' = ' . $db->quote($antenna_info['model']),
            $db->quoteName('comments')      . ' = ' . $db->quote($antenna_info['comments'])
        );

        // antenna to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($antenna_info['id'])
        );

        // update query
        $antenna_query
            ->update($db->quoteName('radsys_antenna'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($antenna_query);

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
