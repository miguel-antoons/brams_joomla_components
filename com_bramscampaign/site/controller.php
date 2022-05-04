<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Cache\Controller\ViewController;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\View\ViewInterface;

/**
 * BramsCampaign Component Controller
 *
 * @since  0.0.1
 */
class BramsCampaignController extends BaseController {
    /**
     * * CHANGES :
     * *      - if $block_display is set to true, the function
     * *        will NOT call the views display method and returns the view instead.
     * *      - if no model is found for the view name, the function will search a
     * *        model that has a name matching the requested view name without the last
     * *        4 letters.
     * *      - if there are models specified in the url, the display function will
	 * *        initialise and add the models to the view in order to use them.
     *
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param boolean $cacheable If true, the view output will be cached
     * @param array $url_params An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return BramsCampaignController|ViewInterface
     *
     * @throws Exception
     * @since   0.0.1
     */
    public function display($cacheable = false, $url_params = array(), $block_display = false) {
        $document = $this->app->getDocument();
        $viewType = $document->getType();
        $viewName = $this->input->get('view', $this->default_view);
        $modelNames = explode(',', $this->input->get('model', '', 'string'));
        $viewLayout = $this->input->get('layout', 'default', 'string');
        $add_def_model = true;

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        // Get/Create the model
        if ($model = $this->getModel($viewName)) {
            // Push the model into the view (as default)
            $view->setModel($model, true);
            $add_def_model = false;
        } elseif ($model = $this->getModel(substr($viewName, 0, -4) . 's')) {
             // Push the model into the view (as default)
             $view->setModel($model, true);
             $add_def_model = false;
        }

        foreach ($modelNames as $modelName) {
            if ($model = $this->getModel($modelName)) {
                // Push the model into the view (as default)
                $view->setModel($model, $add_def_model);
                $add_def_model = false;
            }
        }

        $view->document = $document;

        // Display the view
        if ($cacheable && $viewType !== 'feed' && Factory::getApplication()->get('caching') >= 1) {
            $option = $this->input->get('option');

            if (is_array($url_params)) {
                $this->app = Factory::getApplication();

                if (!empty($this->app->registeredurlparams)) {
                    $registered_url_params = $this->app->registeredurlparams;
                } else {
                    $registered_url_params = new stdClass;
                }

                foreach ($url_params as $key => $value) {
                    // Add your safe URL parameters with variable type as value {@see \JFilterInput::clean()}.
                    $registered_url_params->$key = $value;
                }

                $this->app->registeredurlparams = $registered_url_params;
            }

            /** @var ViewController $cache */
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
     * database row has to be created.
     *
     * @since 0.0.1
     */
    public function create() {
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
     * API - DELETE
     * Function executes the view delete method. This function is called when
     * a database row has to be deleted.
     * The row depends on the view that will be called
     *
     * @since 0.0.1
     */
    public function delete() {
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
     * Function executes the views getAll method. This method
     * will get all the information about equivalent elements and return this
     * to the sites front-end.
     * The element info that will be returned depends on the view.
     *
     * @since 0.0.1
     */
    public function getAll() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getAll();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the views getAllSimple method. This method
     * will get all the information about equivalent elements and return this
     * to the sites front-end. Note that the simple means that only a limited
     * amount of information will be sent compared to the 'getAll' method.
     * The element info that will be returned depends on the view.
     *
     * @since 0.0.1
     */
    public function getAllSimple() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getAllSimple();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the views update method. This function is called when
     * a database row has to be updated.
     * The database row to be updated depends on the specified view.
     *
     * @since 0.0.1
     */
    public function update() {
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
     * API - GET
     * Function executes the view getOne method. THis function is called when
     * front-end needs information about one specific row.
     * The database row to return depends on the specified view.
     *
     * @since 0.0.1
     */
    public function getOne() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getOne();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getTypes method. THis function is called when
     * front-end needs all the campaign types.
     *
     * @since 0.0.2
     */
    public function getTypes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getTypes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getSystems method. THis function is called when
     * front-end needs all the systems.
     *
     * @since 0.0.2
     */
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

    /**
     * API - POST
     * Function executes the view getSystems method. THis function is called when
     * front-end needs all the systems.
     * @since 0.1.1
     */
    public function linkCampaign() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->linkCampaign();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    public function getSpectrograms() {
        // $_SESSION['downloadStatus'] = array('status' => 'pending');
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getSpectrograms();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    public function getCSV() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getCSV();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    public function addMeteor() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->addMeteor();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    public function deleteMeteor() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->deleteMeteor();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

//    public function getDownloadStatus() {
//        if (Jsession::checkToken('get')) {
//            try {
//                $view = $this->display(false, array(), true);
//            } catch (Exception $e) {
//                echo new JResponseJson(array(('message') => $e));
//                Log::add($e, Log::ERROR, 'error');
//                return;
//            }
//            echo new JResponseJson($_SESSION['downloadStatus']);
//            return;
//        } else {
//            echo new JResponseJson(array(('message') => false));
//        }
//    }
}
