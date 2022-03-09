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
	/**
	 * Display the Map view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
		// get all the observer information
		$this->observer_info = $this->get('ObserverInfo');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

		// Display the view
		parent::display($tpl);

		// add javascript and css
		$this->setDocument();
	}

	// function adds needed javascript and css files to the view
	private function setDocument() {
		$document = JFactory::getDocument();
		$document->addStyleSheet('/components/com_bramsnetwork/observers/map/css/observers.css');
		$document->addStyleSheet('/components/com_bramsnetwork/observers/map/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsnetwork/views/observers/js/observers.js');
		$document->addScript('https://kit.fontawesome.com/yourcode.js');
		// $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
	}
}
