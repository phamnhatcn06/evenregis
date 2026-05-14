<?php

class RegistrationDetailAttendees extends CModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    public $id;
    public $registration_detail_id;
    public $staff_id;
    public $status;
    public $note;
    public $created_at;
    public $updated_at;

    public function attributeNames()
    {
        return array('id', 'registration_detail_id', 'staff_id', 'status', 'note', 'created_at', 'updated_at');
    }

    public function rules()
    {
        return array(
            array('registration_detail_id, staff_id', 'required'),
            array('status, note', 'safe'),
        );
    }

    public static function storeViaApi($data)
    {
        return ApiClient::post(ApiEndpoints::REGISTRATION_DETAIL_ATTENDEE_STORE, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DETAIL_ATTENDEE_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getByDetailId($detailId)
    {
        $result = ApiClient::get(ApiEndpoints::REGISTRATION_DETAIL_ATTENDEE_LIST, array('registration_detail_id' => $detailId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
