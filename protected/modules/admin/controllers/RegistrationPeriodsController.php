<?php

class RegistrationPeriodsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$periodContents = RegistrationPeriodContents::getContentsByPeriod($id);
		$this->render('view', array(
			'model' => $model,
			'periodContents' => $periodContents,
		));
	}

	public function actionCreate()
	{
		$model = new RegistrationPeriods;
		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
		$contents = Contents::getApiDataProvider(array('status' => 1), 100)->getData();

		if (isset($_POST['RegistrationPeriods'])) {
			$model->setAttributes($_POST['RegistrationPeriods']);
			$model->is_active = true;
			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;

					if ($newId && isset($_POST['content_ids'])) {
						RegistrationPeriodContents::syncContentsForPeriod($newId, $_POST['content_ids']);
					}

					Yii::app()->user->setFlash('success', 'Tạo đợt đăng ký thành công.');
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Không thể tạo đợt đăng ký.';
					$model->addError('name', $errorMsg);
				}
			}
		}

		$this->render('create', array(
			'model' => $model,
			'events' => $events,
			'contents' => $contents,
			'selectedContentIds' => array(),
		));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);
		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
		$contents = Contents::getApiDataProvider(array('status' => 1), 100)->getData();
		$selectedContentIds = RegistrationPeriodContents::getContentIdsByPeriod($id);

		if (isset($_POST['RegistrationPeriods'])) {
			$model->setAttributes($_POST['RegistrationPeriods']);
			$model->is_active = isset($_POST['RegistrationPeriods']['is_active']) ? 1 : 0;

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					$contentIds = isset($_POST['content_ids']) ? $_POST['content_ids'] : array();
					RegistrationPeriodContents::syncContentsForPeriod($id, $contentIds);

					Yii::app()->user->setFlash('success', 'Cập nhật đợt đăng ký thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', isset($result['error']) ? $result['error'] : 'Không thể cập nhật.');
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
			'events' => $events,
			'contents' => $contents,
			'selectedContentIds' => $selectedContentIds,
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = RegistrationPeriods::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa đợt đăng ký thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
				$this->redirect(array('admin'));
			}
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	protected function loadModelById($id)
	{
		$model = RegistrationPeriods::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy đợt đăng ký.');
		}
		return $model;
	}

	/**
	 * Màn hình danh sách người tham dự Vòng chung kết (VCK) của đợt.
	 */
	public function actionFinalList($id)
	{
		$model = $this->loadModelById($id);
		if (!$model->isFinal()) {
			throw new CHttpException(400, 'Đợt đăng ký không phải loại Vòng chung kết (VCK).');
		}

		$attendees = RegistrationPeriods::getFinalAttendees($id);

		// Map tên đơn vị
		$propertyNames = array();
		$properties = Properties::getApiDataProvider(array(), 1000)->getData();
		foreach ($properties as $p) {
			$pid = is_array($p) ? (isset($p['id']) ? $p['id'] : null) : (isset($p->id) ? $p->id : null);
			$pname = is_array($p) ? (isset($p['name']) ? $p['name'] : '') : (isset($p->name) ? $p->name : '');
			if ($pid) {
				$propertyNames[$pid] = $pname;
			}
		}

		$this->render('finalList', array(
			'model' => $model,
			'attendees' => $attendees,
			'propertyNames' => $propertyNames,
		));
	}

	/**
	 * AJAX: cập nhật attendee VCK (chỉ ảnh + chức danh).
	 */
	public function actionFinalUpdateAttendee($id)
	{
		$this->requirePostJson();
		$data = array(
			'position' => isset($_POST['position']) ? $_POST['position'] : null,
			'photo_path' => isset($_POST['photo_path']) ? $_POST['photo_path'] : null,
		);
		$result = RegistrationPeriods::updateFinalAttendee($id, $data);
		$this->echoApiResult($result, 'Cập nhật thành công.');
	}

	/**
	 * AJAX: danh sách giám đốc đủ điều kiện của đơn vị.
	 */
	public function actionFinalDirectorCandidates()
	{
		header('Content-Type: application/json');
		$propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
		$rows = RegistrationPeriods::getDirectorCandidates($propertyId);
		echo CJSON::encode(array('success' => true, 'data' => $rows));
		Yii::app()->end();
	}

	/**
	 * AJAX: thêm giám đốc / lái xe vào đợt VCK.
	 */
	public function actionFinalAddSupport($id)
	{
		$this->requirePostJson();
		$model = $this->loadModelById($id);
		$data = array(
			'event_id' => (int) $model->event_id,
			'period_id' => (int) $model->id,
			'property_id' => isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0,
			'type' => isset($_POST['type']) ? $_POST['type'] : '',
			'staff_id' => isset($_POST['staff_id']) && $_POST['staff_id'] !== '' ? (int) $_POST['staff_id'] : null,
			'full_name' => isset($_POST['full_name']) ? $_POST['full_name'] : null,
			'position' => isset($_POST['position']) ? $_POST['position'] : null,
			'photo_path' => isset($_POST['photo_path']) ? $_POST['photo_path'] : null,
		);
		$result = RegistrationPeriods::addSupportAttendee($data);
		$this->echoApiResult($result, 'Thêm thành công.');
	}

	/**
	 * AJAX: upload ảnh ở phía frontend, trả về đường dẫn (không upload lên API).
	 */
	public function actionFinalUploadPhoto()
	{
		$this->requirePostJson();

		if (!isset($_FILES['photo']) || empty($_FILES['photo']['name'])) {
			echo CJSON::encode(array('success' => false, 'message' => 'Vui lòng chọn ảnh.'));
			Yii::app()->end();
		}

		$file = $_FILES['photo'];
		if ($file['error'] !== UPLOAD_ERR_OK) {
			echo CJSON::encode(array('success' => false, 'message' => 'Lỗi upload (code ' . $file['error'] . ').'));
			Yii::app()->end();
		}

		$allowedTypes = array('jpg', 'jpeg', 'png');
		$maxSize = 5 * 1024 * 1024;
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowedTypes)) {
			echo CJSON::encode(array('success' => false, 'message' => 'Chỉ chấp nhận ảnh JPG, PNG.'));
			Yii::app()->end();
		}
		if ($file['size'] > $maxSize) {
			echo CJSON::encode(array('success' => false, 'message' => 'Ảnh vượt quá 5MB.'));
			Yii::app()->end();
		}

		$relDir = 'uploads/attendees/final';
		$uploadPath = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relDir);
		if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0755, true)) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không thể tạo thư mục upload.'));
			Yii::app()->end();
		}

		$newFilename = time() . '_' . substr(md5(uniqid('', true)), 0, 8) . '.' . $ext;
		$targetPath = $uploadPath . DIRECTORY_SEPARATOR . $newFilename;

		if (move_uploaded_file($file['tmp_name'], $targetPath)) {
			$path = '/' . $relDir . '/' . $newFilename;
			echo CJSON::encode(array('success' => true, 'path' => $path, 'message' => 'Tải ảnh thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'message' => 'Không thể lưu ảnh.'));
		}
		Yii::app()->end();
	}

	private function requirePostJson()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
		header('Content-Type: application/json');
	}

	private function echoApiResult($result, $successMessage)
	{
		// ApiClient trả $result['data'] = toàn bộ body API {code, message, data}
		$body = isset($result['data']) && is_array($result['data']) ? $result['data'] : array();
		if (!empty($result['success'])) {
			$message = isset($body['message']) ? $body['message'] : $successMessage;
			$payload = isset($body['data']) ? $body['data'] : null;
			echo CJSON::encode(array('success' => true, 'message' => $message, 'data' => $payload));
		} else {
			$message = isset($result['error']) ? $result['error'] : (isset($body['message']) ? $body['message'] : 'Có lỗi xảy ra.');
			echo CJSON::encode(array('success' => false, 'message' => $message));
		}
		Yii::app()->end();
	}

	/**
	 * Tổng hợp finalist 4 module vào đợt VCK (AJAX, trả JSON).
	 */
	public function actionBuildFinal($id)
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		header('Content-Type: application/json');

		$model = $this->loadModelById($id);
		if (!$model->isFinal()) {
			echo CJSON::encode(array('success' => false, 'message' => 'Đợt đăng ký không phải loại Vòng chung kết (VCK).'));
			Yii::app()->end();
		}

		$result = $model->buildFinalViaApi();
		if (!empty($result['success'])) {
			$data = isset($result['data']) ? $result['data'] : array();
			$message = isset($result['message']) ? $result['message'] : 'Tổng hợp finalist thành công.';
			echo CJSON::encode(array('success' => true, 'message' => $message, 'data' => $data));
		} else {
			$message = isset($result['error']) ? $result['error'] : 'Không thể tổng hợp finalist.';
			echo CJSON::encode(array('success' => false, 'message' => $message));
		}
		Yii::app()->end();
	}

	public function actionAdmin()
	{
		$model = new RegistrationPeriods('search');
		$model->unsetAttributes();

		if (isset($_GET['RegistrationPeriods'])) {
			$model->setAttributes($_GET['RegistrationPeriods']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = RegistrationPeriods::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}
}
