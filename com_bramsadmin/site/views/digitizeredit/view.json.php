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
 * Class inserts or updates an antenna and generates
 * a JSON response for front-end.
 * @since 0.8.2
 */
class BramsAdminViewDigitizerEdit extends HtmlView {
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
     * Function is the entrypoint to get all the digitizer codes.
     * This function returns a json array containing all the digitizer
     * codes to the front-end.
     *
     * @since 0.8.2
     */
    public function getCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $digitizer_id = $input->get('digitizerId');
        $model = $this->getModel();

        // if the database select failed
        if (($digitizer_codes = $model->getDigitizerCodes($digitizer_id)) === -1) {
            return;
        }

        echo new JResponseJson($digitizer_codes);
    }

    /**
     * Function is the entrypoint to get all the information about a specific digitizer.
     * This function returns a JSON object with the attributes being the information
     * about the requested digitizer.
     *
     * @return void
     *
     * @since 0.8.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $digitizer_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($digitizer_info = $model->getDigitizer($digitizer_id)) === -1) {
            return;
        }

        echo new JResponseJson($digitizer_info[0]);
    }

    /**
     * Function is the entrypoint to create a new digitizer.
     * This function calls the model method to insert a new digitizer
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.8.2
     */
    public function new() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the location info from the post request
        $new_digitizer = $input->get('new_digitizer', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newDigitizer($new_digitizer)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New antenna '
                . $new_digitizer['code']
                . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update a digitizer.
     * This function calls the model method to update a digitizer
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.8.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the location info from the post request
        $modified_digitizer = $input->get('modified_digitizer', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateDigitizer($modified_digitizer)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Antenna '
                . $modified_digitizer['code']
                . ' has been updated.')
        );
    }
}
