<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.0.2
 */
class BramsAdminViewSystemEdit extends HtmlView {
    public $id;             // contains the id of the requested system or 0 if no system was requested
    public $system_info;    // contains the system info from the database
    public $location_id;    // contains the location id of the requested system (id of the first location if no system was requested)
    public $date_to_show;   // contains the date value to show
    public $antenna;        // contains the antenna value to show
    public $locations;      // contains all the location values
    public $system_names;   // contains all the taken system names
    public $title;          // contains the page title

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
            echo new JResponseJson(array(('message') => false));
            // log the exception
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

	/**
	 * Display the Systems view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @return  void
     *
     * @since 0.2.0
	 */
	function display($tpl = null) {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) {
            return;
        }
        // retrieve the id of the requested system
        $this->id = (int) $input->get('id');
		$model = $this->getModel();

        // if the user requested a system
		if ($this->id) {
			$this->system_info = $model->getSystemInfo($this->id);
			$this->location_id = $this->system_info[0]->location_id;
			$this->date_to_show = $this->system_info[0]->start;
			$this->antenna = $this->system_info[0]->antenna;
			$this->locations = $model->getLocations($this->antenna, $this->location_id);
			$this->system_names = $model->getSystemNames($this->id);
			$this->title = 'Edit System ' . $this->locations[$this->system_info[0]->location_id]->name;
		} else {
			$this->id = 0;
			$this->antenna = -1;
			$this->locations = $model->getLocations(-1, -1);
			reset($this->locations);
			$this->location_id = key($this->locations);
			$this->date_to_show = $this->get('Now');
			$this->system_names = $model->getSystemNames(-1);
			$this->title = 'Create New System';
			$this->system_info = array(
				0 => (object) array(('name') => '', ('comments') => '')
			);
		}

		if ($this->locations) {
			$this->locations[$this->location_id]->selected = 'selected';
		}

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
		$document->addStyleSheet('/components/com_bramsadmin/views/systemedit/css/system_edit.css');
		$document->addStyleSheet('/components/com_bramsadmin/views/systems/css/bootstrap.min.css');
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		$document->addScript('/components/com_bramsadmin/views/systemedit/js/system_edit.js');
		$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
	}
}
