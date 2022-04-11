<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsCampaign Component
 *
 * @since  0.0.2
 */
class BramsCampaignViewCampaignEdit extends HtmlView {
	/**
	 * Display the CampaignEdit view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @since 0.0.2
	 */
	function display($tpl = null) {
		// Display the view
		parent::display($tpl);
		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = Factory::getDocument();
		$document->addStyleSheet('/components/com_bramscampaign/views/_css/edit.css');
		$document->addStyleSheet('/components/com_bramscampaign/views/_css/bootstrap.min.css');
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		$document->addScript('/components/com_bramscampaign/views/_js/edit.js');
		$document->addScript('/components/com_bramscampaign/views/campaignedit/js/campaignedit.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
	}
}
