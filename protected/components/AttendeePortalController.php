<?php

/**
 * Base controller cho cổng cá nhân của người tham dự (không dùng SSO).
 * Người tham dự đăng nhập một lần bằng định danh DHMT + mã PIN tại /portal/login,
 * sau đó dùng chung phiên này cho MỌI tính năng: đăng ký hoạt động, xem lịch thi
 * đấu, lịch thi nghiệp vụ, hình ảnh cá nhân...
 *
 * Các controller tính năng (RunController, ...) kế thừa lớp này để tái sử dụng
 * cơ chế đăng nhập, thay vì tự dựng login riêng.
 */
class AttendeePortalController extends CController
{
    public $layout = '//layouts/portal';

    /** Các khóa session dùng chung cho cổng cá nhân. */
    const SESSION_ATTENDEE_ID = 'portal_attendee_id';
    const SESSION_FULL_NAME   = 'portal_full_name';
    const SESSION_EVENT_ID    = 'portal_event_id';
    /** Đã xác nhận đủ hồ sơ (năm sinh, giới tính) trong phiên đăng nhập này. */
    const SESSION_PROFILE_OK  = 'portal_profile_ok';

    /** Cache hồ sơ trong 1 request để tránh gọi API nhiều lần. */
    private $_profile = null;
    private $_profileLoaded = false;

    protected function session()
    {
        return Yii::app()->session;
    }

    /** Lấy hồ sơ người đang đăng nhập (gọi API tối đa 1 lần mỗi request). */
    protected function loadProfile()
    {
        if (!$this->_profileLoaded) {
            $this->_profile = RunAuth::getProfile($this->currentAttendeeId());
            $this->_profileLoaded = true;
        }
        return $this->_profile;
    }

    public function isLoggedIn()
    {
        return !empty($this->session()[self::SESSION_ATTENDEE_ID]);
    }

    public function currentAttendeeId()
    {
        return $this->session()[self::SESSION_ATTENDEE_ID];
    }

    public function currentFullName()
    {
        return $this->session()[self::SESSION_FULL_NAME];
    }

    public function currentEventId()
    {
        return $this->session()[self::SESSION_EVENT_ID];
    }

    /**
     * Bắt buộc đăng nhập trước khi vào tính năng. Nếu chưa, chuyển về cổng đăng
     * nhập chung kèm URL quay lại.
     */
    protected function requireLogin()
    {
        if ($this->isLoggedIn()) {
            return;
        }
        $returnUrl = Yii::app()->request->requestUri;
        $this->redirect(array('/frontend/portal/login', 'return' => $returnUrl));
    }

    /** Lưu thông tin đăng nhập vào session dùng chung. */
    protected function loginSession(array $data)
    {
        $s = $this->session();
        $s[self::SESSION_ATTENDEE_ID] = isset($data['attendee_id']) ? $data['attendee_id'] : null;
        $s[self::SESSION_FULL_NAME]   = isset($data['full_name']) ? $data['full_name'] : '';
        $s[self::SESSION_EVENT_ID]    = isset($data['event_id']) ? $data['event_id'] : null;
        // Mỗi lần đăng nhập phải xác nhận lại hồ sơ.
        $s[self::SESSION_PROFILE_OK]  = false;
    }

    /** Xóa phiên cổng cá nhân. */
    protected function clearSession()
    {
        $s = $this->session();
        unset(
            $s[self::SESSION_ATTENDEE_ID],
            $s[self::SESSION_FULL_NAME],
            $s[self::SESSION_EVENT_ID],
            $s[self::SESSION_PROFILE_OK]
        );
    }

    /**
     * Người dùng đã xác nhận đủ hồ sơ trong phiên này chưa. Chưa xác nhận thì popup hồ sơ
     * luôn hiện (kể cả reload) và các chức năng đăng ký bị chặn.
     */
    public function isProfileConfirmed()
    {
        if (!empty($this->session()[self::SESSION_PROFILE_OK])) {
            return true;
        }
        // Đã khai đủ năm sinh + giới tính ở lần đăng nhập trước -> coi như đã xác nhận,
        // không bắt xác nhận lại (popup chỉ hiện 1 lần cho tới khi hồ sơ đầy đủ).
        $profile = $this->loadProfile();
        if (is_array($profile) && !empty($profile['is_complete'])) {
            $this->markProfileConfirmed();
            return true;
        }
        return false;
    }

    protected function markProfileConfirmed()
    {
        $s = $this->session();
        $s[self::SESSION_PROFILE_OK] = true;
    }

    /**
     * Dữ liệu cho popup hồ sơ (partial `/portal/_modal_profile`). Chỉ gọi API khi popup
     * cần hiện; đã xác nhận trong phiên thì trả null để view bỏ qua popup.
     */
    protected function profileModalData()
    {
        if ($this->isProfileConfirmed()) {
            return null;
        }
        $profile = RunAuth::getProfile($this->currentAttendeeId());
        return array(
            'saveProfileUrl' => $this->createUrl('/frontend/portal/saveProfile'),
            'logoutUrl'      => $this->createUrl('/frontend/portal/logout'),
            'profile'        => $profile,
            'fullName'       => $this->currentFullName(),
        );
    }

    /** Chặn các action AJAX đăng ký khi chưa xác nhận hồ sơ. Trả true nếu đã chặn (đã echo JSON). */
    protected function denyIfProfileNotConfirmed()
    {
        if ($this->isProfileConfirmed()) {
            return false;
        }
        echo CJSON::encode(array(
            'success' => false,
            'message' => 'Vui lòng xác nhận thông tin cá nhân (năm sinh, giới tính) trước khi đăng ký.',
        ));
        return true;
    }
}
