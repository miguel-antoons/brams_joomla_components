<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * BramsAdmin Component Controller
 *
 * @since  0.0.1
 */
class BramsAdminController extends BaseController {
    public function newSystem() {
        $view = $this->getView($this->input->get('view'));
        $view->create();
    }

    public function updateSystem() {
        $view = $this->getView($this->input->get('view'));
        $view->update();
    }
}
