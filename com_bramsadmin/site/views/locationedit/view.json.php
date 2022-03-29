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
 * Class inserts or updates a location and generates
 * a JSON response for front-end.
 * @since 0.4.2
 */
class BramsAdminViewLocationEdit extends HtmlView {
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
     * Function is the entrypoint to get all the location codes.
     * This function returns a json array containing all the location
     * codes to the front-end.
     *
     * @since 0.4.2
     */
    public function getLocationCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $location_id = (int) $input->get('locationid');
        $model = $this->getModel();

        // if the database select failed
        if (($locations = $model->getLocationCodes($location_id)) === -1) {
            return;
        }

        echo new JResponseJson($locations);
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
     * Function is the entrypoint to get all the observers form the database.
     * This function returns a JSON array with objects. Each object is a different
     * observer.
     *
     * @return void
     *
     * @since 0.4.2
     */
    public function getObservers() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $current_observer = $input->get('currentObserver');
        $model = $this->getModel();

        // if the database select failed
        if (($observers = $model->getObservers($current_observer)) === -1) {
            return;
        }

        echo new JResponseJson($observers);
    }

    /**
     * Function is the entrypoint to get all the information about a specific location.
     * This function returns a JSON object with the attributes being the information
     * about the requested location.
     *
     * @return void
     *
     * @since 0.4.2
     */
    public function getLocation() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $location_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($location_info = $model->getLocation($location_id)) === -1) {
            return;
        }

        echo new JResponseJson($location_info[0]);
    }
}
