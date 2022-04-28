<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Input\Input;

/**
 * Class inserts or updates a couting and generates
 * a JSON response for front-end.
 * @since 0.3.0
 */
class BramsCampaignViewCountingEdit extends HtmlView {
    /**
     * Function makes sure to get the application input. If it fails, it
     * will return false
     *
     * @return boolean|Input
     * @since 0.2.5
     */
    private function getAppInput() {
        try {
            return Factory::getApplication()->input;
        } catch (Exception $e) {
            // if an exception occurs, return false to front-end
            echo new JResponseJson(array(('message') => $e));
            // log the exception
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    public function getSpectrograms() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) return;

        // get the id of the counting to get files from
        $campaign_id        = $input->get('id');
        // initialise the models
        $spectrogram_model  = $this->getModel('spectrogram');
        $campaign_model     = $this->getModel('campaigns');

        // get all the needed data
        if (($campaign = $campaign_model->getCampaign($campaign_id)) === -1)            return;
        if (($spectrograms = $spectrogram_model->getSpectrograms($campaign[0])) === -1) return;

        $final_spectrograms = array();

        foreach ($spectrograms as $spectrogram) {
            $new_spectrogram            = new stdClass();
            $new_spectrogram->url       = $spectrogram->url;
            $new_spectrogram->height    = $spectrogram->height;
            $new_spectrogram->width     = $spectrogram->width;
            $new_spectrogram->start     = $spectrogram->start;
            $new_spectrogram->meteors   = $spectrogram_model->getMeteors($spectrogram->id);

            $final_spectrograms[]       = $new_spectrogram;
        }

        echo new JResponseJson($final_spectrograms);
    }
}
