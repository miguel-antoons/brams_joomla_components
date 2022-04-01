<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.6.2
 */
class BramsAdminViewBeaconEdit extends HtmlView {
    /**
     * Display the beacon Form view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @since 0.6.2
     */
    function display($tpl = null) {
        // Display the view
        parent::display($tpl);
        // add javascript and css
        $this->setDocument();
    }

    // function adds needed javascript and css files to the view
    private function setDocument() {
        $document = Factory::getDocument();
        $document->addStyleSheet('/components/com_bramsadmin/views/beaconedit/css/beaconedit.css');
        $document->addStyleSheet('/components/com_bramsadmin/views/beaconedit/css/bootstrap.min.css');
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        $document->addScript('/components/com_bramsadmin/views/beaconedit/js/beaconedit.js');
        $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
    }
}
