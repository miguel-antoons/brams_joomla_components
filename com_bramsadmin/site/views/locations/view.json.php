<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.4.1
 */
class BramsAdminViewLocations extends HtmlView {
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

    // function returns all the systems in a JSON array
    public function getAll() {
        $model = $this->getModel();
        // if an error occurred in the model
        if (($locations = $model->getLocations()) === -1) {
            return;
        }

        echo new JResponseJson($locations);
    }

    /**
     * Function is the entrypoint to delete a location. It calls the
     * location delete method from the model and returns a json response
     * to front-end.
     *
     * @since 0.4.1
     */
    public function delete() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // get the system's id from url
        $id = (int) $input->get('id');
        $model = $this->getModel();

        // if the database delete failed
        if (($model->deleteLocation($id)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Location with id ' . $id . ' has been deleted.')
        );
    }
}
