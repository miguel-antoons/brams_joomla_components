<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * BramsAdmin Component Controller
 *
 * @since  0.0.1
 */
class BramsAdminController extends BaseController {
    /**
     * CHANGES : if $block_display is set to true, the function
     *  will NOT call the views display method and returns the view instead.
     *
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param boolean $cacheable If true, the view output will be cached
     * @param array $url_params An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return BramsAdminController|JViewLegacy
     *
     * @throws Exception
     * @since   3.0
     */
	public function display($cacheable = false, $url_params = array(), $block_display = false)
	{
		$document = Factory::getDocument();
		$viewType = $document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		// Get/Create the model
		if ($model = $this->getModel($viewName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		// Display the view
		if ($cacheable && $viewType !== 'feed' && JFactory::getConfig()->get('caching') >= 1) {
			$option = $this->input->get('option');

			if (is_array($url_params)) {
				$app = JFactory::getApplication();

				if (!empty($app->registeredurlparams)) {
					$registeredurlparams = $app->registeredurlparams;
				} else {
					$registeredurlparams = new \stdClass;
				}

				foreach ($url_params as $key => $value) {
					// Add your safe URL parameters with variable type as value {@see \JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}

				$app->registeredurlparams = $registeredurlparams;
			}

            /** @var JCacheControllerView $cache */
            $cache = Factory::getCache($option, 'view');
            $cache->get($view);

        } elseif($block_display) {
			return $view;
		} else {
            $view->display();
        }

		return $this;
	}

    /**
     * API - POST
     * Function executes the views create method. This function is executed when a new
     * system has to be created.
     *
     * @since 0.2.0
     */
    public function newSystem() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->create();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the views update method. This function is called when
     * a system has to be updated.
     *
     * @since 0.2.0
     */
    public function updateSystem() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->update();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - DELETE
     * Function executes the views delete method. This function is called when
     * a system has to be deleted.
     *
     * @since 0.2.0
     */
	public function deleteSystem() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->delete();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
	}

    /**
     * API - GET
     * Function executes the view getSystem method. THis function is called when
     * front-end needs information about a specific system.
     *
     * @since 0.3.0
     */
    public function getSystem() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getSystem();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getLocationAntennas() method. This function is called when
     * front-end needs all the available locations.
     *
     * @since 0.3.0
     */
    public function getLocationAntennas() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocationAntennas();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getSystemNames() method. This function is called when
     * front-end needs all the taken system names.
     *
     * @since 0.3.0
     */
    public function getSystemNames() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getSystemNames();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    public function getSystems() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getSystems();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }
}
