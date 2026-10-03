<?php

/**
 * Màn Tổng hợp danh sách Vòng Chung Kết (VCK) + cấp mã lucky.
 *
 * Permission key: "finalattendeerosters".
 */
class FinalAttendeeRostersController extends AdminController
{
    /** Số dòng mỗi trang cho phép chọn */
    const PAGE_SIZES = array(25, 50, 100);

    /** Số dòng mỗi lần gọi API khi xuất Excel */
    const EXPORT_CHUNK_SIZE = 500;

    /** Trần số dòng xuất ra, tránh một bộ lọc quá rộng làm hết bộ nhớ */
    const EXPORT_MAX_ROWS = 10000;

    public function actionAdmin()
    {
        if (!PermissionHelper::can('finalattendeerosters', 'read')) {
            throw new CHttpException(403, 'Bạn không có quyền xem danh sách này.');
        }

        $eventId  = $this->getIntParam('event_id');
        $periodId = $this->getIntParam('period_id');

        $eventList  = $this->getEventList();
        $periodList = $eventId ? $this->getFinalPeriodList($eventId) : array();

        // Chưa chọn đợt mà sự kiện chỉ có một đợt VCK thì chọn sẵn, đỡ một bước cho HO.
        if ($eventId && !$periodId && count($periodList) === 1) {
            $periodId = (int) key($periodList);
        }

        $dataProvider  = null;
        $stats         = null;
        $audit         = null;
        $filterOptions = array('properties' => array(), 'divisions' => array(), 'departments' => array());
        $lastSyncedAt  = null;

        if ($eventId && $periodId) {
            $params = $this->buildFilterParams($eventId, $periodId);

            $pageSize     = $this->resolvePageSize();
            $dataProvider = FinalAttendeeRosters::getApiDataProvider($params, $pageSize);
            $stats        = FinalAttendeeRosters::getStats($eventId, $periodId);

            $filterOptions = FinalAttendeeRosters::getFilterOptions(
                $eventId,
                $periodId,
                isset($params['property_id']) ? $params['property_id'] : null,
                isset($params['division_code']) ? $params['division_code'] : null
            );

            $lastSyncedAt = $this->resolveLastSyncedAt($dataProvider);
            $audit        = FinalAttendeeRosters::getAudit($eventId);
        }

        $this->render('admin', array(
            'eventId'       => $eventId,
            'periodId'      => $periodId,
            'eventList'     => $eventList,
            'periodList'    => $periodList,
            'dataProvider'  => $dataProvider,
            'stats'         => $stats,
            'filterOptions' => $filterOptions,
            'lastSyncedAt'  => $lastSyncedAt,
            'audit'         => $audit,
            'roleList'      => $this->getRoleList(),
            'pageSize'      => $this->resolvePageSize(),
            'pageSizes'     => self::PAGE_SIZES,
            'filters'       => $this->getFilterValues(),
        ));
    }

    /**
     * Xuất Excel toàn bộ dòng theo bộ lọc đang áp dụng.
     */
    public function actionExport()
    {
        if (!PermissionHelper::can('finalattendeerosters', 'read')) {
            throw new CHttpException(403, 'Bạn không có quyền xuất danh sách này.');
        }

        $eventId  = $this->getIntParam('event_id');
        $periodId = $this->getIntParam('period_id');

        if (!$eventId || !$periodId) {
            throw new CHttpException(422, 'Vui lòng chọn sự kiện và đợt Vòng Chung Kết.');
        }

        $rows  = $this->fetchAllForExport($this->buildFilterParams($eventId, $periodId));
        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Tong hop VCK');

        $headers = array(
            'Mã lucky', 'Định danh đăng nhập', 'Họ và tên', 'Mã nhân viên', 'Số CCCD', 'Ngày sinh',
            'Số điện thoại', 'Đơn vị', 'Nhãn in thẻ', 'Bộ phận', 'Phòng ban',
            'Chức danh hiển thị', 'Chức danh gốc (SMILE)', 'Size áo', 'Số thẻ',
            'Loại', 'Trạng thái', 'Đã đặt PIN', 'Đã sửa tay', 'Xung đột',
        );

        $lastColumn = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $sheet->setCellValue('A1', 'TỔNG HỢP DANH SÁCH VÒNG CHUNG KẾT');
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue('A2', 'Xuất lúc: ' . date('d/m/Y H:i') . ' — Tổng: ' . count($rows) . ' người');
        $sheet->mergeCells('A2:' . $lastColumn . '2');

        foreach ($headers as $index => $label) {
            $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($index) . '4', $label);
        }
        $sheet->getStyle('A4:' . $lastColumn . '4')->getFont()->setBold(true);

