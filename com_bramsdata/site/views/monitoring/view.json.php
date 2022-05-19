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
 * @since  0.4.0
 */
class BramsDataViewMonitoring extends HtmlView {
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

	public function getPSD() {
		$input      = $this->getAppInput();
		$start_date = $input->get('start');
		$end_date   = $input->get('end');
		$interval   = (int) $input->get('interval', 60);
		$system_ids = $input->get('system_id');
		$model      = $this->getModel();

		$labels     = $model->getLabels($start_date, $end_date, $interval);
		$raw_data   = $model->getPSD($start_date, $end_date, $system_ids);
		$data       = array();

		foreach ($system_ids as $system_id) {
			$specific_system_data = array_values(
				array_filter(
					$raw_data,
					function($psd_info) use ($system_id) {
						return ((int) $psd_info->system_id) === ((int) $system_id);
					}
				)
			);
			$data[$system_id] = $model->verifyLabels($labels, $specific_system_data);
		}

		return array(
			'labels'    => $labels,
			'data'      => $data,
		);
	}
}
