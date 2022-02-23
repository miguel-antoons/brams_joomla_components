<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * HTML View class for the BramsData Component
 *
 * @since  0.0.1
 */
class BramsDataViewAvailability extends HtmlView {
	/**
	 * Display the Availability view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
		// Assign data to the view
		$this->stations = $this->get('Stations');

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
		$this->selected_stations = array();
		foreach ($_POST['station'] as $result) {
			$this->stations[array_search($result, array_column($this->stations, 'id'))]->checked = 'checked';
			$this->selected_stations[] = $result;
		}
		if ($_POST['endDate'] > $_POST['startDate']) {
			$this->start_date = $_POST['startDate'];
			$this->end_date = $_POST['endDate'];
		}
		else {
			$this->start_date = $this->get('Yesterday');
			$this->end_date = $this->get('Today');
		}

		$this->getFileAvailability();
	}

	// get and structure the file availability data
	private function getFileAvailability() {
		//$this->selected_stations = array(2);
		$model = $this->getModel();
		echo $model;
		$this->availability = $model->getAvailability($this->start_date, $this->end_date);
		//$this->availability = $this->get('Availability');
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/visavail.css');
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/bootstrap.min.css');
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/fontawesome.v5.0.12.css');
		$document->addScript('/components/com_bramsdata/views/availability/js/d3.min.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/moment-with-locales.min.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/check_button.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/visavail.js');
	}
}
