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

        // Xử lý thêm nhiều người
        if (isset($_POST['staff_ids']) && isset($_POST['step_index']) && isset($_POST['step_name'])) {
            $staffIds = $_POST['staff_ids'];
            $stepIndex = $_POST['step_index'];
            $stepName = trim($_POST['step_name']);

            if (empty($staffIds) || empty($stepIndex) || empty($stepName)) {
                Yii::app()->user->setFlash('error', 'Vui lòng chọn đầy đủ thông tin');
            } else {
                $successCount = 0;
                $errorCount = 0;

                foreach ($staffIds as $userId) {
                    // Lấy thông tin user từ Portal SSO
                    $userResult = ApiClient::get(ApiEndpoints::SSO_USERS . '/' . $userId);
                    if ($userResult['success'] && isset($userResult['data'])) {
                        $user = isset($userResult['data']['data']) ? $userResult['data']['data'] : $userResult['data'];

                        $approver = new ApprovalWorkflowApprovers;
                        $approver->workflow_id = $id;
                        $approver->step_index = $stepIndex;
                        $approver->step_name = $stepName;
                        $approver->portal_user_id = $user['id'];
                        $approver->portal_user_name = isset($user['full_name']) ? $user['full_name'] : '';
                        $approver->portal_user_email = isset($user['email']) ? $user['email'] : '';
                        $approver->organization_id = isset($user['property_id']) ? $user['property_id'] : null;
                        $approver->is_active = 1;

                        $result = $approver->storeViaApi();
                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++;
                    }
                }

                if ($successCount > 0) {
                    Yii::app()->user->setFlash('success', "Đã thêm {$successCount} người duyệt thành công");
                }
                if ($errorCount > 0) {
                    Yii::app()->user->setFlash('warning', "{$errorCount} người không thể thêm");
                }
                $this->redirect(array('view', 'id' => $id));
            }
        }

        // Lấy danh sách users từ Portal SSO
        $userList = array();
        $params = Yii::app()->params;
        $portalApiUrl = $params['portal']['api_url'] . '/api/sso/users';

        // Lấy property_code của user hiện tại (nếu có)
        $ssoUser = AuthHandler::getUser();
        $queryParams = array('per_page' => 500);
        if ($ssoUser && isset($ssoUser['property_code']) && $ssoUser['property_code']) {
            $queryParams['property_code'] = $ssoUser['property_code'];
        }

        $url = $portalApiUrl . '?' . http_build_query($queryParams);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: application/json\r\n",
                'timeout' => 30,
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        ));

        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['data'])) {
                $users = isset($data['data']['data']) ? $data['data']['data'] : $data['data'];
                foreach ($users as $user) {
                    $label = isset($user['full_name']) ? $user['full_name'] : (isset($user['email']) ? $user['email'] : 'N/A');
                    if (isset($user['property_name']) && $user['property_name']) {
                        $label .= ' - ' . $user['property_name'];
                    }
                    $userList[$user['id']] = $label;
                }
            }
        }

        $this->render('add_approver', array(
            'workflow' => $workflow,
            'model' => $model,
            'staffList' => $userList,
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
