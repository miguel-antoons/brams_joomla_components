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
 * HTML View class for the BramsCampaign Component
 *
 * @since  0.0.1
 */
class BramsCampaignViewCampaigns extends HtmlView {
	public $message;

	/**
	 * Display the campaigns view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @throws Exception
	 * @since 0.0.1
	 */
	function display($tpl = null) {
		try {
			$input = Factory::getApplication()->input;
		} catch (Exception $e) {
			// if an exception occurs, print an error message
			echo '
                Something went wrong. 
                Activate Joomla debug and view log messages for more information.
            ';
			// log the error and stop the function
			Log::add($e, Log::ERROR, 'error');
			return;
		}
		// Assign data to the view
		$model = $this->getModel();
		// get the message id
		$message_id = (int) $input->get('message');

		if (!empty($model->campaign_messages[$message_id])) {
			$this->message = $model->campaign_messages[$message_id];
		}

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	/**
	 * function adds needed javascript and css files to the view
	 *
	 * @throws Exception
	 * @since 0.1.1
	 */
	private function setDocument() {
		$wam = $this->document->getWebAssetManager();
		// add stylesheets
		$wam->registerAndUseStyle('listStyle',      'components/com_bramscampaign/views/_css/list.css');
		$wam->registerAndUseStyle('pageSpecific',   'components/com_bramscampaign/views/campaigns/css/campaigns.css');
		$wam->registerAndUseStyle('boostrap4',      'components/com_bramscampaign/views/_css/bootstrap.min.css');
		$wam->registerAndUseStyle('icons',          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		// add javascript
		$wam->registerAndUseScript('ajax',          'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		$wam->registerAndUseScript('listFunctions', 'components/com_bramscampaign/views/_js/list.js');
		$wam->registerAndUseScript('pageSpecific',  'components/com_bramscampaign/views/campaigns/js/campaigns.js');
	}
}
