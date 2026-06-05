<?php

class TalentEntriesController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $members = TalentEntryMembers::getApiDataProvider(array('entry_id' => $id), 100)->getData();

        $this->render('view', array(
            'model' => $model,
            'members' => $members,
        ));
    }

    public function actionCreate()
    {
        $model = new TalentEntries;

        if (isset($_POST['TalentEntries'])) {
            $model->setAttributes($_POST['TalentEntries']);
            if ($model->validate()) {
                $model->status = TalentEntries::STATUS_PENDING;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký tiết mục thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    $model->addError('title', $errorMsg);
                }
            }
        }

        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['TalentEntries'])) {
            $model->setAttributes($_POST['TalentEntries']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật tiết mục thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('title', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = TalentEntries::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa tiết mục thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa.');
            }

            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionAdmin()
    {
        $model = new TalentEntries('search');
        $model->unsetAttributes();

        if (isset($_GET['TalentEntries'])) {
            $model->setAttributes($_GET['TalentEntries']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = TalentEntries::getApiDataProvider($params);
        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionAddMember($entryId)
    {
        $entry = $this->loadModelById($entryId);
        $model = new TalentEntryMembers;

        if (isset($_POST['TalentEntryMembers'])) {
            $model->setAttributes($_POST['TalentEntryMembers']);
            $model->entry_id = $entryId;

            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm thành viên thành công.');
                    $this->redirect(array('view', 'id' => $entryId));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể thêm thành viên.');
                }
            }
        }

        $attendees = Attendees::getApiDataProvider(array(
            'property_id' => $entry->property_id,
            'approval_status' => Attendees::APPROVAL_APPROVED,
        ), 500)->getData();

        $this->render('add_member', array(
            'model' => $model,
            'entry' => $entry,
            'attendees' => $attendees,
        ));
    }

    public function actionRemoveMember($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $member = TalentEntryMembers::fetchFromApi($id);
            $entryId = $member ? $member->entry_id : null;

            $result = TalentEntryMembers::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thành viên thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa thành viên.');
            }

            if ($entryId) {
                $this->redirect(array('view', 'id' => $entryId));
            } else {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    protected function getActiveShows()
    {
        $result = ApiClient::get(ApiEndpoints::TALENT_SHOW_LIST, array(
            'is_active' => 1,
            'per_page' => 100,
        ));

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }

    protected function loadModelById($id)
    {
        $model = TalentEntries::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy tiết mục.');
        }
        return $model;
    }
}
