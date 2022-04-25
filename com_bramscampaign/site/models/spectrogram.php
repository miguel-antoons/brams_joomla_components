<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

require JPATH_ROOT.DS.'components/com_bramscampaign/models/Lib/Archive.php';
/**
 * Spectrogram Model
 *
 * Edits, inserts and deletes data concerning the BRAMS
 * campaigns.
 *
 * @since  0.0.1
 */
class BramsCampaignModelSpectrogram extends BaseDatabaseModel {
    private $spectrogram_not_found = array(
        'id'        => 0,
        'url'       => '/img/image_not_found.png',
        'width'     => 864,
        'height'    => 595
    );
    // function connects to the database and returns the database object
    private function connectToDatabase() {
        try {
            /* Below lines are for connecting to production database later on */
            // $database_options = array();

            // $database_options['driver']      = $_ENV['DB_DRIVER'];
            // $database_options['host']        = $_ENV['DB_HOST'];
            // $database_options['user']        = $_ENV['DB_USER'];
            // $database_options['password']    = $_ENV['DB_PASSWORD'];
            // $database_options['database']    = $_ENV['DB_NAME'];
            // $database_options['prefix']      = $_ENV['DB_PREFIX'];

            // return JDatabaseDriver::getInstance($database_options);

            /*
            below line is for connecting to default joomla database
            WARNING : this line should be commented/removed for production
            */
            return $this->getDbo();
        } catch (Exception $e) {
            // if an error occurs, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    /**
     * @throws Exception
     *
     * @since 0.2.0
     */
    public function getSpectrograms($campaign) {
        $spectrograms = array();

        $options = array(
            ('raw')         => true,
            ('system_id')   => $campaign->system,
            ('fft')         => $campaign->fft,
            ('start')       => $campaign->start,
            ('end')         => $campaign->end,
            ('overlap')     => $campaign->overlap,
            ('color_min')   => $campaign->colorMin,
            ('color_max')   => $campaign->colorMax
        );

        $files = $this->getFileIDs($campaign->system, $campaign->start, $campaign->end);
        if (($system_code = $this->getSystemCode($campaign->system)) === -1) return -1;
        $system_code = $system_code[0]->system_code;

        foreach ($files as $file) {
            $archive = new Archive(true);
            $spectrogram_info = $this->getSingleSpectrogram($file->id, $options);
            if (count($spectrogram_info) === 0 || $spectrogram_info === -1) $spectrogram_info = false;
            else $spectrogram_info[0]->filename = basename($spectrogram_info[0]->url);
            $spectrogram = $archive->getSpectrogram(new DateTime($file->start), $system_code, $options);

            if ($spectrogram_info && $spectrogram)  $spectrograms[] = (object) $spectrogram_info[0];
            elseif ($spectrogram)                   $spectrograms[] = (object) $spectrogram;
            else                                    $spectrograms[] = (object) $this->spectrogram_not_found;
        }

        return $spectrograms;
    }

    public function getSpectrogramsDB($campaign) {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $files = $this->getFileIDs($campaign->system, $campaign->start, $campaign->end);
        $file_ids = '(';

        foreach ($files as $file) {
            $file_ids .= '\'' . $file->id . '\', ';
        }
        $file_ids = substr($file_ids, 0, -2);
        $file_ids .= ')';

        $spectrogram_query = $db->getQuery(true);

        // query to get the spectrogram row with data equal to the provided options
        $spectrogram_query->select(
            $db->quoteName('file.path')                         . ' as path, '
            . $db->quoteName('file.start')                      . ' as start, '
            . $db->quoteName('file.precise_start')              . ' as precise_start, '
            . $db->quoteName('file.sample_rate')                . ' as sample_rate, '
            . $db->quoteName('manual_counting_meteor.top')      . ' as top, '
            . $db->quoteName('manual_counting_meteor.left')     . ' as \'left\', '
            . $db->quoteName('manual_counting_meteor.right')    . ' as \'right\', '
            . $db->quoteName('manual_counting_meteor.bottom')   . ' as bottom, '
            . $db->quoteName('manual_counting_meteor.type')     . ' as type, '
            . $db->quoteName('spectrogram.height')              . ' as height, '
            . $db->quoteName('spectrogram.fft')                 . ' as fft, '
            . $db->quoteName('spectrogram.overlap')             . ' as overlap, '
            . $db->quoteName('spectrogram.color_min')           . ' as color_min, '
            . $db->quoteName('spectrogram.color_max')           . ' as color_max, '
            . $db->quoteName('spectrogram.frequency_min')       . ' as frequency_min'
        );
        $spectrogram_query->from($db->quoteName('manual_counting_meteor'));
        $spectrogram_query->join(
            'INNER',
            $db->quoteName('spectrogram')
            . ' ON '
            . $db->quoteName('manual_counting_meteor.spectrogram_id')
            . ' = '
            . $db->quoteName('spectrogram.id')
        );
        $spectrogram_query->join(
            'INNER',
            $db->quoteName('file')
            . ' ON '
            . $db->quoteName('spectrogram.file_id')
            . ' = '
            . $db->quoteName('file.id')
        );
        $spectrogram_query->where($db->quoteName('spectrogram.file_id') . ' in ' . $file_ids);
        $spectrogram_query->order($db->quoteName('manual_counting_meteor.id'));

        Log::add($spectrogram_query, Log::ERROR, 'error');

        $db->setQuery($spectrogram_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    /**
     * Function returns all the file ids for a given system_id, start date and
     * end date.
     *
     * @param $system_id    int     system_id of the file ids to return
     * @param $start        string  start date of the file ids to return
     * @param $end          string  end date of the file ids to return
     *
     * @return int|array    returns -1 if an error occurs, the array with file ids on success
     *
     * @since 0.2.0
     */
    private function getFileIDs($system_id, $start, $end) {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $file_query = $db->getQuery(true);

        // query to select all the file ids
        $file_query->select(
            $db->quoteName('id') . ', '
            . $db->quoteName('start')
        );
        $file_query->from($db->quoteName('file'));
        $file_query->where($db->quoteName('system_id')  . ' = '     . $db->quote($system_id));
        $file_query->where($db->quoteName('start')      . ' >= '    . $db->quote($start));
        $file_query->where($db->quoteName('start')      . ' < '     . $db->quote($end));

        $db->setQuery($file_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    private function getSingleSpectrogram($file_id, $options = array()) {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $spectrogram_query = $db->getQuery(true);

        // query to get the spectrogram row with data equal to the provided options
        $spectrogram_query->select(
            $db->quoteName('id')                . ', '
            . $db->quoteName('url')             . ', '
            . $db->quoteName('width')           . ', '
            . $db->quoteName('height')          . ', '
            . $db->quoteName('fft')             . ', '
            . $db->quoteName('overlap')         . ', '
            . $db->quoteName('color_min')       . ', '
            . $db->quoteName('color_max')       . ', '
            . $db->quoteName('frequency_min')   . ', '
            . $db->quoteName('frequency_max')
        );
        $spectrogram_query->from('spectrogram');
        $spectrogram_query->where($db->quoteName('spectrogram.file_id') . ' = ' . $db->quote($file_id));
        $spectrogram_query->where($db->quoteName('fft')                 . ' = ' . $db->quote($options['fft']));
        $spectrogram_query->where($db->quoteName('overlap')             . ' = ' . $db->quote($options['overlap']));
        $spectrogram_query->where($db->quoteName('color_min')           . ' = ' . $db->quote($options['color_min']));
        $spectrogram_query->where($db->quoteName('color_max')           . ' = ' . $db->quote($options['color_max']));

        $db->setQuery($spectrogram_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    private function getSystemCode($system_id) {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $system_query = $db->getQuery(true);

        // query to get the system code
        $system_query->select(
            'concat(' . $db->quoteName('location_code') . ', '
            . $db->quote('_') . ', '
            . $db->quoteName('alias') . ') as system_code'
        );
        $system_query->from($db->quoteName('system'));
        $system_query->from($db->quoteName('location'));
        $system_query->where($db->quoteName('system.location_id')   . ' = ' . $db->quoteName('location.id'));
        $system_query->where($db->quoteName('system.id')            . ' = ' . $db->quote($system_id));

        $db->setQuery($system_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }

    public function getMeteors($spectrogram_id, $campaign_id = null) {
        // if the connection to the database failed, return false
        if (!$db = $this->connectToDatabase()) {
            return -1;
        }
        $meteor_query = $db->getQuery(true);

        // query to get all the meteor coordinates
        $meteor_query->select(
            $db->quoteName('top')       . ', '
            . $db->quoteName('left')    . ', '
            . $db->quoteName('bottom')  . ', '
            . $db->quoteName('right')
        );
        $meteor_query->from($db->quoteName('manual_counting_meteor'));
        $meteor_query->where($db->quoteName('spectrogram_id') . ' = ' . $db->quote($spectrogram_id));

        $db->setQuery($meteor_query);

        // try to execute the query and return the result
        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            // if it fails, log the error and return false
            echo new JResponseJson(array(('message') => $e));
            Log::add($e, Log::ERROR, 'error');
            return -1;
        }
    }
}
