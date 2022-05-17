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
 * @since  0.0.1
 */
class BramsAdminViewSystems extends HtmlView {
    public $message;

	/**
	 * Display the Systems view
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

        $this->message = $model->system_messages[$message_id];

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = Factory::getDocument();
		$document->addStyleSheet('/components/com_bramsadmin/views/systems/css/systems.css');
		$document->addStyleSheet('/components/com_bramsadmin/views/_css/list.css');
		$document->addStyleSheet('/components/com_bramsadmin/views/_css/bootstrap.min.css');
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		$document->addScript('/components/com_bramsadmin/views/_js/list.js');
		$document->addScript('/components/com_bramsadmin/views/systems/js/systems.js');
		$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js');
		$document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
	}
}
