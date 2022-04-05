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
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.8.1
 */
class BramsAdminViewDigitizers extends HtmlView {
    public $message;

    /**
     * Display the digitizers view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     * @since 0.0.1
     */
    function display($tpl = null) {
        try {
            $input = Factory::getApplication()->input;
        } catch (Exception $e) {
            // if an exception occurs, print an error message
            echo '
                Something went wrong. 
                Activate Joomla debug and view log messages for more information.
            ';
            // log the error and stop the function
            Log::add($e, Log::ERROR, 'error');
            return;
        }
        // Assign data to the view
        $model = $this->getModel();
        // get the message id
        $message_id = (int) $input->get('message');

        $this->message = $model->digitizer_messages[$message_id];

        // Display the view
        parent::display($tpl);

        // add javascript and css
        $this->setDocument();
    }

    // function adds needed javascript and css files to the view
    private function setDocument() {
        $document = Factory::getDocument();
        $document->addStyleSheet('/components/com_bramsadmin/views/digitizers/css/digitizers.css');
        $document->addStyleSheet('/components/com_bramsadmin/views/digitizers/css/bootstrap.min.css');
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
        $document->addScript('/components/com_bramsadmin/views/digitizers/js/digitizers.js');
    }
}
