<?php

App::uses('DataFile', 'Model');
App::uses('System', 'Model');
App::uses('Archive', 'Lib');

class DataController extends AppController {
    public $uses = false;
    public $name = 'Data';
    public $helpers = array('AppHtml', 'Html', 'Session');

    public function beforeFilter() {
        /* Avoid validation of POST data since it is not a CakePHP form. */
        parent::beforeFilter();
        $this->Security->validatePost = false;
                $this->Security->unlockedActions = array('index');
                $this->Auth->allow();
    }

    public function index() {
        $this->loadModel('System');
        $systems = $this->System->find('all', array('order' => 'System.name'));
        $this->set(compact('systems'));
    }

    public function make_images() {
        $this->autoRender = false;

        $this->_header();
        header('Content-Type: text/plain');

        $ar = new Archive();
        $params['task'] = 'makeImages';
        echo $ar->get(array_merge($params, $this->request->query));
    }

    public function save_image() {
        $this->autoRender = false;

        $this->_header();
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename='.$this->request->query['image'].'.png');

        $ar = new Archive();
        $params['task'] = 'saveImage';
        echo $ar->get(array_merge($params, $this->request->query));
    }

    public function save_wave() {
        $filename = $this->request->query['image'];
        list($year, $month, $day, $hour, $minute, $location, $antenna) = sscanf($filename, 'RAD_BEDOUR_%4s%2s%2s_%2s%2s_%6s_SYS%d');
        $start = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute;

        $this->loadModel('Location');
        $location = $this->Location->findByLocationCode($location);

        $this->loadModel('System');
        $system = $this->System->findByLocationIdAndAntenna($location['Location']['id'], $antenna);

        $this->loadModel('DataFile');
        $file = $this->DataFile->findByStartAndSystemId($start, $system['System']['id']);

        if ($file['DataFile']['status']) {
            $this->autoRender = false;
            $this->_header();
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$this->request->query['image'].'.wav');

            $ar = new Archive();
            $params['task'] = 'saveWave';
            echo $ar->get(array_merge($params, $this->request->query));
        }
    }

    public function show_image() {
        $this->autoRender = false;

        $this->_header();
        header('Content-Type: image/png');

        $ar = new Archive();
        $params['task'] = 'showImage';
        echo $ar->get(array_merge($params, $this->request->query));
    }

    private function _header() {
        header('MIME-Version: 1.0');
    }
}
