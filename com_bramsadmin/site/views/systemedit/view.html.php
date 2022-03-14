<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.0.2
 */
class BramsAdminViewSystemEdit extends HtmlView {
	/**
	 * Display the Systems view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
        $this->id = JRequest::getVar('id');
		$model = $this->getModel();
		$this->system_info = $model->getSystemInfo($this->id);
		$this->locations = $model->getLocations();

		if ($this->id) {
			$this->date_to_show = $this->system_info[0]->start;
		} else {
			$this->date_to_show = $this->get('Now');
		}

		$this->locations[array_search($this->id, array_column($this->locations, 'id'))]->selected = 'selected';

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
		$document->addStyleSheet('/components/com_bramsadmin/views/system_edit/css/system_edit.css');
		$document->addStyleSheet('/components/com_bramsadmin/views/systems/css/bootstrap.min.css');
		$document->addScript('/components/com_bramsadmin/views/system_edit/js/system_edit.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
	}
}
