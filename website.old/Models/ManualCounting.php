<?php

App::uses('Location', 'Model');
App::uses('Spectrogram', 'Model');

class ManualCounting extends AppModel {
    public $recursive = 0;
    public $name = 'ManualCounting';
    public $useTable = 'manual_counting';

    public $virtualFields = array(
        /* Too slow when paginated, maybe because of the join?
        'progress' => 'SELECT COUNT(*) FROM file as F
                           WHERE F.start >= ManualCountingCampaign.start
                           AND F.start < DataFile.start
                           AND F.system_id = DataFile.system_id'*/
        'progress' => 0,
        'state' => 'CASE WHEN ManualCounting.file_id IS NULL THEN \'C\' ELSE \'R\' END'
    );

        public $belongsTo = array(
                'ManualCountingCampaign' => array(
                        'className' => 'ManualCountingCampaign',
                        'foreignKey' => 'campaign_id',
        ),
        'User' => array(
            'className' => 'User',
                        'foreignKey' => 'user_id',
        ),
        'DataFile' => array(
            'className' => 'DataFile',
            'foreignKey' => 'file_id'
        )
    );

    public $hasMany = array(
        'ManualCountingMeteor' => array(
            'className'  => 'ManualCountingMeteor',
            'foreignKey' => 'manual_counting_id'
        )
    );

    public $validate = array(
        'campaign_id' => array(
            'notBlank' => array(
                                'rule' => 'notBlank',
                                'message' => 'The campaign cannot be left blank'
            )
        )
    );

