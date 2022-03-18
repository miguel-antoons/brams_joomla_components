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
 * Class inserts or updates a system and generates
 * a JSON response for front-end.
 */
class BramsAdminViewSystemEdit extends HtmlView {
    public function create() {
        $input = JFactory::getApplication()->input;
        $new_system_info = $input->get('newSystemInfo', array(), 'ARRAY');
        $model = $this->getModel();
        $model->insertSystem($new_system_info);

        // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

        echo new JResponseJson(
            array(
                ('message') => 'New system ' . $new_system_info['name'] . ' has been created.'
            )
        );
    }

    public function update() {
        $input = JFactory::getApplication()->input;
        $system_info = $input->get('systemInfo', array(), 'ARRAY');
        print_r($system_info);
        $model = $this->getModel();
        $model->updateSystem($system_info);

        // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

        echo new JResponseJson(
            array(
                ('message') => 'System ' . $system_info['name'] . ' has been updated.'
            )
        );
    }
}