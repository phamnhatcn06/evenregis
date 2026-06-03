<?php

class ApprovalworkflowsController extends AdminController
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
        // Lấy thông tin user từ Portal SSO
        $params = Yii::app()->params;
        $portalApiUrl = $params['portal']['api_url'] . '/api/sso/users';

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
                    $url = $portalApiUrl . '/' . $userId;
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
                        $user = isset($data['data']['data']) ? $data['data']['data'] : (isset($data['data']) ? $data['data'] : null);

                        if ($user) {
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
        // Lấy property_code của user hiện tại (nếu có)
        $ssoUser = AuthHandler::getUser();
        $queryParams = array('per_page' => 500);
        if ($ssoUser && isset($ssoUser['property_code']) && $ssoUser['property_code'] && $ssoUser['property_code'] != '9999') {
            $queryParams['hotelID'] = $ssoUser['property_code'];
        }

        $url = $portalApiUrl . '?' . http_build_query($queryParams);

        // Lấy token từ session
        $token = isset(Yii::app()->session['sso_token']) ? Yii::app()->session['sso_token'] : '';

        // Dùng CURL thay vì file_get_contents
        $portalSecret = isset($params['portal']['jwt_secret']) ? $params['portal']['jwt_secret'] : '';

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'X-Portal-Secret: ' . $portalSecret,
                'Accept: application/json',
            ),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Debug - hiển thị trực tiếp để kiểm tra
        Yii::log("Portal SSO Users API: URL={$url}, HTTP={$httpCode}, Error={$curlError}, Token=" . substr($token, 0, 20) . "..., Response=" . substr($response, 0, 500), 'info', 'application.approvalworkflows');

        // Tạm thời dump để debug
        if (YII_DEBUG) {
            error_log("=== Portal SSO Debug ===");
            error_log("URL: " . $url);
            error_log("HTTP Code: " . $httpCode);
            error_log("Token: " . ($token ? 'Yes (' . strlen($token) . ' chars)' : 'NO TOKEN'));
            error_log("Response: " . substr($response, 0, 1000));
        }

        if ($response) {
            $data = json_decode($response, true);
            // Response là array trực tiếp, không có wrapper
            $users = is_array($data) ? $data : array();
            // Nếu có wrapper data thì lấy từ đó
            if (isset($data['data'])) {
                $users = isset($data['data']['data']) ? $data['data']['data'] : $data['data'];
            }

            foreach ($users as $user) {
                // Portal trả về: AccountId, DisplayName, Email, Employee{Hotel, Position, Department, ...}
                $userId = isset($user['AccountId']) ? $user['AccountId'] : (isset($user['id']) ? $user['id'] : null);
                if (!$userId) continue;

                $name = isset($user['DisplayName']) ? $user['DisplayName'] : '';
                if (!$name) {
                    $name = isset($user['Email']) ? $user['Email'] : 'N/A';
                }

                $parts = array($name);

                // Chức danh - Position có thể là object hoặc string
                $emp = isset($user['Employee']) ? $user['Employee'] : array();
                $position = '';
                if (isset($emp['Position'])) {
                    $position = is_array($emp['Position']) ? (isset($emp['Position']['Name']) ? $emp['Position']['Name'] : '') : $emp['Position'];
                }
                if ($position) $parts[] = $position;

                // Đơn vị - Hotel có thể là object hoặc string
                $hotel = '';
                if (isset($emp['Hotel'])) {
                    $hotel = is_array($emp['Hotel']) ? (isset($emp['Hotel']['Name']) ? $emp['Hotel']['Name'] : '') : $emp['Hotel'];
                }
                if ($hotel) $parts[] = $hotel;

                $userList[$userId] = implode(' - ', $parts);
            }
        }

        // Debug: hiển thị 1 user đầu tiên để xem structure
        if (!empty($userList)) {
            $data = json_decode($response, true);
            $firstUser = is_array($data) && isset($data[0]) ? $data[0] : null;
            if ($firstUser && isset($firstUser['Employee'])) {
                $empKeys = implode(', ', array_keys($firstUser['Employee']));
                Yii::app()->user->setFlash('info', 'Employee fields: ' . $empKeys);
            }
        } elseif (empty($userList)) {
            $responsePreview = $response ? substr($response, 0, 500) : 'EMPTY';
            Yii::app()->user->setFlash('warning', 'Debug: ' . $responsePreview);
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
