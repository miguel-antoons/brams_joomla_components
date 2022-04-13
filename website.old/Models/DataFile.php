<?php
class DataFile extends AppModel {
    public $recursive = -1;
    public $useTable = 'file';
    public $displayField = 'start';

    public $belongsTo = array(
                'System' => array(
                        'className' => 'System',
                        'foreignKey' => 'system_id',
                )
    );
}
