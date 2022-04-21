<?php

App::uses('Archive', 'Lib');
App::uses('System', 'Model');

class Spectrogram extends AppModel {
    public $useTable = 'spectrogram';

    public $belongsTo = array(
        'DataFile' => array(
            'className' => 'DataFile',
            'foreignKey' => 'file_id'
        )
    );

    public function get($file_id, $fft, $overlap, $color_min, $color_max) {
        $data = array(
            'file_id' => $file_id,
            'fft' => $fft,
            'overlap' => $overlap,
            'color_min' => $color_min,
            'color_max' => $color_max
        );

        /* Check if the spectrogram is already in the database. */
        $spect = $this->findByparameters($data);
        if ($spect) {
            /* Ensure that image is in cache. */
            $img = $this->_createSpectrogram($data);
            if (!$img) {
                return $this->spectrogramNotFound();
            }
            return $spect['Spectrogram'];
        }

        /* Otherwise, create it. Force the cache to create the image again
         * since we need 'frequency_min' and 'frequency_max'. */
        $img = $this->_createSpectrogram($data, true);
        if (!$img) {
            return $this->spectrogramNotFound();
        }

        $data['url'] = $img['url'];
        $data['width'] = $img['width'];
        $data['height'] = $img['height'];
        $data['frequency_min'] = $img['frequency_min'];
        $data['frequency_max'] = $img['frequency_max'];
        $this->create($data);
        if (!$this->save()) {
            return $this->spectrogramNotFound();
        }

        $data['id'] = $this->id;
        return $data;
    }

    public function findByParameters($data) {
        return $this->find('first', array('conditions' => $data));
    }

    public function spectrogramNotFound() {
        return array('id'  => 0,
                     'url' => '/img/image_not_found.png',
                     'width' => 864,
                     'height' => 595);
    }

    private function _createSpectrogram($data, $force = false) {
        $file = new DataFile($data['file_id']);
        if (!$file->read()) {
            return false;
        }
        $start = $file->data['DataFile']['start'];

        $system = new System($file->data['DataFile']['system_id']);
        if (!$system->read()) {
            return false;
        }
        $system_code = $system->data['System']['system_code'];

        $options['raw'] = true;
        $options['fft'] = $data['fft'];
        $options['overlap'] = $data['overlap'];
        $options['color_min'] = $data['color_min'];
        $options['color_max'] = $data['color_max'];

        $ar = new Archive(!$force);
        return $ar->getSpectrogram(new DateTime($start), $system_code, $options);
    }
}
