<?php

class ApprovalInboxController extends AdminController
{
    /**
     * Danh sách đơn chờ duyệt của user hiện tại
     */
    public function actionIndex()
    {
        $ssoUser = AuthHandler::getUser();
        if (!$ssoUser || !isset($ssoUser['id'])) {
            throw new CHttpException(403, 'Không có quyền truy cập');
        }

        $portalUserId = $ssoUser['id'];
        $pendingList = RegistrationApprovals::getPendingForApprover($portalUserId);

        $this->render('index', array(
            'pendingList' => $pendingList,
            'ssoUser' => $ssoUser,
        ));
    }

    /**
     * Chi tiết đơn cần duyệt
     */
    public function actionView($id)
    {
        $ssoUser = AuthHandler::getUser();
        if (!$ssoUser || !isset($ssoUser['id'])) {
            throw new CHttpException(403, 'Không có quyền truy cập');
        }

        $model = RegistrationApprovals::fetchFromApi($id);
        if (!$model) {
            throw new CHttpException(404, 'Không tìm thấy');
        }

        // Lấy lịch sử duyệt
        $logs = RegistrationApprovalLogs::getHistory($model->registration_id);

        // Lấy thông tin registration
        $registration = Registrations::fetchFromApi($model->registration_id);

        $this->render('view', array(
            'model' => $model,
            'registration' => $registration,
            'logs' => $logs,
            'ssoUser' => $ssoUser,
        ));
    }

    /**
     * Duyệt đơn
     */
    public function actionApprove($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request');
        }

        $ssoUser = AuthHandler::getUser();
        if (!$ssoUser || !isset($ssoUser['id'])) {
            throw new CHttpException(403, 'Không có quyền truy cập');
        }

        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;

        $result = RegistrationApprovals::approveViaApi(
            $id,
            $ssoUser['id'],
            isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $ssoUser['username'],
            $comment
        );

        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Duyệt thành công');
        } else {
            Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
        }

        $this->redirect(array('index'));
    }

    /**
     * Từ chối đơn
     */
    public function actionReject($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request');
        }

        $ssoUser = AuthHandler::getUser();
        if (!$ssoUser || !isset($ssoUser['id'])) {
            throw new CHttpException(403, 'Không có quyền truy cập');
        }

        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
        if (empty($comment)) {
            Yii::app()->user->setFlash('error', 'Vui lòng nhập lý do từ chối');
            $this->redirect(array('view', 'id' => $id));
            return;
        }

        $result = RegistrationApprovals::rejectViaApi(
            $id,
            $ssoUser['id'],
            isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $ssoUser['username'],
            $comment
        );

        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Đã từ chối đơn đăng ký');
        } else {
            Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
        }

        $this->redirect(array('index'));
    }

    /**
     * Yêu cầu chỉnh sửa - trả về cấp cụ thể
     */
    public function actionRevision($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Invalid request');
        }

        $ssoUser = AuthHandler::getUser();
        if (!$ssoUser || !isset($ssoUser['id'])) {
            throw new CHttpException(403, 'Không có quyền truy cập');
        }

        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
        $returnToIndex = isset($_POST['return_to_index']) ? intval($_POST['return_to_index']) : 0;

        if (empty($comment)) {
            Yii::app()->user->setFlash('error', 'Vui lòng nhập lý do yêu cầu chỉnh sửa');
            $this->redirect(array('view', 'id' => $id));
            return;
        }

        $result = RegistrationApprovals::revisionViaApi(
            $id,
            $ssoUser['id'],
            isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $ssoUser['username'],
            $returnToIndex,
            $comment
        );

        if ($result['success']) {
            $msg = $returnToIndex == 0 ? 'Đã trả về người tạo' : 'Đã trả về bước ' . $returnToIndex;
            Yii::app()->user->setFlash('success', $msg);
        } else {
            Yii::app()->user->setFlash('error', isset($result['message']) ? $result['message'] : 'Có lỗi xảy ra');
        }

        $this->redirect(array('index'));
    }
}
