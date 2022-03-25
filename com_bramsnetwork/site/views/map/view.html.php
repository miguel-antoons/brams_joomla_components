<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsNetwork Component
 *
 * @since  0.0.1
 */
class BramsNetworkViewMap extends HtmlView {
    public $today;
	/**
	 * Display the Map view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
     * @since 0.2.0
	 */
	function display($tpl = null) {
		$this->today = $this->get('Today');

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = Factory::getDocument();
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/map.css');
		$document->addStyleSheet('/components/com_bramsnetwork/views/map/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsnetwork/views/map/js/map.js');
		// $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
		$document->addScript('/components/com_bramsnetwork/views/map/js/jquery.maphilight.min.js');
	}
}
