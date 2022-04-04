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
 * @since 0.5.2
 */
class BramsAdminViewObserverEdit extends HtmlView {
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
     * Function is the entrypoint to get all the observer codes.
     * This function returns a json array containing all the observer
     * codes to the front-end.
     *
     * @since 0.5.2
     */
    public function getObserverCodes() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested observer
        $observer_id = $input->get('observerId');
        $model = $this->getModel();

        // if the database select fails
        if (($observer_codes = $model->getObserverCodes($observer_id)) === -1) {
            return;
        }

        echo new JResponseJson($observer_codes);
    }

    /**
     * Function is the entrypoint to get all the countries.
     * This function returns a json array with all the countries
     * to the front-end of the site.
     *
     * @since 0.5.2
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
     * Function is the entrypoint to get all the information about a specific observer.
     * This function returns a JSON object with the attributes being the information
     * about the requested observer.
     *
     * @return void
     *
     * @since 0.5.2
     */
    public function getOne() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $observer_id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database select failed
        if (($observer_info = $model->getObserver($observer_id)) === -1) {
            return;
        }

        echo new JResponseJson($observer_info[0]);
    }

    /**
     * Function is the entrypoint to create a new observer.
     * This function calls the model method to insert a new observer
     * into the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.5.2
     */
    public function new() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the location info from the post request
        $new_observer = $input->get('new_observer', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->newObserver($new_observer)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'New observer '
                . $new_observer['first_name']
                . ' '
                . $new_observer['last_name']
                . ' has been created.')
        );
    }

    /**
     * Function is the entrypoint to update an observer.
     * This function calls the model method to update an observer
     * in the database and returns a JSON response with a confirmation
     * message.
     *
     * @return void
     *
     * @since 0.5.2
     */
    public function update() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get all the location info from the post request
        $modified_observer = $input->get('modified_observer', array(), 'ARRAY');
        $model = $this->getModel();

        // if the database insert fails
        if (($model->updateobserver($modified_observer)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Observer '
                . $modified_observer['first_name']
                . ' '
                . $modified_observer['last_name']
                . ' has been updated.')
        );
    }
}