        $fieldLabels = FinalAttendeeRosters::editableFields();
        $rowIndex    = 5;

        foreach ($rows as $item) {
            $overridden = isset($item['overridden_fields']) && is_array($item['overridden_fields'])
                ? $item['overridden_fields']
                : array();

            $overriddenLabels = array();
            foreach ($overridden as $field) {
                $overriddenLabels[] = isset($fieldLabels[$field]) ? $fieldLabels[$field] : $field;
            }

            $values = array(
                $this->excelText($item, 'lucky_number'),
                $this->excelText($item, 'login_identifier'),
                $this->excelText($item, 'full_name'),
                $this->excelText($item, 'staff_code'),
                $this->excelText($item, 'id_card'),
                $this->excelText($item, 'birthday'),
                $this->excelText($item, 'phone_number'),
                $this->excelText($item, 'property_name'),
                $this->excelText($item, 'unit_label'),
                $this->excelText($item, 'division_name'),
                $this->excelText($item, 'department_name'),
                $this->excelText($item, 'position_display'),
                $this->excelText($item, 'position_name'),
                $this->excelText($item, 'shirt_size'),
                $this->excelText($item, 'badge_number'),
                $this->excelTypeLabel($item),
                $this->excelStatusLabel($item),
                !empty($item['pin_is_set']) ? 'Đã đặt' : 'Chưa đặt',
                implode(', ', $overriddenLabels),
                !empty($item['conflict_flag'])
                    ? FinalAttendeeRosters::getConflictLabel($item['conflict_flag'])
                    : '',
            );

            foreach ($values as $index => $value) {
                // Ghi dạng chuỗi tường minh: mã lucky, mã NV, CCCD, SĐT nếu để Excel tự nhận kiểu
                // thì số 0 đứng đầu bị mất và mã dài bị đổi sang dạng khoa học.
                $sheet->setCellValueExplicit(
                    PHPExcel_Cell::stringFromColumnIndex($index) . $rowIndex,
                    $value,
                    PHPExcel_Cell_DataType::TYPE_STRING
                );
            }

            $rowIndex++;
        }

