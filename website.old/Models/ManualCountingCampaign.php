<?php

App::uses('DataFile', 'Model');

class ManualCountingCampaign extends AppModel {
    public $name = 'ManualCountingCampaign';
    public $useTable = 'manual_counting_campaign';
        public $displayField = 'name';
    public $recursive = 0;

    public $virtualFields = array(
        /* Too slow when paginated, maybe because of the join?
        'file_count' => 'SELECT COUNT(*) FROM file as DataFile
                            WHERE DataFile.start >= ManualCountingCampaign.start
                            AND DataFile.start < ManualCountingCampaign.end
                            AND DataFile.system_id = ManualCountingCampaign.system_id'*/
        'file_count' => 0
    );

        public $belongsTo = array(
                'System' => array(
                        'className' => 'System',
                        'foreignKey' => 'system_id'
                ),
        'Type' => array(
            'className' => 'ManualCountingCampaignType',
            'foreignKey' => 'type_id'
        )
    );

    public $hasMany = array(
        'ManualCounting' => array(
            'className' => 'ManualCounting',
            'foreignKey' => 'campaign_id'
        )
    );

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                                'rule' => 'notBlank',
                                'message' => 'The name cannot be left blank'
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This campaign name has already been taken'
            )
        ),
        'system_id' => array(
            'notBlank' => array(
                                'rule' => 'notBlank',
                                'message' => 'The station cannot be left blank'
            ),
        ),
        'type_id' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'The campaign type cannot be left blank'
            ),
        ),
        'start' => array(
            'datetime' => array(
                'rule' => array('datetime', 'ymd'),
                'message' => 'The starting date is invalid'
            )
        ),
        'end' => array(
            'datetime' => array(
                'rule' => array('datetime', 'ymd'),
                'message' => 'The ending date is invalid'
            )
        ),
        'fft' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'This value must be a number',
                'allowEmpty' => true
            ),
            'positive' => array(
                'rule' => array('comparison', '>', 0),
                'message' => 'The number of FFT points must be greater than zero',
                'allowEmpty' => true
            )
        ),
        'overlap' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'This value must be a number',
                'allowEmpty' => true
            ),
            'positive' => array(
                'rule' => array('comparison', '>=', 0),
                'message' => 'The overlap must be greater than or equal to zero',
                'allowEmpty' => true
            ),
            'lessThan' =>  array(
                'rule' => array('lessThan', 'fft'),
                'message' => 'The overlap must be less than number of FFT points',
                'allowEmpty' => true
            )
        ),
        'color_min' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'This value must be a number',
                'allowEmpty' => true
            ),
            'positive' => array(
                'rule' => array('comparison', '>=', 0),
                'message' => 'The value for the darkest color must positive',
                'allowEmpty' => true
            )
        ),
        'color_max' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'This value must be a number',
                'allowEmpty' => true
            ),
            'positive' => array(
                'rule' => array('comparison', '>=', 0),
                'message' => 'The value for the brightest color must positive',
                'allowEmpty' => true
            ),
            'greaterThan' => array(
                'rule' => array('greaterThan', 'color_min'),
                'message' => 'Color max must be greater than color min',
                'allowEmpty' => true
            )
        )
    );

    public function afterFind($results, $primary = false) {
        foreach ($results as $key => $val) {
            if (isset($val['ManualCountingCampaign']['start'], $val['ManualCountingCampaign']['end'], $val['ManualCountingCampaign']['system_id'])) {
                $results[$key]['ManualCountingCampaign']['file_count'] = $this->file_count(
                    $val['ManualCountingCampaign']['start'],
                    $val['ManualCountingCampaign']['end'],
                    $val['ManualCountingCampaign']['system_id']
                );
            }
        }
        return $results;
    }

    public function file_count($start, $end, $system_id) {
        $conditions = array(
            'DataFile.start >= ' => $start,
            'DataFile.start < ' => $end,
            'DataFile.system_id' => $system_id
        );
        $file = new DataFile();
        return $file->find('count', array('conditions' => $conditions));
    }


    public function newCounting($id = null, $user_id) {
        if ($id !== null && $id !== $this->id) {
            $this->read(null, $id);
        }

        $file = new DataFile();
        $file_id = $file->field('id', array('start' => $this->data[$this->alias]['start'],
                                            'system_id' => $this->data[$this->alias]['system_id']));

        $this->ManualCounting->create();
        $this->ManualCounting->set('user_id', $user_id);
        $this->ManualCounting->set('file_id', $file_id);
        $this->ManualCounting->set('campaign_id', $this->id);
        return $this->ManualCounting->save();
    }

    public function findDataFiles($id = null) {
        if ($id !== null && $id !== $this->id) {
            $this->read(null, $id);
        }

        $conditions = array(
            'DataFile.start >= ' => $this->data[$this->alias]['start'],
            'DataFile.start < ' => $this->data[$this->alias]['end'],
            'DataFile.system_id' => $this->data[$this->alias]['system_id']
        );

        $file = new DataFile();
        return $file->find('all', array('conditions' => $conditions));
    }

    public function listDataFiles($id = null) {
        if ($id !== null && $id !== $this->id) {
            $this->read(null, $id);
        }

        $conditions = array(
            'DataFile.start >= ' => $this->data[$this->alias]['start'],
            'DataFile.start < ' => $this->data[$this->alias]['end'],
            'DataFile.system_id' => $this->data[$this->alias]['system_id']
        );

        $file = new DataFile();
        $dataFiles = $file->find('list', array('conditions' => $conditions));
        foreach ($dataFiles as $key => $item) {
            $dataFiles[$key] = substr($item, 0, 16);
        }
        return $dataFiles;

    }

    public function findSpectrograms($id = null) {
        if ($id !== null && $id !== $this->id) {
            $this->read(null, $id);
        }

        $spectrograms = array();
        $spectrogram = new Spectrogram();

        $fft = $this->data[$this->alias]['fft'];
        $overlap = $this->data[$this->alias]['overlap'];
        $color_min = $this->data[$this->alias]['color_min'];
        $color_max = $this->data[$this->alias]['color_max'];

        $files = $this->findDataFiles();
        foreach ($files as $file) {
            $spectrograms[] = $spectrogram->get($file['DataFile']['id'], $fft, $overlap, $color_min, $color_max);
        }

        return $spectrograms;
    }

    public function findZooCampaign($id = null) {
        return $this->find('first', array('conditions' => array('ManualCountingCampaign.type_id' => 'ZOO')));
    }
}