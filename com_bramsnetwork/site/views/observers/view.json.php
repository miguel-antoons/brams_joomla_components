<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsNetwork Component
 *
 * @since  0.2.1
 */
class BramsNetworkViewObservers extends HtmlView {

    /**
     * Function gets all the observers and returns it to the sites front-end.
     * If the function fails, it will return nothing and send false to the sites
     * front-end.
     *
     * @return void
     *
     * @since 0.3.5
     */
    public function getObservers() {
        if (!$observer_info = (array) $this->get('ObserverInfo')) {
            // return an error response to front-end and stop the function
            echo new JResponseJson(array(('message') => false));
            return;
        }

        echo new JResponseJson($observer_info);
    }
}
