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
    function display($tpl = null) {
        $input = JFactory::getApplication()->input;
        $new_system_info = $input->get('newSystemInfo', array(), 'ARRAY');
        $model = $this->getModel();
        // $model->insertSystem($new_system_info);
        print_r($new_system_info);

        // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

        echo new JResponseJson(array('this', 'is', 'a', 'test'));
    }
}