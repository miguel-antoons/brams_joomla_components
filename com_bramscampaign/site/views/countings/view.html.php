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

use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsCampaign Component
 * Lists all the campaigns in a table. The user will have the possibility
 * to add meteors to the campaign from this view trough an 'edit' button.
 *
 * @since  0.1.1
 */
class BramsCampaignViewCountings extends HtmlView {
	/**
	 * @var mixed
	 * @since version
	 */
	public $message;

	/**
	 * Display the Countings view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @throws Exception
	 * @since 0.1.1
	 */
	function display($tpl = null) {
		// Display the view
		parent::display($tpl);
		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$wam = $this->document->getWebAssetManager();
		// add css stylesheets
		$wam->registerAndUseStyle('formStyle',      'components/com_bramscampaign/views/_css/list.css');
		$wam->registerAndUseStyle('bootstrap4',     'components/com_bramscampaign/views/_css/bootstrap.min.css');
		$wam->registerAndUseStyle('pageSpecific',   'components/com_bramscampaign/views/countings/css/countings.css');
		$wam->registerAndUseStyle('icons',          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		// add javascript files
		$wam->registerAndUseScript('formFunctions', 'components/com_bramscampaign/views/_js/list.js');
		$wam->registerAndUseScript('jquery',        'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		$wam->registerAndUseScript('pageSpecific',  'components/com_bramscampaign/views/countings/js/countings.js');
	}
}
