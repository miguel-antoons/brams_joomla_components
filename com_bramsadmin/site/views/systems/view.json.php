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
 * @since  0.0.1
 */
class BramsAdminViewSystems extends HtmlView {

    // function returns all the systems in a JSON array
    public function getSystems() {
        $model = $this->getModel();
        // if an error occurred in the model
        if (($systems = $model->getSystems()) === -1) {
            return;
        }

        echo new JResponseJson($systems);
    }
}
