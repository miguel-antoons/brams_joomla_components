<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * HTML View class for the BramsNetwork Component
 *
 * @since  0.2.1
 */
class BramsNetworkViewObservers extends HtmlView {
    public $observer_info;

	/**
	 * Display the Map view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
     * @since 0.2.0
	 */
	function display($tpl = null) {
		// get all the observer information
		$this->observer_info = $this->get('ObserverInfo');

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsnetwork/views/observers/css/observers.css');
		$document->addStyleSheet('/components/com_bramsnetwork/views/observers/css/bootstrap.min.css');
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		$document->addScript('/components/com_bramsnetwork/views/observers/js/observers.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
	}
}
