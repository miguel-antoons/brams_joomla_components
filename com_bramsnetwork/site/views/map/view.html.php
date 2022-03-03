<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * HTML View class for the BramsNetwork Component
 *
 * @since  0.0.1
 */
class BramsNetworkViewMap extends HtmlView {
	/**
	 * Display the Map view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
		// Assign data to the view
		$this->active_stations = $this->get('ActiveStationInfo');
		$this->inactive_stations = $this->get('InactiveStationInfo');

		// process the submitted form
		if (isset($_POST['submit'])) {
			$this->processForm();
		}
		else {
			$this->start_date = $this->get('StartDate');
			$this->end_date = $this->get('Today');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// entry-point of form processing
	private function processForm() {
		// TODO: process form
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/map.css');
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsnetwork/views/map/js/map.js');
		// $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
	}
}
