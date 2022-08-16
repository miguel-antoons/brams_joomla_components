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
     * Function gets the labels and psd values for each
     * station and returns all the data as a JSON object.
     */
	public function getPSD() {
        // get all the url params
		$input      = $this->getAppInput();
		$start_date = new DateTime($input->get('start'));
		$start_date = $start_date->format('Y-m-d H:i:s');
		$end_date   = new DateTime($input->get('end'));
		$end_date   = $end_date->format('Y-m-d H:i:s');
		$system_ids = explode(',', $input->get('ids', '', 'string'));
		$model      = $this->getModel();

        // get the labels (dates) based on the interval
		if (($labels= $model->getLabels($start_date, $end_date)) === -1)                return;
        // get the actual PSD values
		if (($raw_data = $model->getPSD($start_date, $end_date, $system_ids)) === -1)   return;
		$data = array();

		foreach ($system_ids as $system_id) {
			$system_id = trim($system_id);
			$specific_system_data = array_values(
				array_filter(
					$raw_data,
					function($psd_info) use ($system_id) {
						return ((int) $psd_info->system_id) === ((int) $system_id);
					}
				)
			);
            // get only the relevant data
			$data[$system_id] = $model->verifyLabels($labels, $specific_system_data);
		}

		echo new JResponseJson(array(
			'labels'    => $labels,
			'data'      => $data,
		));
	}
}
