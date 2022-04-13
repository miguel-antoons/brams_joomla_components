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
class BramsCampaignViewCampaignParticipation extends HtmlView {
	// function returns all the campaigns in a JSON array
	public function getAll() {
		$model = $this->getModel();
		// if an error occurred in the model
		if (($campaigns = $model->getCampaigns()) === -1) return;

		echo new JResponseJson($campaigns);
	}

	// ! below function will probably be removed in the near future
	public function linkCampaign() {
		$model = $this->getModel();
	}
}
