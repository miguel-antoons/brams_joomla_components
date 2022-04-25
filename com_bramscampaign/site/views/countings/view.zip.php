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

    public function getSpectrograms() {
        // if an error occurred when getting the app input, stop the function
        if (!$input = $this->getAppInput()) return -1;
        $config = new JConfig();

        // get the id of the counting to get files from
        $campaign_id        = $input->get('id');
        $annotated          = (int) $input->get('annotated');
        // initialise the models
        $spectrogram_model  = $this->getModel('spectrogram');
        $campaign_model     = $this->getModel('campaigns');

        // get all the needed data
        if (($campaign = $campaign_model->getCampaign($campaign_id)) === -1)            return -1;
        if (($spectrograms = $spectrogram_model->getSpectrograms($campaign[0])) === -1) return -1;

        // create the zip file
        $file_name = $config->tmp_path . '/original_spectrograms.zip';

        if ($annotated === 1)   $this->getOriginalSpectrograms($spectrograms, $file_name);
        else                    $this->getAnnotatedSpectrograms($spectrogram_model, $spectrograms, $file_name);

        // send the zip file so the user can download it
        header("Content-Disposition: attachment; filename=original_spectrograms.zip");
        header("Content-Type: application/zip");
        readfile($file_name);

        return 1;
    }

    private function getOriginalSpectrograms($spectrograms, $file_name) {
        $zip = new ZipArchive();

        // add all the spectrograms to the zip file
        if ($zip->open($file_name, ZipArchive::CREATE|ZipArchive::OVERWRITE)) {
            foreach ($spectrograms as $spectrogram) {
                $zip->addFile(JPATH_ROOT.'/ProjectDir'.$spectrogram->url, $spectrogram->filename);
            }
        }

        $zip->close();
    }

    private function getAnnotatedSpectrograms($spectrogram_model, $spectrograms, $file_name) {
        $zip = new ZipArchive();

        // add all the spectrograms to the zip file
        if ($zip->open($file_name, ZipArchive::CREATE|ZipArchive::OVERWRITE)) {
            foreach ($spectrograms as $spectrogram) {
                $image_path = JPATH_ROOT.'/ProjectDir'.$spectrogram->url;

                // generate the image and the rectangle color
                $image      = imagecreatefrompng($image_path);
                $red_color  = imagecolorallocate($image, 255, 0, 0);

                // get all the meteor coordinates
                $meteors = $spectrogram_model->getMeteors($spectrogram->id);

                // add meteor rectangles one by one
                foreach ($meteors as $meteor) {
                    $top    = $meteor->top;
                    $left   = $meteor->left;
                    $bottom = $meteor->bottom;
                    $right  = $meteor->right;
                    imagerectangle($image, $left, $top, $right, $bottom, $red_color);
                }

                // save the image with the rectangles to a string
                ob_start();
                imagepng($image);
                $contents = ob_get_contents();
                ob_end_clean();

                imagecolorallocate($image, $red_color);
                imagedestroy($image);
                $zip->addFromString($spectrogram->filename, $contents);
            }
        }

        $zip->close();
    }
}
