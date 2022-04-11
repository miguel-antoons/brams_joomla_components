<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Class inserts or updates a campaign and generates
 * a JSON response for front-end.
 * @since 0.0.2
 */
class BramsCampaignViewCampaignEdit extends HtmlView {
	/**
	 * Function makes sure to get the application input. If it fails, it
	 * will return false
	 *
	 * @return boolean|JInput
	 * @since 0.2.5
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
	 * Function is the entrypoint to create a new campaign. It calls the
	 * according method from the model and returns a json response to
	 * front-end.
	 *
	 * @since 0.0.2
	 */
	public function create() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// get the new campaign's information from request data
		$new_campaign_info = $input->get('newCampaignInfo', array(), 'ARRAY');
		$model = $this->getModel();

		// if the database insert fails
		if (($model->insertCampaign($new_campaign_info)) === -1) {
			return;
		}

		// if everything goes well, return a validation message to front-end
		echo new JResponseJson(
			array(('message') => 'New campaign ' . $new_campaign_info['name'] . ' has been created.')
		);
	}

	/**
	 * Function is the entrypoint to a campaign update. It calls the
	 * according methods from the model and returns a json response
	 * to the front-end.
	 *
	 *  @since 0.0.2
	 */
	public function update() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// get the new campaign's information from request data
		$campaign_info = $input->get('campaignInfo', array(), 'ARRAY');
		$model = $this->getModel();

		// if the database update fails
		if (($model->updateCampaign($campaign_info)) === -1) {
			return;
		}

		// if everything goes well, return a validation message to front-end
		echo new JResponseJson(
			array(('message') => 'Campaign ' . $campaign_info['name'] . ' has been updated.')
		);
	}

	/**
	 * Function is the entrypoint to get specific campaign information.
	 * It calls the method to get all the information about one campaign.
	 * The campaign it will request information for is given in the api url (id).
	 *
	 * @since 0.0.2
	 */
	public function getOne() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// retrieve the id of the requested campaign
		$id = (int) $input->get('id');
		$model = $this->getModel();

		// if the database select failed
		if (($response = $model->getCampaign($id)) === -1) {
			return;
		}

		echo new JResponseJson($response[0]);
	}

	/**
	 * Function is the entrypoint to get all the campaign names. It was initially
	 * created to provide campaign names for front-end.
	 *
	 * @since 0.0.2
	 */
	public function getAllSimple() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// retrieve the id of the requested campaign
		$id = (int) $input->get('id');
		$model = $this->getModel();

		// if the database select failed
		if (($response = $model->getCampaignNames($id)) === -1) {
			return;
		}

		echo new JResponseJson($response);
	}

	/**
	 * Function is the entrypoint to get all the campaign types and id.
	 * It is created to provide HTML select options for the campaign
	 * edit form.
	 *
	 * @since 0.0.2
	 */
	public function getTypes() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// retrieve the id of the selected type
		$id = $input->get('id');
		$model = $this->getModel();

		// if the database select failed
		if (($response = $model->getCampaignTypes($id)) === -1) {
			return;
		}

		echo new JResponseJson($response);
	}

	/**
	 * Function is the entrypoint to get all the systems name and id.
	 * It is created to provide HTML select options for the campaign
	 * edit form.
	 *
	 * @since 0.0.2
	 */
	public function getSystems() {
		// if an error occurred when getting the app input, stop the function
		if (!$input = $this->getAppInput()) {
			return;
		}
		// retrieve the id of the selected system
		$id = $input->get('id');
		$model = $this->getModel();

		// if the database select failed
		if (($response = $model->getSystemNames($id)) === -1) {
			return;
		}

		echo new JResponseJson($response);
	}
}
