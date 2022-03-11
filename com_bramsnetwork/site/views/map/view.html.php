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
		$this->active_checkbox_value = 'active';
		$this->inactive_checkbox_value = 'inactive';
		$this->new_checkbox_value = 'new';
		$this->old_checkbox_value = 'old';
		// Assign data to the view
		$this->checkbox[$this->active_checkbox_value] = '';
		$this->checkbox[$this->inactive_checkbox_value] = '';
		$this->checkbox[$this->new_checkbox_value] = '';
		$this->checkbox[$this->old_checkbox_value] = '';
		$this->today = $this->get('Today');

		// process the submitted form
		if (isset($_POST['submit'])) {
			$this->processForm();
		} else {
			// if there is no submitted form, perform default actions
			$this->defaultAction();
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
		$model = $this->getModel();

		// get all existing stations for a given date
		$this->active_stations = $model->getActiveStationInfo($_POST['startDate']);
		$this->inactive_stations = $model->getInactiveStationInfo($_POST['startDate']);
		$this->selected_date = $_POST['startDate'];
		$this->beacons = $model->getBeacons();

		// set the checkboxes checked status
		foreach ($_POST['checkbox'] as $checkbox_value) {
			$this->checkbox[$checkbox_value] = 'checked';
		}
	}

	private function defaultAction() {
		$this->selected_date = $this->get('Today');
		$model = $this->getModel();

		// get all existing stations for current date (today)
		$this->active_stations = $model->getActiveStationInfo($this->today);
		$this->inactive_stations = $model->getInactiveStationInfo($this->today);
		$this->beacons = $model->getBeacons();

		// set default values for the checkboxes checked status
		$this->checkbox[$this->active_checkbox_value] = 'checked';
		$this->checkbox[$this->new_checkbox_value] = 'checked';
		$this->checkbox[$this->old_checkbox_value] = 'checked';
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/map.css');
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsnetwork/views/map/js/map.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
		$document->addScript('/components/com_bramsnetwork/views/map/js/jquery.maphilight.min.js');
	}
}
