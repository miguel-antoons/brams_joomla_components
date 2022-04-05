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
 * @since  0.8.1
 */
class BramsAdminViewDigitizers extends HtmlView {
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
     * Function is the entrypoint to delete a digitizer. It calls the
     * digitizer delete method from the model and returns a json response
     * to front-end.
     *
     * @since 0.8.1
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
        if (($model->deleteDigitizer($id)) === -1) {
            return;
        }

        // if everything goes well, return a validation message to front-end
        echo new JResponseJson(
            array(('message') => 'Digitizer with id ' . $id . ' has been deleted.')
        );
    }

    // function returns all the digitizers in a JSON array
    public function getAll() {
        $model = $this->getModel();
        // if an error occurred in the model
        if (($digitizers = $model->getDigitizers()) === -1) {
            return;
        }

        echo new JResponseJson($digitizers);
    }
}
