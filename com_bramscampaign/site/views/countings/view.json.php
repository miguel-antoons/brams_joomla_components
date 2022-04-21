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

	/**
	 * Function is the entrypoint to get all the campaigns
	 * for the countings page. It uses the campaigns model instead
	 * of the countings model.
	 * It returns all the campaigns in a JSON array
	 *
	 * @throws Exception
	 * @since 0.1.1
	 */
	public function getAll() {
		$model = $this->getModel('campaigns');
		// if an error occurred in the model
		if (($campaigns = $model->getCampaigns(Factory::getApplication()->getIdentity()->id)) === -1) return;

		echo new JResponseJson($campaigns);
	}

	// ! below function will probably be removed in the near future

	/**
	 * Function is the entrypoint to create a new counting.
	 * A counting is a unique link between a user and a campaign.
	 *
	 * @throws Exception
	 * @since 0.1.1
	 */
	public function create() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) return;

		// get all the info needed for a new counting creation
		$counting_info = $input->get('counting_info', array(), 'ARRAY');
		$model = $this->getModel();

		// try to create the new counting, return empty if an error occurs
		if ($model->createCounting($counting_info) === -1) return;

		// if everything goes as planned, return a confirmation message
		echo new JResponseJson(
			array(
				('message') => 'Counting has created for user '
					. Factory::getApplication()->getIdentity()->id
					. ' and for campaign ' . $counting_info['campaign_id']
			)
		);
	}
}
