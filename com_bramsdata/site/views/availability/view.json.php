<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Input\Input;

/**
 * HTML View class for the BramsData Component
 *
 * @since  0.3.5
 */
class BramsDataViewAvailability extends HtmlView {
	public $selected_stations;
	public $start_date;
	public $end_date;
	public $stations;
	public $availability;
	public $interval;
	/**
	 * Function makes sure to get the application input. If it fails, it
	 * will return false
	 *
	 * @return Input|boolean
	 * @since 0.2.5
	 */
	private function getAppInput() {
		try {
			return Factory::getApplication()->input;
		} catch (Exception $e) {
			// log the exception
			Log::add($e, Log::ERROR, 'error');
			return false;
		}
	}

	/**
	 * API - GET
	 * Function gets all the file availability data from the model and structures it in
	 * a way that front-end can use the data. It eventually returns a json string with
	 * all the availability data to the front-end of the site.
	 *
	 * @since 0.3.5
	 */
	public function getAvailability() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// get all the selected stations from the request
		$this->selected_stations = $input->get('ids', array(), 'ARRAY');
		$this->start_date = $input->get('start');  // get the start date from the request
		$this->end_date = $input->get('end');      // get the end date from the request

		// get the availability
		if ($this->getFileAvailability() === -1) {
			return;
		}
		// get all the stations
		if (($this->stations = $this->get('Stations')) === -1) {
			return;
		}

		$dataset = array();
		$data = array();

		// construct the dataset from database data
		foreach ($this->selected_stations as $station) {
			for ($index = 0 ; $index < count($this->availability[$station]) - 1 ; $index++) {
				// add time stamps and file availability to the dataset
				$data[] = array(
					$this->availability[$station][$index]->start,
					$this->availability[$station][$index]->available,
					$this->availability[$station][$index + 1]->start
				);
			}
			// add categories, title and interval to the dataset
			$dataset[] = (object) array(
				('measure') => $this->stations[array_search($station, array_column($this->stations, 'id'))]->name,
				('interval_s') => $this->interval,
				('categories') => (object) array(
					('0%') => (object) array(
						('class') => 'rect_has_no_data',
						('tooltip_html') => '<i class="fas fa-fw fa-exclamation-circle tooltip_has_no_data">0%</i><br>'
					),
					('100%') => (object) array(
						('class') => 'rect_has_data',
						('tooltip_html') => '<i class="fas fa-fw fa-check tooltip_has_data">100%</i><br>'
					),
					('0.1 - 20%') => (object) array(
						('class') => 'rect_red1',
						('tooltip_html') => '<i class="fas fa-fw tooltip_red1">0.1 - 20%</i><br>'
					),
					('20.1 - 40%') => (object) array(
						('class') => 'rect_red2',
						('tooltip_html') => '<i class="fas fa-fw tooltip_red2">20.1 - 40%</i><br>'
					),
					('40.1 - 60%') => (object) array(
						('class') => 'rect_blue',
						('tooltip_html') => '<i class="fas fa-fw tooltip_blue">40.1 - 60%</i><br>'
					),
					('60.1 - 80%') => (object) array(
						('class') => 'rect_green2',
						('tooltip_html') => '<i class="fas fa-fw tooltip_green2">60.1 - 80%</i><br>'
					),
					('80.1 - 99.9%') => (object) array(
						('class') => 'rect_green1',
						('tooltip_html') => '<i class="fas fa-fw tooltip_green1">80.1 - 99.9%</i><br>'
					),
				),
				('data') => $data
			);
		}

		echo new JResponseJson($dataset);
	}

	// get and structure the file availability data
	private function getFileAvailability() {
		$this->interval = 300;

		// get the model and call the appropriate method
		$model = $this->getModel();
		return $this->availability = $model->getAvailability(
			$this->start_date,
			$this->end_date,
			$this->selected_stations,
			$this->interval
		);
	}
}
