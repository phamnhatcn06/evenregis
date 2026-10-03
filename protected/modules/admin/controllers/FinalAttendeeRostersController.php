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
            'pageSize'      => $this->resolvePageSize(),
            'pageSizes'     => self::PAGE_SIZES,
            'filters'       => $this->getFilterValues(),
        ));
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
