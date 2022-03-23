<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use \Joomla\CMS\Log\Log;

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
            echo new JResponseJson(array(('message') => false));
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
        if (!$model->insertSystem($new_system_info)) {
            // return an error response to front-end and stop the function
            echo new JResponseJson(array(('message') => false));
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
        if (!$model->updateSystem($system_info)) {
            // return an error response to front-end and stop the function
            echo new JResponseJson(array(('message') => false));
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'System ' . $system_info['name'] . ' has been updated.')
        );
    }

    /**
     * Function is the entrypoint to delete a system. It calls the
     * system delete method from the model and returns a json response
     * to front-end.
     *
     * @since 0.2.0
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
        if (!$model->deleteSystem($id)) {
            // return an error message and stop the function
            echo new JResponseJson(array(('message') => false));
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'System with id ' . $id . ' has been deleted.')
        );
    }
}
