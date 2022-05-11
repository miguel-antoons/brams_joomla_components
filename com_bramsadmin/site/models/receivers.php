<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Receivers Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * receivers.
 *
 * @since  0.8.1
 */
class BramsAdminModelReceivers extends BaseDatabaseModel {
    // array contains various receiver messages (could be moved to database if a lot of messages are required)
    public $receiver_messages = array(
        // default message (0) is empty
        (0) => array(
            ('message')     => '',
            ('css_class')   => ''
        ),
        (1) => array(
            ('message')     => 'Receiver was successfully updated',
            ('css_class')   => 'success'
        ),
        (2) => array(
            ('message')     => 'Receiver was successfully created',
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
     * Function gets receiver information from the BRAMS database. The information
     * it requests is the following : (id, brand, model and receiver_code).
     *
     * @returns int|array -1 if an error occurs, else the array with receiver info
     * @since 0.8.1
     */
    public function getReceivers() {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);
        $sub_receiver_query = $db->getQuery(true);

        // query to check if there are any systems for a given receiver
        $sub_receiver_query->select($db->quoteName('receiver_id'));
        $sub_receiver_query->from($db->quoteName('radsys_system'));
        $sub_receiver_query->where(
            $db->quoteName('receiver_id') . ' = ' . $db->quoteName('radsys_receiver.id') . ' limit 1'
        );

        // SQL query to get all information about the multiple receivers
        $receiver_query->select(
            'distinct ' . $db->quoteName('id')      . ', '
            . $db->quoteName('brand')               . ', '
            . $db->quoteName('receiver_code')       . 'as code, '
            . $db->quoteName('model')               . ', '
            . 'exists(' . $sub_receiver_query . ')' . ' as notDeletable'
        );
        $receiver_query->from($db->quoteName('radsys_receiver'));

        $db->setQuery($receiver_query);

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
     * Function deletes receiver with receiver.id equal to $id (arg)
     *
     * @param $id   int                 id of the receiver that has to be deleted
     * @return      int|JDatabaseDriver on fail returns -1, on success returns JDatabaseDriver
     *
     * @since 0.8.1
     */
    public function deleteReceiver($id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);

        // delete condition
        $condition = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        // delete query
        $receiver_query->delete($db->quoteName('radsys_receiver'));
        $receiver_query->where($condition);

        $db->setQuery($receiver_query);

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
     * Function gets all the available receiver codes from the database except
     * the receiver code whose id is equal to $id (arg)
     *
     * @param $id   int         id of the receiver not to take the receiver code from
     * @return      int|array   -1 if the function fails, the array with receiver codes on success
     *
     * @since 0.8.1
     */
    public function getReceiverCodes($id = -1) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);

        // query to get all the receiver codes and ids
        $receiver_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('receiver_code')
        );
        $receiver_query->from($db->quoteName('radsys_receiver'));

        $db->setQuery($receiver_query);

        // try to execute the query and return the results
        try {
            return $this->structureReceivers($db->loadObjectList(), $id);
        } catch (RuntimeException $e) {
            // on fail, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function filters the receiver whose id is equal to $id from the database
     * results and also transforms an array of stdClasses into a simple array.
     *
     * @param $database_data    array   receiver codes from the database
     * @param $id               int     id to filter
     * @return                  array   array of strings with all receiver codes except the one with id = $id
     *
     * @since 0.8.1
     */
    private function structureReceivers($database_data, $id) {
        $final_receiver_array = array();
        foreach ($database_data as $receiver) {
            // if the receiver id is not equal to $id (arg)
            if ($receiver->id !== $id) {
                // add the receiver code to the codes array
                $final_receiver_array[] = $receiver->receiver_code;
            }
        }

        return $final_receiver_array;
    }

    /**
     * Function gets all the information from the database related to the receiver
     * with its id equal to $receiver_id (arg)
     *
     * @param $receiver_id  int         id of the receiver to get information about
     * @return              array|int   -1 on fail, array with receiver info on success
     *
     * @since 0.8.1
     */
    public function getReceiver($receiver_id) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);

        // query to get the receiver information
        $receiver_query->select(
            $db->quoteName('receiver_code') . ' as code, '
            . $db->quoteName('brand')       . ', '
            . $db->quoteName('model')       . ', '
            . $db->quoteName('comments')
        );
        $receiver_query->from($db->quoteName('radsys_receiver'));
        $receiver_query->where(
            $db->quoteName('id') . ' = ' . $db->quote($receiver_id)
        );

        $db->setQuery($receiver_query);

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
     * Function inserts a new receiver into the database. The attributes of the new
     * value are given as argument ($receiver_info)
     *
     * @param $receiver_info    array               array with the attributes of the new receiver
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.7.2
     */
    public function newReceiver($receiver_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);

        // query to insert a new receiver with data being the $receiver_info arg
        $receiver_query
            ->insert($db->quoteName('radsys_receiver'))
            ->columns(
                $db->quoteName(
                    array(
                        'receiver_code',
                        'brand',
                        'model',
                        'comments'
                    )
                )
            )
            ->values(
                $db->quote($receiver_info['code'])      . ', '
                . $db->quote($receiver_info['brand'])   . ', '
                . $db->quote($receiver_info['model'])   . ', '
                . $db->quote($receiver_info['comments'])
            );

        $db->setQuery($receiver_query);

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
     * Function updates a receiver from the database with values from the
     * $receiver_info argument.
     *
     * @param $receiver_info    array               array with the attributes of the modified receiver
     * @return                  int|JDatabaseDriver -1 on fail, JDatabaseDriver on success
     *
     * @since 0.8.1
     */
    public function updateReceiver($receiver_info) {
        // if database connection fails, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $receiver_query = $db->getQuery(true);
        // attributes to update with their new values
        $fields = array(
            $db->quoteName('receiver_code') . ' = ' . $db->quote($receiver_info['code']),
            $db->quoteName('brand')         . ' = ' . $db->quote($receiver_info['brand']),
            $db->quoteName('model')         . ' = ' . $db->quote($receiver_info['model']),
            $db->quoteName('comments')      . ' = ' . $db->quote($receiver_info['comments'])
        );

        // receiver to be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($receiver_info['id'])
        );

        // update query
        $receiver_query
            ->update($db->quoteName('radsys_receiver'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($receiver_query);

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
