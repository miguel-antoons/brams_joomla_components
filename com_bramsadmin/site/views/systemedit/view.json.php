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
 * Class inserts or updates a system and generates
 * a JSON response for front-end.
 * @since 0.0.1
 */
class BramsAdminViewSystemEdit extends HtmlView {
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
     * Function is the entrypoint to create a new system. It calls the
     * according method from the model and returns a json response to
     * front-end.
     *
     * @since 0.2.0
     */
    public function create() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get the new system's information from request data
        $new_system_info = $input->get('newSystemInfo', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->insertSystem($new_system_info)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New system ' . $new_system_info['name'] . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to a system update. It calls the
     * according methods from the model and returns a json response
     * to the front-end.
     *
     *  @since 0.2.0
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get the new system's information from request data
        $system_info = $input->get('systemInfo', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database update fails
        if (($model->updateSystem($system_info)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'System ' . $system_info['name'] . ' has been updated.')
        );
    }

    /**
     * Function is the entrypoint to get specific system information.
     * It calls the method to get all the information about one system.
     * The system it will request information for is given in the api url (id).
     *
     * @since 0.3.0
     */
    public function getSystem() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($response = $model->getSystemInfo($id)) === -1) {
            return;
        }

        echo new JResponseJson($response[0]);
    }

    /**
     * Function is the entrypoint to get all the antennas grouped by location.
     * This function is called when front-end needs all the antennas grouped
     * by location.
     *
     * @since 0.3.0
     */
    public function getLocationAntennas() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $location_id = (int) $input->get('locationid');
        $antenna = (int) $input->get('antenna');
        $model = $this->getModel();

        // if the database select failed
        if (($locations = $model->getLocations($antenna, $location_id)) === -1) {
            return;
        }

        // if a valid location_id was given, set a default location to be selected
        if ($location_id !== -1) {
            $locations[$location_id]->selected = 'selected';
        }

        echo new JResponseJson($locations);
    }

    /**
     * Function is the entrypoint to get all the system names. It was initially
     * created to provide system names for front-end.
     *
     * @since 0.3.0
     */
    public function getSystemNames() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($response = $model->getSystemNames($id)) === -1) {
            return;
        }

        echo new JResponseJson($response);
    }
}
