<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * BramsNetwork Component Controller
 *
 * @since  0.0.1
 */
class BramsNetworkController extends BaseController {
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
     * @return BramsNetworkController|JViewLegacy
     *
     * @throws Exception
     * @since   3.0
     */
    public function display($cacheable = false, $url_params = array(), $block_display = false)
    {
        $document = JFactory::getDocument();
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
     * Function calls the view method to return all the observer info.
     *
     * @since 0.3.5
     */
    public function getObservers() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo '
                    Something went wrong. 
                    Activate Joomla debug and view log messages for more information.
                ';
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObservers();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * Function calls the view method to return all the stations ordered by status
     * (active, inactive  or beacon).
     *
     * @since 0.3.5
     */
    public function getStations() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo '
                    Something went wrong. 
                    Activate Joomla debug and view log messages for more information.
                ';
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getStations();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }
}
