<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsNetwork Component
 *
 * @since  0.0.1
 */
class BramsNetworkViewMap extends HtmlView {
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
     * Function gets all the stations grouped by active, inactive or beacons and returns
     * all this data to the front-end trough JSON.
     * @since 0.3.5
     */
    public function getStations() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        $date = $input->get('date');
        $model = $this->getModel();

        // get all active stations for a given date
        if (($active_stations = $model->getActiveStationInfo($date)) === -1) {
            return;
        }

        // get inactive stations for a given date
        if (($inactive_stations = $model->getInactiveStationInfo($date)) === -1) {
            return;
        }

        // get all beacons
        if (($beacons = $model->getBeacons()) === -1) {
            return;
        }

        // send a JSON response with all the data
        echo new JResponseJson(array(
            ('active') => $active_stations,
            ('inactive') => $inactive_stations,
            ('beacon') => $beacons,
        ));
    }
}
