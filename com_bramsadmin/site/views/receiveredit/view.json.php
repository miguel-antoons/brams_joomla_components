<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Class inserts or updates a receiver and generates
 * a JSON response for front-end.
 * @since 0.9.2
 */
class BramsAdminViewReceiverEdit extends HtmlView {
    /**
     * Function makes sure to get the application input. If it fails, it
     * will return false
     *
     * @return boolean|JInput
     * @since 0.2.5
     */
    private function getAppInput() {
        try {
            return Factory::getApplication()->input;
        } catch (Exception $e) {
            // if an exception occurs, return false to front-end
            echo new JResponseJson(array(('message') => $e));
            // log the exception
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    /**
     * Function is the entrypoint to get all the receiver codes.
     * This function returns a json array containing all the receiver
     * codes to the front-end.
     *
     * @since 0.9.2
     */
    public function getCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested receiver
        $receiver_id = $input->get('receiverId');
        $model = $this->getModel();

        // if the database select failed
        if (($receiver_codes = $model->getReceiverCodes($receiver_id)) === -1) {
            return;
        }

        echo new JResponseJson($receiver_codes);
    }

    /**
     * Function is the entrypoint to get all the information about a specific receiver.
     * This function returns a JSON object with the attributes being the information
     * about the requested receiver.
     *
     * @return void
     *
     * @since 0.9.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $receiver_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($receiver_info = $model->getReceiver($receiver_id)) === -1) {
            return;
        }

        echo new JResponseJson($receiver_info[0]);
    }

    /**
     * Function is the entrypoint to create a new receiver.
     * This function calls the model method to insert a new receiver
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.9.2
     */
    public function new() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the receiver info from the post request
        $new_receiver = $input->get('new_receiver', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newReceiver($new_receiver)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New receiver '
                . $new_receiver['code']
                . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update a receiver.
     * This function calls the model method to update a receiver
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.9.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the receiver info from the post request
        $modified_receiver = $input->get('modified_receiver', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateReceiver($modified_receiver)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Receiver '
                . $modified_receiver['code']
                . ' has been updated.')
        );
    }
}
