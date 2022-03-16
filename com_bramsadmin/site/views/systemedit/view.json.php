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
        echo new JResponseJson(array('this', 'is', 'a', 'test'));
    }
}