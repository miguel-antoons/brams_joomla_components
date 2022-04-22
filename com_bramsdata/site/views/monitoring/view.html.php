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
 * @since  0.4.0
 */
class BramsDataViewMonitoring extends HtmlView {
	public $stations;
	public $today;
	public $start_date;
	public $column_length;

	/**
	 * Display the monitoring view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @throws Exception
	 * @since 0.4.0
	 */
	function display($tpl = null) {
		// get all the stations from the model
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
		$document->addStyleSheet('/components/com_bramsdata/views/monitoring/css/monitoring.css');
		$document->addStyleSheet('/components/com_bramsdata/views/monitoring/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsdata/views/monitoring/js/monitoring.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js');
	}
}
