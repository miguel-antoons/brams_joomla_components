<?php
class ManualCountingCampaignsController extends AppController {
        public $name      = 'ManualCountingCampaigns';

    public $paginate = array('limit' => PAGINATOR_LIMIT, 'order' => 'ManualCountingCampaign.name');
    public $helpers  = array('AppPaginator', 'AppHtml');

    public function index() {
        $this->loadModel('ManualCounting');

        $db = $this->ManualCountingCampaign->getDataSource();

        $subQuery = $db->buildStatement(
            array('fields' => array('ManualCounting.campaign_id'),
                  'table' => $db->fullTableName($this->ManualCounting),
                  'alias' => 'ManualCounting',
                  'limit' => null,
                  'offset' => null,
                  'joins' => array(),
                  'conditions' => array('ManualCounting.user_id' => $this->Auth->user('id')),
                  'order' => null,
                  'group' => null
            ),
            $this->ManualCounting
        );

        $subQueryExpression = $db->expression(' ManualCountingCampaign.id NOT IN ('.$subQuery.') ');

        // Filter campaigns that have been already added.
        $this->paginate['conditions'][] = $subQueryExpression;

        // Filter private campaigns for normal users.
        if ($this->Auth->user('role_id') < Role::SCIENTIST) {
            $this->paginate['conditions'][] = 'ManualCountingCampaign.type_id = \'ZOO\'';
        }

        $this->set('manualCountingCampaigns', $this->paginate());
    }

    public function add_counting() {
        if (!empty($this->request->data)) {
            $id = $this->request->data['ManualCountingCampaign']['id'];
            $user_id = $this->Auth->user('id');
            if ($this->ManualCountingCampaign->newCounting($id, $user_id)) {
                $this->Session->setFlash(__('The counting has been saved'));
                $this->redirect(array('controller' => 'manual_countings', 'action' => 'index'));
            }
        }

        $this->Session->setFlash(__('The counting could not be saved. Please, try again.'));
        $this->redirect(array('action' => 'index'));
    }

        public function admin_index() {
                $this->set('manualCountingCampaigns', $this->paginate());
        }

        public function admin_add() {
        if (!empty($this->request->data)) {
            $this->ManualCountingCampaign->create();
                        if ($this->ManualCountingCampaign->save($this->request->data)) {
                                $this->Session->setFlash(__('The campaign has been saved'));
                                $this->redirect(array('action' => 'index'));
                        } else {
                                $this->Session->setFlash(__('The campaign could not be saved. Please, try again.'));
                        }
                }
        }

        public function admin_edit($id = null) {
                if (!$id && empty($this->request->data)) {
                        $this->Session->setFlash(__('Invalid campaign'));
                        $this->redirect(array('action' => 'index'));
                }
        if (!empty($this->request->data)) {
                        if ($this->ManualCountingCampaign->save($this->request->data)) {
                                $this->Session->setFlash(__('The campaign has been saved'));
                                $this->redirect(array('action' => 'index'));
                        } else {
                                $this->Session->setFlash(__('The campaign could not be saved. Please, try again.'));
                        }
                }
                if (empty($this->request->data)) {
                        $this->request->data = $this->ManualCountingCampaign->read(null, $id);
        }
        }

        public function admin_delete($id = null) {
                if (!$id) {
                        $this->Session->setFlash(__('Invalid id for the campaign'));
                        $this->redirect(array('action'=>'index'));
                }
                if ($this->ManualCountingCampaign->delete($id)) {
                        $this->Session->setFlash(__('Campaign deleted'));
                        $this->redirect(array('action'=>'index'));
                }
                $this->Session->setFlash(__('Campaign was not deleted'));
                $this->redirect(array('action' => 'index'));
    }
}