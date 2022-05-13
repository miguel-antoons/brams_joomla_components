<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Input\Input;

require JPATH_ROOT.DIRECTORY_SEPARATOR.'components/com_bramsdata/models/Lib/Archive.php';

/**
 * HTML View class for the BramsData Component
 *
 * @since  0.4.0
 */
class BramsDataViewViewer extends HtmlView {
    /**
     * Function makes sure to get the application input. If it fails, it
     * will return false
     *
     * @return Input|boolean
     * @since 0.2.5
     */
    private function getAppInput() {
        try {
            return Factory::getApplication()->input;
        } catch (Exception $e) {
            // log the exception
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    public function makeImages() {
        $input          = $this->getAppInput();
        $params         = array(
            'task'      => 'makeImages',
            'begin'     => trim($input->get('begin', '', 'string')),
            'end'       => trim($input->get('end', '', 'string')),
            'station'   => $input->get('station')
        );

        if ($fMin = $input->get('fmin', false)) $params['fmin'] = $fMin;
        if ($fMax = $input->get('fmax', false)) $params['fmax'] = $fMax;

        $archive        = new Archive();

        echo new JResponseJson(array($archive->get($params)));
    }
}
