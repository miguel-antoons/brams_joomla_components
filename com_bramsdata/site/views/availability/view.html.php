<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsData Component
 *
 * @since  0.0.1
 */
class BramsDataViewAvailability extends HtmlView {
	public $selected_stations;
	public $stations;
	public $today;
	public $start_date;
	public $end_date;
	public $interval;
	public $availability;
	public $column_length;

	/**
	 * Display the Availability view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @since 0.0.2
	 */
	function display($tpl = null) {
		// Assign data to the view
		$this->selected_stations = array();	// initialize the $selected_stations array
		if (($this->stations = $this->get('Stations')) === -1) {
			// show an error message and stop the function
			echo '
                Something went wrong. 
                Activate Joomla debug and view log messages for more information.
            ';
			return;
		}
		$this->today = $this->get('Today');
		$this->start_date = $this->get('StartDate');
		$this->set_columns_length();

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	private function set_columns_length() {
		$this->column_length = ceil(count($this->stations) / 5);
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = Factory::getDocument();
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/availability.css');
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/visavail.css');
		$document->addStyleSheet('/components/com_bramsdata/views/availability/css/bootstrap.min.css');
		$document->addStyleSheet('https://use.fontawesome.com/releases/v5.0.12/css/all.css');
		$document->addScript('/components/com_bramsdata/views/availability/js/d3.min.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/moment-with-locales.min.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/check_button.js');
		$document->addScript('/components/com_bramsdata/views/availability/js/visavail.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		// $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
	}
}
