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
 * Class inserts or updates a beacon and generates
 * a JSON response for front-end.
 * @since 0.6.2
 */
class BramsAdminViewBeaconEdit extends HtmlView {
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
     * Function is the entrypoint to get all the beacon codes.
     * This function returns a json array containing all the beacon
     * codes to the front-end.
     *
     * @since 0.6.2
     */
    public function getBeaconCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $beacon_id = $input->get('beaconId');
        $model = $this->getModel();

        // if the database select failed
        if (($beacon_codes = $model->getBeaconCodes($beacon_id)) === -1) {
            return;
        }

        echo new JResponseJson($beacon_codes);
    }

    /**
     * Function is the entrypoint to get all the countries.
     * This function returns a json array with all the countries
     * to the front-end of the site.
     *
     * @since 0.4.2
     */
    public function getCountries() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // check which country has to be selected
        $current_country = $input->get('currentCountry');
        $model = $this->getModel();

        // if the database select failed
        if (($countries = $model->getCountries($current_country)) === -1) {
            return;
        }

        echo new JResponseJson($countries);
    }

    /**
     * Function is the entrypoint to get all the information about a specific beacon.
     * This function returns a JSON object with the attributes being the information
     * about the requested beacon.
     *
     * @return void
     *
     * @since 0.6.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $beacon_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($beacon_info = $model->getBeacon($beacon_id)) === -1) {
            return;
        }

        echo new JResponseJson($beacon_info[0]);
    }

    /**
     * Function is the entrypoint to create a new beacon.
     * This function calls the model method to insert a new beacon
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.6.2
     */
    public function new() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the beacon info from the post request
        $new_beacon = $input->get('new_beacon', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newBeacon($new_beacon)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New beacon ' . $new_beacon['name'] . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update a beacon.
     * This function calls the model method to update a beacon
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.6.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the beacon info from the post request
        $modified_beacon = $input->get('modified_beacon', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateBeacon($modified_beacon)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Beacon ' . $modified_beacon['name'] . ' has been updated.')
        );
    }
}