    public function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
            if (isset($val['ManualCountingCampaign']['start'], $val['DataFile']['start'], $val['DataFile']['system_id'])) {
                $results[$key]['ManualCounting']['progress'] = $this->progress(
                    $val['ManualCountingCampaign']['start'],
                    $val['DataFile']['start'],
                    $val['DataFile']['system_id']
                );
            }
        }
        return $results;
    }

    public function spectrogram() {
        $spect = new Spectrogram();

        if (!$this->id || $this->data[$this->alias]['file_id'] === null) {
            return $spect->spectrogramNotFound();
        }

        return $spect->get($this->data[$this->alias]['file_id'],
                           $this->data['ManualCountingCampaign']['fft'],
                           $this->data['ManualCountingCampaign']['overlap'],
                           $this->data['ManualCountingCampaign']['color_min'],
                           $this->data['ManualCountingCampaign']['color_max']);
    }

    public function findMeteors() {
        $conditions = array(
            'ManualCountingMeteor.manual_counting_id' => $this->id,
            'ManualCountingMeteor.spectrogram_id' => $this->_spectrogramId()
        );
        return $this->ManualCountingMeteor->find('all', array('conditions' => $conditions));
    }

    public function findAllMeteors() {
        $conditions = array(
            'ManualCountingMeteor.manual_counting_id' => $this->id,
        );

        $this->ManualCountingMeteor->unbindModel(array('belongsTo' => array('ManualCounting')));

        return $this->ManualCountingMeteor->find('all', array('conditions' => $conditions,
                                                              'recursive' => 2));
    }

    public function progress($campaign_start, $start, $system_id) {
        $conditions = array(
            'DataFile.start >= ' => $campaign_start,
            'DataFile.start < ' => $start,
            'DataFile.system_id' => $system_id
        );
        return $this->DataFile->find('count', array('conditions' => $conditions));
    }

    public function previous($data = null) {
        if (!isset($data['ManualCounting']['id'])) {
            return false;
        }

        $id = $data['ManualCounting']['id'];
        $this->read(null, $id);

        $spect_id = $this->_spectrogramId();
        if (!$spect_id) {
            return false;
        }

        $db = $this->getDataSource();
        $db->begin();

        $this->ManualCountingMeteor->deleteAll(array(
            'ManualCountingMeteor.manual_counting_id' => $id,
            'ManualCountingMeteor.spectrogram_id' => $spect_id
        ));

        if (isset($data['ManualCountingMeteor'])) {
            foreach ($data['ManualCountingMeteor'] as &$meteor) {
                $meteor['manual_counting_id'] = $id;
                $meteor['spectrogram_id'] = $spect_id;
            }
            if (!$this->ManualCountingMeteor->saveMany($data['ManualCountingMeteor'])) {
                $db->rollback();
                return false;
            }
        }

        $conditions = array(
            'system_id' => $this->data['DataFile']['system_id'],
            'start >=' => $this->data['ManualCountingCampaign']['start'],
            'start <' => $this->data['DataFile']['start']
        );

        $file_id = $this->DataFile->field('id', $conditions, 'start DESC');
        if (!$file_id) {
            // No more spectrograms.
            $db->commit();
            return null;
        }

        $this->set('file_id', $file_id);
        if ($this->save() === false) {
            $db->rollback();
            return false;
        }
        $db->commit();
        /* Note that save() discards the data of current model. */
        return $file_id !== null;
    }

    public function next($data = null) {
        if (!isset($data['ManualCounting']['id'])) {
            return false;
        }

        $id = $data['ManualCounting']['id'];
        $this->read(null, $id);

        $spect_id = $this->_spectrogramId();
        if (!$spect_id) {
            return false;
        }

        $db = $this->getDataSource();
        $db->begin();

        $this->ManualCountingMeteor->deleteAll(array(
            'ManualCountingMeteor.manual_counting_id' => $id,
            'ManualCountingMeteor.spectrogram_id' => $spect_id
        ));

        if (isset($data['ManualCountingMeteor'])) {
            foreach ($data['ManualCountingMeteor'] as &$meteor) {
                $meteor['manual_counting_id'] = $id;
                $meteor['spectrogram_id'] = $spect_id;
            }
            if (!$this->ManualCountingMeteor->saveMany($data['ManualCountingMeteor'])) {
                $db->rollback();
                return false;
            }
        }

        $conditions = array(
            'system_id' => $this->data['DataFile']['system_id'],
            'start >' => $this->data['DataFile']['start'],
            'start <' => $this->data['ManualCountingCampaign']['end']
        );

        $file_id = $this->DataFile->field('id', $conditions, 'start');
        if (!$file_id) {
            // No more spectrograms.
            $db->commit();
            return null;
        }

        $this->set('file_id', $file_id);
        if ($this->save() === false) {
            $db->rollback();
            return false;
        }
        $db->commit();
        /* Note that save() discards the data of current model. */
        return $file_id !== null;
    }

    public function select($data = null) {
        if (!isset($data['ManualCounting']['id'])) {
            return false;
        }

        $id = $data['ManualCounting']['id'];
        $this->read(null, $id);

        $this->log($data, 'debug');

        $this->set('file_id', $data['ManualCounting']['data_file']);
        return $this->save();
    }

    public function stateLabels() {
        return array('R' => 'In Progress', 'C' => 'Completed');
    }

    public function exportCSV() {
        $csv = $this->_csvHeader();

        $meteors = $this->findAllMeteors();
        if ($meteors === false) {
            return false;
        }

        foreach ($meteors as $meteor) {
            $csv .= $this->_csvMeteor($meteor);
        }

        return $csv;
    }

    public function exportSpectrograms($path) {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE|ZipArchive::OVERWRITE);

        $campaign_id = $this->data['ManualCountingCampaign']['id'];
        $spectrograms = $this->ManualCountingCampaign->findSpectrograms($campaign_id);
        foreach ($spectrograms as $spectrogram) {
            $image_path = WWW_ROOT.$spectrogram['url'];
            $zip->addFile($image_path, $this->_imageName($image_path));
        }

        $zip->close();
        return true;
    }

    public function exportAnnotatedSpectrograms($path) {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE|ZipArchive::OVERWRITE);

        $campaign_id = $this->data['ManualCountingCampaign']['id'];
        $spectrograms = $this->ManualCountingCampaign->findSpectrograms($campaign_id);
        foreach ($spectrograms as $spectrogram) {
            $image_path = WWW_ROOT.$spectrogram['url'];

            $image = imagecreatefrompng($image_path);
            $red = imagecolorallocate($image, 255, 0, 0);

            // Draw meteors on image.
            $conditions = array(
                'ManualCountingMeteor.manual_counting_id' => $this->id,
                'ManualCountingMeteor.spectrogram_id' => $spectrogram['id']
            );
            $meteors = $this->ManualCountingMeteor->find('all', array('conditions' => $conditions, 'recursive' => -1));

            foreach ($meteors as $meteor) {
                $top = $meteor['ManualCountingMeteor']['top'];
                $left = $meteor['ManualCountingMeteor']['left'];
                $bottom = $meteor['ManualCountingMeteor']['bottom'];
                $right = $meteor['ManualCountingMeteor']['right'];
                imagerectangle($image, $left, $top, $right, $bottom, $red);
            }

            // Save image to string.
            ob_start();
            imagepng($image);
            $contents = ob_get_contents();
            ob_end_clean();

            imagecolordeallocate($image, $red);
            imagedestroy($image);

            $zip->addFromString($this->_imageName($image_path), $contents);
        }

        $zip->close();
        return true;
    }

    private function _csvHeader() {
        return "filename, file_start, start (s), end (s), frequency_min (Hz), frequency_max (Hz), type, ".
               "top (px), left (px), bottom (px), right (px), sample_rate (Hz), fft, overlap, color_min, color_max\n";
    }

    private function _csvMeteor($data) {
        $sample_rate = (float) $data['Spectrogram']['DataFile']['sample_rate'];

        $height = $data['Spectrogram']['height'];
        $fft = $data['Spectrogram']['fft'];
        $overlap = $data['Spectrogram']['overlap'];
        $z_min = $data['Spectrogram']['color_min'];
        $z_max = $data['Spectrogram']['color_max'];
        $freq_0 = $data['Spectrogram']['frequency_min'];

        $nonoverlap = $fft - $overlap;
        $half = $fft / 2.0;
        $df = $sample_rate / $fft;

        $top = $height - $data['ManualCountingMeteor']['top'];
        $left = $data['ManualCountingMeteor']['left'];
        $bottom = $height - $data['ManualCountingMeteor']['bottom'];
        $right = $data['ManualCountingMeteor']['right'];

        $start = ($nonoverlap*$left + $half) / $sample_rate;
        $end = ($nonoverlap*$right + $half) / $sample_rate;
        $freq_min = $freq_0 + $df * $bottom;
        $freq_max = $freq_0 + $df * $top;

        $file_start = str_replace(
            array('-', ' ', ':'),
            array('', '_', ''),
            substr($data['Spectrogram']['DataFile']['start'], 0, 16)
        );

        $file_name = substr_replace(basename($data['Spectrogram']['DataFile']['path'], '.tar') . '.wav', $file_start, 11, 13);

        $precise_start = $this->_format_time($data['Spectrogram']['DataFile']['precise_start']);

        $str = sprintf('%s, %s, ', $file_name, $precise_start);

        $str .= sprintf('%10.6f, %10.6f, %10.6f, %10.6f, %s, ',
            $start, $end, $freq_min, $freq_max, $data['ManualCountingMeteor']['type']);

        $str .= sprintf('%4d, %4d, %4d, %4d, ', $top, $left, $bottom, $right);

        $str .= sprintf('%10.6f, %5d, %5d, %10.6f, %10.6f', $sample_rate, $fft, $overlap, $z_min, $z_max);

        return $str."\n";
    }

    private function _format_time($time) {
        date_default_timezone_set('UTC');
        return date("Y-m-d\TH:i:s", $time / 1000000) . sprintf('.%06d', $time % 1000000);
    }

    private function _spectrogramId() {
        $parameters = array(
            'file_id' => $this->data['ManualCounting']['file_id'],
            'fft' => $this->data['ManualCountingCampaign']['fft'],
            'overlap' => $this->data['ManualCountingCampaign']['overlap'],
            'color_min' => $this->data['ManualCountingCampaign']['color_min'],
            'color_max' => $this->data['ManualCountingCampaign']['color_max']
        );

        $spect = new Spectrogram();
        if (!($spect = $spect->findByParameters($parameters))) {
            return false;
        }

        return $spect['Spectrogram']['id'];
    }

    private function _imageName($path) {
        return substr(basename($path), 0, 38) . '.png';
    }
}