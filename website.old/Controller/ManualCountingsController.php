<?php
class ManualCountingsController extends AppController {
    public $name = 'ManualCountings';
    public $helpers = array('AppPaginator', 'AppHtml');
    public $paginate = array('limit' => PAGINATOR_LIMIT, 'order' => 'ManualCountingCampaign.name');

    public function beforeFilter() {
        $this->Security->validatePost = false;
        parent::beforeFilter();
        $this->Auth->allow('edit', 'next', 'previous', 'start');
    }

    public function isAuthorized($user) {
        App::uses('Role', 'Model');

        if (isset($user['role_id']) && in_array($this->request->params['action'],
                array('admin_index', 'admin_view', 'admin_download', 'admin_download_spectrograms'))) {
            return $user['role_id'] >= Role::SCIENTIST;
        }

        return parent::isAuthorized($user);
    }

    public function index() {
        $userId = $this->Auth->user('id');
        $this->ManualCounting->recursive = 0;
        $this->set('stateLabels', $this->ManualCounting->stateLabels());
        $this->set('manualCountings', $this->paginate(array('User.id' => $userId)));
    }

    public function edit($id = null, $meteorType = '') {
        if (!$id) {
            $this->redirectToError(__('Invalid counting'));
        }

        $data = $this->ManualCounting->read(null, $id);
        if (!$this->_isAuthorized($data)) {
            $this->redirect(array('controller' => 'pages', 'action' => 'error'));
        }

        $campaign_id = $data['ManualCountingCampaign']['id'];

        $this->request->data = $data;
        $this->set('spectrogram', $this->ManualCounting->spectrogram());
        $this->set('meteors', $this->ManualCounting->findMeteors());
        $this->set('dataFiles', $this->ManualCounting->ManualCountingCampaign->listDatafiles($campaign_id));

        $this->loadModel('ManualCountingMeteor');
        if ($this->ManualCountingMeteor->isMeteorType($meteorType)) {
           $this->set('meteorType', $meteorType);
        } else {
           $this->set('meteorType', '');
        }

        $this->set('displayHelp', $this->Session->read('Counting.displayHelp'));
        $this->Session->write('Counting.displayHelp', false);

        $this->layout = 'nomenu';
    }

    public function select($id = null, $meteorType = '') {
        if (!$id || empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->_isAuthorized($this->ManualCounting->read(null, $id))) {
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->ManualCounting->select($this->request->data)) {
            $this->redirect(array('action' => 'index'));
        }

        $this->_redirectToEdit($id, $meteorType);
    }

    public function previous($id = null, $meteorType = '') {
        if (!$id || empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->_isAuthorized($this->ManualCounting->read(null, $id))) {
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->ManualCounting->previous($this->request->data)) {
            $this->redirect(array('action' => 'index'));
        }

        $this->_redirectToEdit($id, $meteorType);
    }

    public function next($id = null, $meteorType = '') {
        if (!$id || empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->_isAuthorized($this->ManualCounting->read(null, $id))) {
            $this->redirect(array('action' => 'index'));
        }

        if (!$this->ManualCounting->next($this->request->data)) {
            $this->redirect(array('action' => 'index'));
        }

        $this->_redirectToEdit($id, $meteorType);
    }

    public function download($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        $data = $this->ManualCounting->read(null, $id);
        if ($data['ManualCounting']['user_id'] != $this->Auth->user('id')) {
            $this->Session->setFlash(__('You are not authorized to access this page.'));
            $this->redirect(array('action' => 'index'));
        }

        return $this->admin_download($id);
    }

    public function admin_download($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        $data = $this->ManualCounting->read(null, $id);

        $csv = $this->ManualCounting->exportCSV();
        if (!$csv) {
            $this->redirect(array('action' => 'index'));
        }
        $this->response->type('csv');
        $this->response->body($csv);

        // Force file download.
        $this->response->download($data['ManualCountingCampaign']['name'].'.csv');

        // Return response object to prevent controller from trying to render a view.
        return $this->response;
    }

    public function download_spectrograms($id = null, $annotated = false) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        $data = $this->ManualCounting->read(null, $id);
        if ($data['ManualCounting']['user_id'] != $this->Auth->user('id')) {
            $this->Session->setFlash(__('You are not authorized to access this page.'));
            $this->redirect(array('action' => 'index'));
        }

        return $this->admin_download_spectrograms($id, $annotated);
    }

    public function admin_download_spectrograms($id = null, $annotated = false) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid counting'));
            $this->redirect(array('action' => 'index'));
        }

        $data = $this->ManualCounting->read(null, $id);

        $path = TMP.'downloads'.DS.$this->Auth->user('username').'_'.session_id().'_'.uniqid().'.zip';
        if ($annotated) {
            $status = $this->ManualCounting->exportAnnotatedSpectrograms($path);
        } else {
            $status = $this->ManualCounting->exportSpectrograms($path);
        }

        if (!$status) {
            $this->redirect(array('action' => 'index'));
        }

        $this->response->type('zip');
        $this->response->file($path);

        // Force file download.
        $this->response->download($data['ManualCountingCampaign']['name'].'.zip');

        // Return response object to prevent controller from trying to render a view.
        return $this->response;
    }

    public function admin_index() {
        $this->ManualCounting->recursive = 0;
        $this->set('stateLabels', $this->ManualCounting->stateLabels());
        $this->set('manualCountings', $this->paginate());
    }

    public function beforeRender() {
        /* To prevent populate on Files... */
    }

    private function _isAuthorized($data) {
        // Note: Session.read and Auth.user return null if they don't exist.

        if ($data['ManualCounting']['state'] == 'C') {
            $this->Session->setFlash(__('This counting has been already completed.'));
            return false;
        }

        if ($data['ManualCounting']['user_id'] && $data['ManualCounting']['user_id']  === $this->Auth->user('id')) {
            return true;
        }

        if (!$data['ManualCounting']['user_id'] && $data['ManualCounting']['id'] === $this->Session->read('Counting.id')) {
            return true;
        }

        $this->Session->setFlash(__('You are not authorized to access this page.'));
        return false;
    }

    private function _redirectToEdit($id, $meteorType = '') {
        if ($meteorType == '') {
            $this->redirect(array('action' => 'edit', $id));
        } else {
            $this->redirect(array('action' => 'edit', $id, $meteorType));
        }
    }
}