<?php
class ManualCountingMeteor extends AppModel {
    public $useTable = 'manual_counting_meteor';

    public $belongsTo = array(
                'ManualCounting' => array(
                        'className' => 'ManualCounting',
                        'foreignKey' => 'manual_counting_id',
        ),
        'Spectrogram' => array(
                        'className' => 'Spectrogram',
                        'foreignKey' => 'spectrogram_id',
        )
    );

    public $validate = array(
        'type' => array(
            'isMeteorType' => array(
                                'rule' => 'validateMeteorType',
                                'message' => 'Invalid meteor type'
            )
        )
    );

    public function beforeSave($options = array()) {
        if (isset($this->data['ManualCountingMeteor']['type'])) {
            $this->data['ManualCountingMeteor']['type'] =
                strtoupper($this->data['ManualCountingMeteor']['type']);
        }
        return true;
    }

    public function isMeteorType($type) {
        $type = strtoupper($type);
        return $type == '' || $type == 'S' || $type == 'U' || $type = 'L' || $type == 'O' || $type == 'H' || $type == 'E';
    }

    public function validateMeteorType($field = array()) {
        foreach ($field as $key => $value) {
            if (!$this->isMeteorType($value)) {
                return false;
            }
        }
        return true;
    }
}
