<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Cache\Controller\ViewController;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\View\ViewInterface;

/**
 * BramsData Component Controller
 *
 * @since  0.0.1
 */
class BramsDataController extends BaseController {
	/**
	 * * CHANGES :
	 * *      - if $block_display is set to true, the function
	 * *        will NOT call the views display method and returns the view instead.
	 * *      - if no model is found for the view name, the function will search a
	 * *        model that has a name matching the requested view name without the last
	 * *        4 letters.
	 * *      - if there are models specified in the url, the display function will
	 * *        initialize and add the models to the view in order to use them.
	 *
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param boolean $cacheable If true, the view output will be cached
	 * @param array $url_params An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return BramsDataController|ViewInterface
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
	 * API - GET
	 * Function call the view function that returns all the availability info to
	 * front-end.
	 *
	 * @since 0.3.5
	 */
	public function getAvailability() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->getAvailability();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}

    /**
     * API
     * Api to make the spectrogram images
     */
	public function makeImages() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->makeImages();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}

    /**
     * API - GET
     * Api to get all the spectrogram images in png form.
     */
	public function getImage() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->getImage();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}

    /**
     * API
     * Api to get a spectrogram image as attachment
     */
	public function saveImage() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->saveImage();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}

    /**
     * API
     * Api to get a WAV file as attachment
     */
	public function saveWav() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->saveWav();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}

    /**
     * API - GET
     * Api to get PSD values and labels.
     */
	public function getPSD() {
		if (Jsession::checkToken('get')) {
			try {
				$view = $this->display(false, array(), true);
			} catch (Exception $e) {
				echo new JResponseJson(array(('message') => $e));
				Log::add($e, Log::ERROR, 'error');

				return;
			}
			$view->getPSD();
		} else {
			echo new JResponseJson(array(('message') => false));
		}
	}
}
