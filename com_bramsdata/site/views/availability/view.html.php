<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;

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
		$document = JFactory::getDocument();
		$document->addScript('/components/com_bramsdata/views/availability/js/check_button.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/visavail.js');
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/visavail.css');

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
	}

	private function processForm() {
		foreach ($_POST['station'] as $result) {
			$this->stations[array_search($result, array_column($this->stations, 'id'))]->checked = 'checked';
		}
		if ($_POST['endDate'] > $_POST['startDate']) {
			$this->start_date = $_POST['startDate'];
			$this->end_date = $_POST['endDate'];
		}
		else {
			$this->start_date = $this->get('Yesterday');
			$this->end_date = $this->get('Today');
		}
	}
}
