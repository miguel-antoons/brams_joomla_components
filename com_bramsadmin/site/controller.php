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
    /**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  \JControllerLegacy  A \JControllerLegacy object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array(), $block_display = false)
	{
		$document = \JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		// Get/Create the model
		if ($model = $this->getModel($viewName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		// Display the view
		if ($cachable && $viewType !== 'feed' && \JFactory::getConfig()->get('caching') >= 1)
		{
			$option = $this->input->get('option');

			if (is_array($urlparams))
			{
				$app = \JFactory::getApplication();

				if (!empty($app->registeredurlparams))
				{
					$registeredurlparams = $app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new \stdClass;
				}

				foreach ($urlparams as $key => $value)
				{
					// Add your safe URL parameters with variable type as value {@see \JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}

				$app->registeredurlparams = $registeredurlparams;
			}

			try
			{
				/** @var \JCacheControllerView $cache */
				$cache = \JFactory::getCache($option, 'view');
				$cache->get($view, 'display');
			}
			catch (\JCacheException $exception)
			{
				$view->display();
			}
		}
		elseif($block_display)
		{
			return $view;
		}
        else {
            $view->display();
        }

		return $this;
	}
    
    public function newSystem() {
        $view = $this->display(false, array(), true);
        $view->create();
    }

    public function updateSystem() {
        $view = $this->display(false, array(), true);
        $view->update();
    }
}
