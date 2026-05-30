<?php

class ApprovalWorkflowsController extends AdminController
{
    // Bypass permission check tạm thời
    protected $publicActions = array('index', 'view', 'list', 'search', 'admin', 'create', 'update', 'delete', 'addApprover', 'deleteApprover');

    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionAdmin()
    {
        $params = array();

        if (isset($_GET['code']) && $_GET['code'] !== '') {
            $params['code'] = $_GET['code'];
        }
        if (isset($_GET['name']) && $_GET['name'] !== '') {
            $params['name'] = $_GET['name'];
        }
        if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
            $params['is_active'] = $_GET['is_active'];
        }

        $dataProvider = ApprovalWorkflows::getApiDataProvider($params);

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionCreate()
    {
        $model = new ApprovalWorkflows;

        if (isset($_POST['ApprovalWorkflows'])) {
            $model->setAttributes($_POST['ApprovalWorkflows']);

            $ssoUser = AuthHandler::getUser();
            $model->created_by = ($ssoUser && isset($ssoUser['id'])) ? $ssoUser['id'] : null;

            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo workflow thành công');
                    $this->redirect(array('admin'));
                } else {
                    Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['ApprovalWorkflows'])) {
            $model->setAttributes($_POST['ApprovalWorkflows']);

            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật workflow thành công');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        // Lấy danh sách approvers của workflow này
        $approvers = ApprovalWorkflowApprovers::getApiDataProvider(array(
            'workflow_id' => $id,
            'per_page' => 100,
        ), 100);

        $this->render('view', array(
            'model' => $model,
            'approvers' => $approvers,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->request->isPostRequest) {
            $result = ApprovalWorkflows::deleteViaApi($id);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa workflow thành công');
            } else {
                Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
            }

            if (!isset($_GET['ajax'])) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Invalid request');
        }
    }

    // ==================== Approver Management ====================

    public function actionAddApprover($id)
    {
        $workflow = $this->loadModelById($id);
        $model = new ApprovalWorkflowApprovers;
        $model->workflow_id = $id;

        if (isset($_POST['ApprovalWorkflowApprovers'])) {
            $model->setAttributes($_POST['ApprovalWorkflowApprovers']);
            $model->workflow_id = $id;

            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm người duyệt thành công');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
                }
            }
        }

        $this->render('add_approver', array(
            'workflow' => $workflow,
            'model' => $model,
        ));
    }

    public function actionDeleteApprover($id, $approverId)
    {
        if (Yii::app()->request->isPostRequest) {
            $result = ApprovalWorkflowApprovers::deleteViaApi($approverId);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa người duyệt thành công');
            } else {
                Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
            }

            $this->redirect(array('view', 'id' => $id));
        } else {
            throw new CHttpException(400, 'Invalid request');
        }
    }

    protected function loadModelById($id)
    {
        $model = ApprovalWorkflows::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy workflow');
        }
        return $model;
    }
}
