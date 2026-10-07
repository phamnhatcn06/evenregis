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

    protected function session()
    {
        return Yii::app()->session;
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
    }

    /** Xóa phiên cổng cá nhân. */
    protected function clearSession()
    {
        $s = $this->session();
        unset(
            $s[self::SESSION_ATTENDEE_ID],
            $s[self::SESSION_FULL_NAME],
            $s[self::SESSION_EVENT_ID]
        );
    }
}
