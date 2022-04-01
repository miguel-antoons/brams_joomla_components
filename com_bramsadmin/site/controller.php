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
use Joomla\CMS\MVC\Controller\BaseController;

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

    /** + SYSTEMS VIEW APIs */
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
     * API - DELETE
     * Function executes the system view delete method. This function is called when
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
     * Function executes the views getSystem method. This method
     * will get all the system and their information and return this
     * to the sites front-end.
     *
     * @since 0.3.5
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

    /** + SYSTEMEDIT VIEW APIs  */
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

    /** + LOCATIONS VIEW APIs */
    /**
     * API - GET
     * Function executes the views getLocations method. This method
     * will get all the locations and their information and return this
     * to the sites front-end.
     *
     * @since 0.4.1
     */
    public function getLocations() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocations();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - DELETE
     * Function executes the locations view delete method. This function is called when
     * a location has to be deleted.
     *
     * @since 0.4.1
     */
    public function deleteLocation() {
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

    /** + LOCATION EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the locationEdit view getLocationCodes method.
     * This function is called when the front-end of the site needs all
     * the locations with the location code.
     *
     * @since 0.4.2
     */
    public function getLocationCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocationCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the locationEdit view getCountries method.
     * This function is called when the front-end of the site needs all
     * the countries from the database.
     *
     * @since 0.4.2
     */
    public function getCountries() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getCountries();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * GET api for observers is to be found in the OBSERVERS view part

    /**
     * API - GET
     * Function executes the locationEdit view getLocation method.
     * This function is called when the front-end of the site needs all
     * the information about one location from the database.
     *
     * @since 0.4.2
     */
    public function getLocation() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocation();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - POST
     * Function executes the locationEdit view newLocation method.
     * This function is called when the front-end of the site wants to
     * create a new location. The front posts all the information about
     * that new location.
     *
     * @since 0.4.3
     */
    public function newLocation() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->newLocation();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the locationEdit view updateLocation method.
     * This function is called when the front-end of the site wants to
     * update a new location. The front posts all the information about
     * that modified location.
     *
     * @since 0.4.3
     */
    public function updateLocation() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->updateLocation();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + OBSERVERS VIEW APIs */
    /**
     * API - GET
     * Function executes the specified view getObservers method.
     * This function is called when the front-end of the site needs all
     * the observers from the database.
     *
     * @since 0.4.2
     */
    public function getObservers() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObservers();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - DELETE
     * Function executes the observers view deleteObserver method. This function is called when
     * a location has to be deleted.
     *
     * @since 0.5.1
     */
    public function deleteObserver() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->deleteObserver();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + OBSERVER EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the observerEdit view getObserverCodes method.
     * This function is called when the front-end of the site needs all
     * the observer codes.
     *
     * @since 0.5.2
     */
    public function getObserverCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObserverCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * getCountries goes trough the same task as in the LOCATION EDIT part

    /**
     * API - GET
     * Function executes the observerEdit view getObserver method.
     * This function is called when the front-end of the site needs all
     * the information about one observer from the database.
     *
     * @since 0.5.2
     */
    public function getObserver() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObserver();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - POST
     * Function executes the observerEdit view newObserver method.
     * This function is called when the front-end of the site wants to
     * create a new observer. The front posts all the information about
     * that new observer.
     *
     * @since 0.5.2
     */
    public function newObserver() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->newObserver();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the observerEdit view updateObserver method.
     * This function is called when the front-end of the site wants to
     * update an observer. The front posts all the information about
     * that modified observer.
     *
     * @since 0.5.2
     */
    public function updateObserver() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->updateObserver();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + BEACONS VIEW APIs */
    /**
     * API - GET
     * Function executes the specified view getBeacons method.
     * This function is called when the front-end of the site needs all
     * the beacons from the database.
     *
     * @since 0.6.1
     */
    public function getBeacons() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getBeacons();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - DELETE
     * Function executes the specified view deleteBeacon method.
     * This function is called when a beacon has to be deleted.
     *
     * @since 0.6.1
     */
    public function deleteBeacon() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->deleteBeacon();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + OBSERVER EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the given view getBeaconCodes method.
     * This function is called when the front-end of the site needs all
     * the beacon codes.
     *
     * @since 0.6.2
     */
    public function getBeaconCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getBeaconCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * getCountries goes trough the same task as in the LOCATION EDIT part

    /**
     * API - GET
     * Function executes the given view getBeacon method.
     * This function is called when the front-end of the site needs all
     * the information about one beacon from the database.
     *
     * @since 0.6.2
     */
    public function getBeacon() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getBeacon();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - POST
     * Function executes the given view newBeacon method.
     * This function is called when the front-end of the site wants to
     * create a new beacon. The front posts all the information about
     * that new beacon.
     *
     * @since 0.6.2
     */
    public function newBeacon() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->newBeacon();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the given view updateBeacon method.
     * This function is called when the front-end of the site wants to
     * update a beacon. The front posts all the information about
     * that modified beacon.
     *
     * @since 0.6.2
     */
    public function updateBeacon() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->updateBeacon();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }
}
