<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the BramsAdmin Component
 *
 * @since  0.7.2
 */
class BramsCampaignViewCountingEdit extends HtmlView {
    /**
     * Display the Antenna Form view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @throws Exception
     * @since 0.3.0
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
        $document->addStyleSheet('/components/com_bramscampaign/views/_css/edit.css');
        $document->addStyleSheet('/components/com_bramscampaign/views/_css/bootstrap.min.css');
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        $document->addStyleSheet('/components/com_bramscampaign/views/countingedit/css/countingEdit.css');
        $document->addStyleSheet('/components/com_bramscampaign/views/countingedit/fancybox/jquery.fancybox.css');
        $document->addScript('/components/com_bramscampaign/views/_js/edit.js');
        $document->addScript('/components/com_bramscampaign/views/countingedit/js/countingEdit.js');
        $document->addScript('/components/com_bramscampaign/views/countingedit/js/detect-zoom.js');
        $document->addScript('/components/com_bramscampaign/views/countingedit/fancybox/jquery.fancybox.pack.js');
        $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
    }
}
