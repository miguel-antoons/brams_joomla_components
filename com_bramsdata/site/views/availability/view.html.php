<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

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

		// Assign data to the view
		$this->stations = $this->get('Stations');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

		// process the submitted form
		if (isset($_GET['submit'])) {
			foreach ($_GET['station'] as $result) {
				$this->stations[array_search($result, array_column($this->stations, 'id'))]->checked = 'checked';
			}
			if ($_GET['endDate'] > $_GET['startDate']) {
				$this->start_date = $_GET['startDate'];
				$this->end_date = $_GET['endDate'];
			}
			else {
				$this->start_date = $this->get('Yesterday');
				$this->end_date = $this->get('Today');
			}
		}
		else {
			$this->start_date = $this->get('StartDate');
			$this->end_date = $this->get('Today');
		}

		// Display the view
		parent::display($tpl);
	}
}
