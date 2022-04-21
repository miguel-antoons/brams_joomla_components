<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 *
 * ! NOTE that this class and its functions will probably be deleted in the near future
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Input\Input;

/**
 * HTML View class for the BramsCampaign Component
 *
 * @since  0.0.1
 */
class BramsCampaignViewCountings extends HtmlView {
	/**
	 * Function makes sure to get the application input. If it fails, it
	 * will return false
	 *
	 * @return boolean|Input
	 * @since 0.0.1
	 */
	private function getAppInput() {
		try {
			return Factory::getApplication()->input;
		} catch (Exception $e) {
			// if an exception occurs, return false to front-end
			echo new JResponseJson(array(('message') => $e));
			// log the exception
			Log::add($e, Log::ERROR, 'error');
			return false;
		}
	}

	public function getSpectrograms() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) return -1;
		$config = new JConfig();

		// get the id of the counting to get files from
		$campaign_id        = $input->get('id');
		// initialise the models
		$spectrogram_model  = $this->getModel('spectrogram');
		$campaign_model     = $this->getModel('campaigns');

		// get all the needed data
		if (($campaign = $campaign_model->getCampaign($campaign_id)) === -1)         return -1;
		if (($spectrograms = $spectrogram_model->getSpectrograms($campaign[0])) === -1) return -1;

		// create the zip file
		$file_name = $config->tmp_path . '/original_spectrograms.zip';
		$zip = new ZipArchive();

		// add all the spectrograms to the zip file
		if ($zip->open($file_name, ZipArchive::CREATE)) {
			foreach ($spectrograms as $spectrogram) {
				$zip->addFile(JPATH_ROOT.'/ProjectDir'.$spectrogram->url, $spectrogram->url);
			}
		}

		$zip->close();
		// send the zip file so the user can download it
		header("Content-Disposition: attachment; filename=original_spectrograms.zip");
		header("Content-Type: application/zip");
		readfile($file_name);
	}
}
