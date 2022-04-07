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
 * Class inserts or updates a software trough the software model
 * and generates a JSON response for front-end.
 * @since 0.10.2
 */
class BramsAdminViewSoftwareEdit extends HtmlView {
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
     * Function is the entrypoint to get all the software codes.
     * This function returns a json array containing all the software
     * codes to the front-end.
     *
     * @since 0.10.2
     */
    public function getCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested antenna
        $software_id = $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($software_codes = $model->getSoftwareCodes($software_id)) === -1) {
            return;
        }

        echo new JResponseJson($software_codes);
    }

    /**
     * Function is the entrypoint to get all the information about a specific software.
     * This function returns a JSON object with the attributes being the information
     * about the requested software.
     *
     * @return void
     *
     * @since 0.10.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $software_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($software_info = $model->getSoftware($software_id)) === -1) {
            return;
        }

        echo new JResponseJson($software_info[0]);
    }

    /**
     * Function is the entrypoint to create a new software entry.
     * This function calls the model method to insert a new software
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.10.2
     */
    public function create() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the software info from the post request
        $new_software = $input->get('new_software', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newSoftware($new_software)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New software '
                . $new_software['code']
                . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update a software entry.
     * This function calls the model method to update a software
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.10.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the software info from the post request
        $modified_software = $input->get('modified_software', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateSoftware($modified_software)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Software '
                . $modified_software['code']
                . ' has been updated.')
        );
    }
}
