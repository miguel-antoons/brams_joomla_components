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
 * Softwares Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * software.
 *
 * @since  0.7.1
 */
class BramsAdminModelSoftwares extends BaseDatabaseModel {
    // array contains various software messages (could be moved to database if a lot of messages are required)
    public $software_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Software was successfully updated',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Software was successfully created',
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
     * Function gets software information from the BRAMS database. The information
     * it requests is the following : (id, name, version and software code).
     *
     * @returns int|array -1 if an error occurred, else the array with software info
     * @since 0.10.1
     */
    public function getSoftwares() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);
        $sub_software_query = $db->getQuery(true);

        // query to check if there are any systems for a given software
        $sub_software_query->select($db->quoteName('software_id'));
        $sub_software_query->from($db->quoteName('radsys_system'));
        $sub_software_query->where(
            $db->quoteName('software_id') . ' = ' . $db->quoteName('radsys_software.id') . ' limit 1'
        );

        // SQL query to get all information about the multiple software
        $software_query->select(
            'distinct ' . $db->quoteName('id')      . ', '
            . $db->quoteName('name')                . ', '
            . $db->quoteName('software_code')       . ' as code, '
            . $db->quoteName('version')             . ', '
            . 'exists(' . $sub_software_query . ')' . ' as notDeletable'
        );
        $software_query->from($db->quoteName('radsys_software'));

        $db->setQuery($software_query);

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
     * Function deletes software with software.id equal to $id (arg)
     *
     * @param $id   int                 id of the software that has to be deleted
     * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.10.1
     */
    public function deleteSoftware($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);

        // delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $software_query->delete($db->quoteName('radsys_software'));
        $software_query->where($condition);

        $db->setQuery($software_query);

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
     * Function gets all the available software codes from the database except
     * the software code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the software not to take the software code from
     * @return      int|array   -1 if the function fails, the array with software codes on success
     *
     * @since 0.10.1
     */
    public function getSoftwareCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);

        // query to get all the software codes and id's
        $software_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('software_code')
        );
        $software_query->from($db->quoteName('radsys_software'));

        $db->setQuery($software_query);

        // try to execute the query and return the results
        try {
            return $this->structureSoftwares($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the software whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   software codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all software codes except the one with id = $id
     *
     * @since 0.10.1
     */
    private function structureSoftwares($database_data, $id) {
        $final_software_array = array();
        foreach ($database_data as $software) {
            // if the software id is not equal to $id (arg)
            if ($software->id !== $id) {
                // add the software code to the codes array
                $final_software_array[] = $software->software_code;
            }
        }

        return $final_software_array;
    }

    /**
     * Function gets all the information from the database related to the software
     * with its id equal to $software_id (arg)
     *
     * @param $software_id   int        id of the software to get information about
     * @return              array|int   -1 on fail, array with antenna info on success
     *
     * @since 0.10.1
     */
    public function getSoftware($software_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);

        // query to get the software information
        $software_query->select(
            $db->quoteName('software_code') . ' as code, '
            . $db->quoteName('name')        . ', '
            . $db->quoteName('version')     . ', '
            . $db->quoteName('comments')
        );
        $software_query->from($db->quoteName('radsys_software'));
        $software_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($software_id)
        );

        $db->setQuery($software_query);

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
     * Function inserts a new software into the database. The attributes of the new
     * value are given as argument ($software_info)
     *
     * @param $software_info    array               array with the attributes of the new software
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.10.1
     */
    public function newSoftware($software_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);

        // query to insert a new software with data being the $software_info arg
        $software_query
            ->insert($db->quoteName('radsys_software'))
            ->columns(
                $db->quoteName(
                    array(
                        'software_code',
                        'name',
                        'version',
                        'comments'
                    )
                )
            )
            ->values(
                $db->quote($software_info['code'])      . ', '
                . $db->quote($software_info['name'])    . ', '
                . $db->quote($software_info['version']) . ', '
                . $db->quote($software_info['comments'])
            );

        $db->setQuery($software_query);

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
     * Function updates a software from the database with values from the
     * $software_info argument.
     *
     * @param $software_info     array              array with the attributes of the modified software
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.10.1
     */
    public function updateSoftware($software_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $software_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('software_code') . ' = ' . $db->quote($software_info['code']),
            $db->quoteName('name')          . ' = ' . $db->quote($software_info['name']),
            $db->quoteName('version')       . ' = ' . $db->quote($software_info['version']),
            $db->quoteName('comments')      . ' = ' . $db->quote($software_info['comments'])
        );

        // software to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($software_info['id'])
        );

        // update query
        $software_query
            ->update($db->quoteName('radsys_software'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($software_query);

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
