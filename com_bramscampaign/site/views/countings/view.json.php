<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 *
 * ! NOTE that this class and its functions will probably be deleted in the near future
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Input\Input;

/**
 * HTML View class for the BramsCampaign Component
 *
 * @since  0.0.1
 */
class BramsCampaignViewCountings extends HtmlView {
    private $csv_headers = array(
        'filename',
        'file_start',
        'start (s)',
        'end (s)',
        'frequency_min (Hz)',
        'frequency_max (Hz)',
        'type',
        'top (px)',
        'left (px)',
        'bottom (px)',
        'right (px)',
        'sample_rate (Hz)',
        'fft',
        'overlap',
        'color_min',
        'color_max'
    );
    /**
     * Function makes sure to get the application input. If it fails, it
     * will return false
     *
     * @return boolean|Input
     * @since 0.0.1
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

    /**
     * Function is the entrypoint to get all the campaigns
     * for the countings page. It uses the campaigns model instead
     * of the countings model.
     * It returns all the campaigns in a JSON array
     *
     * @throws Exception
     * @since 0.1.1
     */
    public function getAll() {
        $model = $this->getModel('campaigns');
        // if an error occurred in the model
        if (($campaigns = $model->getCampaigns(Factory::getApplication()->getIdentity()->id)) === -1) return;

        echo new JResponseJson($campaigns);
    }

    /**
     * Function is the entrypoint to create a new counting.
     * A counting is a unique link between a user and a campaign.
     *
     * @throws Exception
     * @since 0.1.1
     */
    public function create() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) return;

        // get all the info needed for a new counting creation
        $counting_info = $input->get('counting_info', array(), 'ARRAY');
        $model = $this->getModel();

        // try to create the new counting, return empty if an error occurs
        if ($model->createCounting($counting_info) === -1) return;

        // if everything goes as planned, return a confirmation message
        echo new JResponseJson(
            array(
                ('message') => 'Counting has created for user '
                    . Factory::getApplication()->getIdentity()->id
                    . ' and for campaign ' . $counting_info['campaign_id']
            )
        );
    }

    public function getCSV() {
        $_SESSION['downloadStatus'] = array('status' => 'pending');
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) return -1;
        $response = array();
        $response[] = $this->csv_headers;

        // get the id of the counting to get files from
        $campaign_id        = $input->get('id');

        // initialise the models
        $spectrogram_model  = $this->getModel('spectrogram');
        $campaign_model     = $this->getModel('campaigns');

        // get all the needed data
        if (($campaign = $campaign_model->getCampaign($campaign_id)) === -1)                return -1;
        if (($spectrograms = $spectrogram_model->getSpectrogramsDB($campaign[0])) === -1)   return -1;

        foreach ($spectrograms as $spectrogram) {
            $response[] = $this->_csv_meteor($spectrogram);
        }

        echo new JResponseJson(array(
            ('csv_header')  => $this->csv_headers,
            ('csv_data')    => $response
        ));
    }

    private function _csv_meteor($spectrogram) {
        date_default_timezone_set('UTC');

        $sample_rate    = (float)   $spectrogram->sample_rate;
        $fft            = (int)     $spectrogram->fft;
        $overlap        = (int)     $spectrogram->overlap;
        $top            = (int)     $spectrogram->top;
        $bottom         = (int)     $spectrogram->bottom;
        $left           = (int)     $spectrogram->left;
        $right          = (int)     $spectrogram->right;
        $freq_0         = (float)   $spectrogram->frequency_min;
        $height         = (int)     $spectrogram->height;
        $precise_start  = (int)     $spectrogram->precise_start;

        $nonOverlap = $fft - $overlap;
        $half       = $fft / 2.0;
        $df         = $sample_rate / $fft;

        $top        = $height - $top;
        $bottom     = $height - $bottom;

        $start      = ($nonOverlap * $left + $half) / $sample_rate;
        $end        = ($nonOverlap * $right + $half) / $sample_rate;
        $freq_min   = $freq_0 + $df * $bottom;
        $freq_max   = $freq_0 + $df * $top;

        $file_start = str_replace(
            array('-', ' ', ':'),
            array('', '_', ''),
            substr($spectrogram->start, 0, 16)
        );

        return array(
            substr_replace(basename($spectrogram->path, '.tar') . '.wav', $file_start, 11, 13),
            date("Y-m-d\TH:i:s", $precise_start / 1000000) . sprintf('.%06d', $precise_start % 1000000),
            $start,
            $end,
            $freq_min,
            $freq_max,
            $spectrogram->type,
            $top,
            $left,
            $bottom,
            $right,
            $sample_rate,
            $fft,
            $overlap,
            $spectrogram->color_min,
            $spectrogram->color_max
        );
    }
}
