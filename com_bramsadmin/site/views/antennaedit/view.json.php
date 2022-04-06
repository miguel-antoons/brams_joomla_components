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
 * @since 0.7.2
 */
class BramsAdminViewAntennaEdit extends HtmlView {
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
     * Function is the entrypoint to get all the antenna codes.
     * This function returns a json array containing all the antenna
     * codes to the front-end.
     *
     * @since 0.7.2
     */
    public function getCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested antenna
        $antenna_id = $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($antenna_codes = $model->getAntennaCodes($antenna_id)) === -1) {
            return;
        }

        echo new JResponseJson($antenna_codes);
    }

    /**
     * Function is the entrypoint to get all the information about a specific antenna.
     * This function returns a JSON object with the attributes being the information
     * about the requested antenna.
     *
     * @return void
     *
     * @since 0.7.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $antenna_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($antenna_info = $model->getAntenna($antenna_id)) === -1) {
            return;
        }

        echo new JResponseJson($antenna_info[0]);
    }

    /**
     * Function is the entrypoint to create a new antenna.
     * This function calls the model method to insert a new antenna
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.7.2
     */
    public function create() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the antenna info from the post request
        $new_antenna = $input->get('new_antenna', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newAntenna($new_antenna)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New antenna '
                . $new_antenna['brand']
                . ' ' . $new_antenna['model']
                . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update an antenna.
     * This function calls the model method to update an antenna
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.7.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the antenna info from the post request
        $modified_antenna = $input->get('modified_antenna', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateAntenna($modified_antenna)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Antenna '
                . $modified_antenna['brand']
                . ' ' . $modified_antenna['model']
                . ' has been updated.')
        );
    }
}
