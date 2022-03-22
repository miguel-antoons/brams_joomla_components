<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Log\Log;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.0.1
 */
class BramsAdminViewSystems extends HtmlView {
	/**
	 * Display the Systems view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
		// Assign data to the view
		$model = $this->getModel();
		// if an error occurred in the model
		if (!$this->systems = $model->getSystems()) {
			return false;
		}
		$message_id = (int) JRequest::getVar('message');

		try {
			$this->message = $model->system_messages[$message_id];
		} catch (Exception $e) {
			echo '
				An error occurred while looking for a message following a user action. 
				Most likely, the message code requested does not exist and has yet to be
				created. Activate Joomla debugging and view the logs for more information.
			';
			Log::add($e, JLog::ERROR, 'jerror');
			return false;
		}

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsadmin/views/systems/css/systems.css');
		$document->addStyleSheet('/components/com_bramsadmin/views/systems/css/bootstrap.min.css');
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		$document->addScript('/components/com_bramsadmin/views/systems/js/systems.js');
	}
}
