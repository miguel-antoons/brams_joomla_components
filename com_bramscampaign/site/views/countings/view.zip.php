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

	public function getOriginal() {
		$file = "C:\Users\Miguel\Documents\TFE2022\\recordings\BEHAAC\New Compressed (zipped) Folder.zip";
		if (file_exists($file)) {
			header("Content-Disposition: attachment; filename=New Compressed (zipped) Folder.zip");
			header("Content-Type: application/zip");
			readfile($file);
		}
	}
}