        foreach (range(0, count($headers) - 1) as $index) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($index))->setAutoSize(true);
        }

        $filename = 'TongHop_VCK_Lucky_' . $eventId . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    /**
     * Lấy toàn bộ dòng theo bộ lọc, chia trang để không nạp hết vào bộ nhớ một lúc.
     */
    protected function fetchAllForExport($params)
    {
        $all  = array();
        $page = 1;

        do {
            $chunk = FinalAttendeeRosters::fetchPage($params, $page, self::EXPORT_CHUNK_SIZE);

            foreach ($chunk as $item) {
                $all[] = $item;
            }

            $page++;

            // Chặn trần để một bộ lọc quá rộng không kéo vô hạn và làm hết bộ nhớ.
            if (count($all) >= self::EXPORT_MAX_ROWS) {
                break;
            }
        } while (count($chunk) === self::EXPORT_CHUNK_SIZE);

        return $all;
    }

    protected function excelText($item, $field)
    {
        return isset($item[$field]) && $item[$field] !== null ? (string) $item[$field] : '';
    }

    protected function excelTypeLabel($item)
    {
        $options = FinalAttendeeRosters::getTypeOptions();
        $type    = isset($item['attendee_type']) ? $item['attendee_type'] : null;

        return isset($options[$type]) ? $options[$type] : (string) $type;
    }

    protected function excelStatusLabel($item)
    {
        if (!empty($item['is_withdrawn'])) {
            return 'Đã huỷ tư cách';
        }

        return (int) $item['status'] === FinalAttendeeRosters::STATUS_MANUAL
            ? 'HO thêm tay'
            : 'Đang tham dự';
    }

    /**
     * Khởi tạo PHPExcel. Phải tạm bỏ autoload của Yii vì PHPExcel dùng autoload riêng.
     */
    protected function createPhpExcel()
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $excel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        return $excel;
    }

    /**
     * Trả giá trị dropdown Bộ phận / Phòng ban theo phạm vi đang chọn (JSON, cho dropdown phụ thuộc).
     *
     * Đi qua controller thay vì để JS gọi thẳng External API, để API key không bị nhúng vào HTML.
     */
    public function actionFilterOptions()
    {
        if (!PermissionHelper::can('finalattendeerosters', 'read')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền xem danh sách này.'), 403);
            return;
        }

        $eventId = $this->getIntParam('event_id');
        if (!$eventId) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu sự kiện.'), 422);
            return;
        }

        $options = FinalAttendeeRosters::getFilterOptions(
            $eventId,
            $this->getIntParam('period_id'),
            $this->getIntParam('property_id'),
            isset($_GET['division_code']) && $_GET['division_code'] !== '' ? $_GET['division_code'] : null
        );

        $this->renderJson(array(
            'success'     => true,
            'divisions'   => $options['divisions'],
            'departments' => $options['departments'],
        ));
    }

    /**
     * Xem trước kết quả đồng bộ (dry-run) — KHÔNG ghi dữ liệu.
     */
    public function actionSyncPreview()
    {
        $this->runSync(FinalAttendeeRosters::SYNC_MODE_PREVIEW);
    }

    /**
     * Ghi thật kết quả đồng bộ.
     */
    public function actionSync()
    {
        $this->runSync(FinalAttendeeRosters::SYNC_MODE_APPLY);
    }

    /**
     * Thân chung của xem trước và ghi thật, chỉ khác tham số mode.
     */
    protected function runSync($mode)
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }

        // Cả xem trước và ghi thật đều cần quyền tạo: xem trước tiết lộ toàn bộ danh sách VCK.
        if (!PermissionHelper::can('finalattendeerosters', 'create')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền đồng bộ danh sách.'), 403);
            return;
        }

        $request  = Yii::app()->request;
        $eventId  = (int) $request->getPost('event_id');
        $periodId = (int) $request->getPost('period_id');

        if (!$eventId || !$periodId) {
            $this->renderJson(array(
                'success' => false,
                'message' => 'Vui lòng chọn sự kiện và đợt Vòng Chung Kết.',
            ), 422);
            return;
        }

        $propertyId = $request->getPost('property_id');
        $propertyId = $propertyId !== null && $propertyId !== '' ? (int) $propertyId : null;

        $result = FinalAttendeeRosters::syncViaApi($eventId, $periodId, $propertyId, $mode);

        if (!$result['success']) {
            // BE trả 409 khi đang có tiến trình đồng bộ khác, 422 khi tham số sai.
            $status = isset($result['code']) && (int) $result['code'] >= 400 ? (int) $result['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $result['error'] ?: 'Không thể đồng bộ danh sách.',
            ), $status);
            return;
        }

        $report  = isset($result['data']['data']) ? $result['data']['data'] : array();
        $message = isset($result['data']['message']) ? $result['data']['message'] : 'Đã đồng bộ.';

        $this->renderJson(array(
            'success' => true,
            'mode'    => $mode,
            'message' => $message,
            'report'  => $report,
        ));
    }

    /**
     * Cấp mã lucky cho những người chưa có mã (JSON).
     */
    public function actionGenLucky()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }

        if (!PermissionHelper::can('finalattendeerosters', 'create')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền cấp mã lucky.'), 403);
            return;
        }

        $request = Yii::app()->request;
        $eventId = (int) $request->getPost('event_id');

        if (!$eventId) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn sự kiện.'), 422);
            return;
        }

        $propertyId = $request->getPost('property_id');
        $propertyId = $propertyId !== null && $propertyId !== '' ? (int) $propertyId : null;

        $result = FinalAttendeeRosters::provisionLuckyViaApi($eventId, $propertyId);

        if (!$result['success']) {
            $status = isset($result['code']) && (int) $result['code'] >= 400 ? (int) $result['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $result['error'] ?: 'Không thể cấp mã lucky.',
            ), $status);
            return;
        }

        $this->renderJson(array(
            'success' => true,
            'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Đã cấp mã lucky.',
            'report'  => isset($result['data']['data']) ? $result['data']['data'] : array(),
        ));
    }

    /**
     * HO thêm người thủ công vào danh sách (JSON).
     */
    public function actionCreate()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }

        if (!PermissionHelper::can('finalattendeerosters', 'create')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền thêm người.'), 403);
            return;
        }

        $request = Yii::app()->request;
        $data    = array(
            'event_id'    => (int) $request->getPost('event_id'),
            'period_id'   => (int) $request->getPost('period_id'),
            'property_id' => (int) $request->getPost('property_id'),
            'full_name'   => trim((string) $request->getPost('full_name')),
        );

        if (!$data['event_id'] || !$data['period_id'] || !$data['property_id'] || $data['full_name'] === '') {
            $this->renderJson(array(
                'success' => false,
                'message' => 'Vui lòng nhập đủ sự kiện, đợt Vòng Chung Kết, đơn vị và họ tên.',
            ), 422);
            return;
        }

        $roleId = $request->getPost('role_id');
        if ($roleId !== null && $roleId !== '') {
            $data['role_id'] = (int) $roleId;
        }

        foreach (array_keys(FinalAttendeeRosters::editableFields()) as $field) {
            if (isset($data[$field])) {
                continue;
            }
            $value = $request->getPost($field);
            if ($value !== null && trim((string) $value) !== '') {
                $data[$field] = trim((string) $value);
            }
        }

        $result = FinalAttendeeRosters::storeViaApi($data);

        if (!$result['success']) {
            // 409 = người này đã có trong danh sách hoặc hết dải số thẻ.
            $status = isset($result['code']) && (int) $result['code'] >= 400 ? (int) $result['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $result['error'] ?: 'Không thể thêm người.',
            ), $status);
            return;
        }

        $row = isset($result['data']['data']) ? $result['data']['data'] : array();

        $this->renderJson(array(
            'success' => true,
            'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Đã thêm người.',
            'row'     => $row,
        ));
    }

    /**
     * Gộp hai dòng của cùng một người (JSON).
     */
    public function actionMerge()
    {
        if (!$this->guardWrite('update')) {
            return;
        }

        $request = Yii::app()->request;
        $keepId  = (int) $request->getPost('keep_id');
        $mergeId = (int) $request->getPost('merge_id');

        if (!$keepId || !$mergeId) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn cả hai dòng cần gộp.'), 422);
            return;
        }

        $this->respondApi(FinalAttendeeRosters::mergeViaApi($keepId, $mergeId), 'Không thể gộp dòng.');
    }

    /**
     * Tách một số bản ghi khỏi dòng hiện tại (JSON).
     */
    public function actionSplit()
    {
        if (!$this->guardWrite('update')) {
            return;
        }

        $request     = Yii::app()->request;
        $id          = (int) $request->getPost('id');
        $attendeeIds = $request->getPost('attendee_ids_to_split');
        $attendeeIds = is_array($attendeeIds) ? array_map('intval', $attendeeIds) : array();

        if (!$id || empty($attendeeIds)) {
            $this->renderJson(array(
                'success' => false,
                'message' => 'Vui lòng chọn người cần tách ra khỏi dòng này.',
            ), 422);
            return;
        }

        $hint = array();
        foreach (array('staff_code', 'id_card', 'full_name', 'birthday') as $field) {
            $value = $request->getPost($field);
            if ($value !== null && trim((string) $value) !== '') {
                $hint[$field] = trim((string) $value);
            }
        }

        $this->respondApi(
            FinalAttendeeRosters::splitViaApi($id, $attendeeIds, $hint),
            'Không thể tách người.'
        );
    }

    /**
     * Huỷ tư cách người tham dự (JSON). Mã lucky được giữ lại.
     */
    public function actionDelete()
    {
        if (!$this->guardWrite('delete')) {
            return;
        }

        $id = (int) Yii::app()->request->getPost('id');
        if (!$id) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu mã dòng cần huỷ.'), 422);
            return;
        }

        $alsoDeactivate = Yii::app()->request->getPost('also_deactivate_attendee');
        $alsoDeactivate = $alsoDeactivate === null || (string) $alsoDeactivate !== '0';

        $this->respondApi(
            FinalAttendeeRosters::deleteViaApi($id, $alsoDeactivate),
            'Không thể huỷ tư cách.'
        );
    }

    /**
     * Đánh dấu xung đột đã xử lý (JSON).
     */
    public function actionClearConflict()
    {
        if (!$this->guardWrite('update')) {
            return;
        }

        $id = (int) Yii::app()->request->getPost('id');
        if (!$id) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu mã dòng.'), 422);
            return;
        }

        $this->respondApi(
            FinalAttendeeRosters::clearConflictViaApi($id),
            'Không xoá được cờ xung đột.'
        );
    }

    /**
     * Kiểm tra POST + quyền cho các action ghi. Trả false nếu đã xuất lỗi.
     */
    protected function guardWrite($operation)
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return false;
        }

        if (!PermissionHelper::can('finalattendeerosters', $operation)) {
            $this->renderJson(array(
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.',
            ), 403);
            return false;
        }

        return true;
    }

    /**
     * Chuyển kết quả ApiClient thành JSON cho JS, giữ nguyên HTTP status thật của API.
     */
    protected function respondApi($result, $fallbackMessage)
    {
        if (!$result['success']) {
            $status = isset($result['code']) && (int) $result['code'] >= 400 ? (int) $result['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $result['error'] ?: $fallbackMessage,
            ), $status);
            return;
        }

        $this->renderJson(array(
            'success' => true,
            'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Đã cập nhật.',
            'data'    => isset($result['data']['data']) ? $result['data']['data'] : null,
        ));
    }

    /**
     * Đối soát dải số thẻ và mã lucky (JSON, chỉ đọc).
     */
    public function actionAudit()
    {
        if (!PermissionHelper::can('finalattendeerosters', 'read')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền xem đối soát.'), 403);
            return;
        }

        $eventId = $this->getIntParam('event_id');
        if (!$eventId) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn sự kiện.'), 422);
            return;
        }

        $scope = isset($_GET['scope']) && in_array($_GET['scope'], array('lucky', 'badge', 'all'), true)
            ? $_GET['scope']
            : 'all';

        $data = FinalAttendeeRosters::getAudit($eventId, $scope);

        if ($data === null) {
            $this->renderJson(array('success' => false, 'message' => 'Không lấy được dữ liệu đối soát.'), 502);
            return;
        }

        $this->renderJson(array('success' => true, 'audit' => $data));
    }

    /**
     * Sửa thủ công các trường của một dòng (JSON, cho inline edit và modal sửa).
     */
    public function actionUpdateField()
    {
        $this->runFieldAction('update');
    }

    /**
     * Khôi phục các trường về giá trị gốc (JSON).
     */
    public function actionResetField()
    {
        $this->runFieldAction('reset');
    }

    /**
     * Thân chung của sửa tay và khôi phục gốc.
     */
    protected function runFieldAction($operation)
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }

        if (!PermissionHelper::can('finalattendeerosters', 'update')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền sửa danh sách.'), 403);
            return;
        }

        $request = Yii::app()->request;
        $id      = (int) $request->getPost('id');

        if (!$id) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu mã dòng cần sửa.'), 422);
            return;
        }

        if ($operation === 'reset') {
            $fields = $request->getPost('fields');
            $fields = is_array($fields) ? $fields : array_filter(array((string) $fields));

            if (empty($fields)) {
                $this->renderJson(array('success' => false, 'message' => 'Chưa chọn trường cần khôi phục.'), 422);
                return;
            }

            $result = FinalAttendeeRosters::resetFieldsViaApi($id, $fields);
        } else {
            $fields = $this->collectEditableFields($request->getPost('fields'));

            if (empty($fields)) {
                $this->renderJson(array('success' => false, 'message' => 'Không có trường nào được phép sửa.'), 422);
                return;
            }

            $result = FinalAttendeeRosters::updateFieldsViaApi($id, $fields);
        }

        if (!$result['success']) {
            // 409 = sửa xong bị trùng khoá với người khác, 404 = không tìm thấy dòng.
            $status = isset($result['code']) && (int) $result['code'] >= 400 ? (int) $result['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $result['error'] ?: 'Không thể cập nhật.',
            ), $status);
            return;
        }

        $data = isset($result['data']['data']) ? $result['data']['data'] : array();

        $this->renderJson(array(
            'success' => true,
            'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Đã cập nhật.',
            'row'     => isset($data['row']) ? $data['row'] : null,
        ));
    }

    /**
     * Lọc chỉ giữ trường được phép sửa.
     *
     * Chặn ngay ở FE để request lạ không đi tới API; BE vẫn lọc lần nữa nên đây là lớp đầu,
     * không phải lớp duy nhất. `lucky_number` không nằm trong danh sách nên không có đường sửa.
     */
    protected function collectEditableFields($input)
    {
        if (!is_array($input)) {
            return array();
        }

        $allowed = array_keys(FinalAttendeeRosters::editableFields());
        $fields  = array();

        foreach ($input as $name => $value) {
            if (in_array($name, $allowed, true)) {
                $fields[$name] = is_string($value) ? trim($value) : $value;
            }
        }

        return $fields;
    }

    /**
     * Xuất JSON và kết thúc request, kèm HTTP status thật để JS phân biệt được lỗi.
     */
    protected function renderJson($payload, $status = 200)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status);
        echo CJSON::encode($payload);
        Yii::app()->end();
    }

    /**
     * Gom tham số lọc gửi sang API.
     */
    protected function buildFilterParams($eventId, $periodId)
    {
        $params = array(
            'event_id'  => $eventId,
            'period_id' => $periodId,
        );

        foreach ($this->getFilterValues() as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Giá trị bộ lọc đang áp dụng, đọc từ query string.
     */
    protected function getFilterValues()
    {
        $keys = array(
            'property_id', 'division_code', 'department_code', 'attendee_type',
            'has_lucky', 'has_override', 'conflict_flag', 'keyword', 'with_trashed',
        );

        $values = array();
        foreach ($keys as $key) {
            $values[$key] = isset($_GET[$key]) && $_GET[$key] !== '' ? $_GET[$key] : null;
        }

        return $values;
    }

    protected function resolvePageSize()
    {
        $size = $this->getIntParam('per_page');

        return in_array($size, self::PAGE_SIZES, true) ? $size : self::PAGE_SIZES[0];
    }

    /**
     * Thời điểm đồng bộ gần nhất, lấy từ dòng đầu tiên của trang hiện tại.
     */
    protected function resolveLastSyncedAt($dataProvider)
    {
        try {
            $rows = $dataProvider->getData();
        } catch (Exception $e) {
            return null;
        }

        $latest = null;
        foreach ($rows as $row) {
            $value = (int) $row->last_synced_at;
            if ($value > $latest) {
                $latest = $value;
            }
        }

        return $latest;
    }

    protected function getIntParam($name)
    {
        return isset($_GET[$name]) && $_GET[$name] !== '' ? (int) $_GET[$name] : null;
    }

    /**
     * Danh mục vai trò người tham dự, cho dropdown ở modal thêm người.
     */
    protected function getRoleList()
    {
        $list = array();
        try {
            foreach (Roles::getApiDataProvider(array(), 200)->getData() as $role) {
                $list[$role->id] = $role->name;
            }
        } catch (Exception $e) {
            Yii::log('Không tải được danh mục vai trò: ' . $e->getMessage(), CLogger::LEVEL_WARNING);
        }
        return $list;
    }

    protected function getEventList()
    {
        $list = array();
        try {
            foreach (Events::getApiDataProvider(array(), 200)->getData() as $event) {
                $list[$event->id] = $event->name;
            }
        } catch (Exception $e) {
            Yii::log('Không tải được danh sách sự kiện: ' . $e->getMessage(), CLogger::LEVEL_WARNING);
        }
        return $list;
    }

    /**
     * Chỉ các đợt Vòng Chung Kết (is_final = 1) của sự kiện — màn này không làm việc với đợt thường.
     */
    protected function getFinalPeriodList($eventId)
    {
        $list = array();
        try {
            $periods = RegistrationPeriods::getApiDataProvider(
                array('event_id' => $eventId, 'is_final' => 1),
                200
            )->getData();

            foreach ($periods as $period) {
                if ((int) $period->is_final === 1) {
                    $list[$period->id] = $period->name;
                }
            }
        } catch (Exception $e) {
            Yii::log('Không tải được danh sách đợt VCK: ' . $e->getMessage(), CLogger::LEVEL_WARNING);
        }
        return $list;
    }
}
