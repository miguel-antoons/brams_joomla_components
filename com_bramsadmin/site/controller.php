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
    public function display() {
        $document = \JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		$view = $this->getView(
            $viewName,
            $viewType,
            '',
            array('base_path' => $this->basePath, 'layout' => $viewLayout)
        );

        // Get/Create the model
		if ($model = $this->getModel($viewName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

        return $view;
    }
    
    public function newSystem() {
        $view = $this->display();
        $view->create();
    }

    public function updateSystem() {
        $view = $this->display();
        $view->update();
    }
}
