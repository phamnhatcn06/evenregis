<?php

Yii::import('application.models._base.BaseBeautyContestants');

class BeautyContestants extends BaseBeautyContestants
{
    const STATUS_REGISTERED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_WITHDRAWN = 2;
    const STATUS_DISQUALIFIED = 3;
    const STATUS_EMAIL_SENT = 4;

    public $attendee_name;
    public $property_id;
    public $property_code;
    public $property_name;
    public $contest_name;
    public $event_name;
    public $registration_id;
    public $note;
    public $members;
    public $photo_portrait_2;
    public $photo_full_body_2;
    public $video_path;
    public $submission_token;
    public $submission_token_expires_at;
    public $submitted_at;
    public $personal_email;
    public $contestant_number;
    public $department_name;
    public $division_name;
    public $birthday;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function relations()
    {
        return array_merge(parent::relations(), array(
            'registration' => array(self::BELONGS_TO, 'Registrations', 'registration_id'),
        ));
    }

    public function rules()
    {
        return array(
            array('contest_id, attendee_id', 'required'),
            array('final_rank', 'numerical', 'integerOnly' => true),
            array('height_cm, weight_kg', 'numerical'),
            array('contest_id, attendee_id, candidate_number', 'length', 'max' => 20),
            array('measurements', 'length', 'max' => 50),
            array('talent, award, personal_email', 'length', 'max' => 255),
            array('photo_portrait, photo_full_body, photo_portrait_2, photo_full_body_2, video_path', 'length', 'max' => 500),
            array('status', 'length', 'max' => 12),
            array('candidate_number, height_cm, weight_kg, measurements, talent, bio, photo_portrait, photo_full_body, photo_portrait_2, photo_full_body_2, video_path, personal_email, award, final_rank, registered_at, status, submitted_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'contest_id' => 'Cuộc thi',
            'attendee_id' => 'Thí sinh',
            'contestant_number' => 'Số báo danh',
            'height_cm' => 'Chiều cao (cm)',
            'weight_kg' => 'Cân nặng (kg)',
            'measurements' => 'Số đo 3 vòng',
            'talent' => 'Tài năng',
            'bio' => 'Tiểu sử',
            'photo_portrait' => 'Ảnh chân dung 1',
            'photo_portrait_2' => 'Ảnh chân dung 2',
            'photo_full_body' => 'Ảnh toàn thân 1',
            'photo_full_body_2' => 'Ảnh toàn thân 2',
            'video_path' => 'Video dự thi',
            'personal_email' => 'Email cá nhân',
            'submitted_at' => 'Ngày gửi hồ sơ',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày đăng ký',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->contest_name = isset($data['contest_name']) ? $data['contest_name'] : '';
            $model->event_name = isset($data['event_name']) ? $data['event_name'] : '';
            $model->personal_email = isset($data['personal_email']) ? $data['personal_email'] : '';
            $model->photo_portrait_2 = isset($data['photo_portrait_2']) ? $data['photo_portrait_2'] : '';
            $model->photo_full_body_2 = isset($data['photo_full_body_2']) ? $data['photo_full_body_2'] : '';
            $model->video_path = isset($data['video_path']) ? $data['video_path'] : '';
            $model->submission_token = isset($data['submission_token']) ? $data['submission_token'] : '';
            $model->submission_token_expires_at = isset($data['submission_token_expires_at']) ? $data['submission_token_expires_at'] : '';
            $model->submitted_at = isset($data['submitted_at']) ? $data['submitted_at'] : '';
            $model->contestant_number = isset($data['contestant_number']) ? $data['contestant_number'] : (isset($data['candidate_number']) ? $data['candidate_number'] : '');
            $model->id = $id;
            return $model;
        }
        return null;
    }

    public static function generateSubmissionToken($id, $expiresAt = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_GENERATE_TOKEN, array('id' => $id));
        $data = array();
        if ($expiresAt) {
            $data['expires_at'] = $expiresAt;
        }
        return ApiClient::post($url, $data);
    }

    public static function generateAllSubmissionTokens($expiresAt)
    {
        return ApiClient::post(ApiEndpoints::BEAUTY_CONTESTANT_GENERATE_ALL_TOKENS, array(
            'expires_at' => $expiresAt,
        ));
    }

    public static function fetchByToken($token)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_BY_TOKEN, array('token' => $token));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->contest_name = isset($data['contest_name']) ? $data['contest_name'] : '';
            $model->event_name = isset($data['event_name']) ? $data['event_name'] : '';
            $model->personal_email = isset($data['personal_email']) ? $data['personal_email'] : '';
            $model->submitted_at = isset($data['submitted_at']) ? $data['submitted_at'] : '';
            return $model;
        }
        return null;
    }

    public static function submitByToken($token, $data)
    {
        $postData = array_merge(array('token' => $token), $data);
        // var_dump(json_encode($postData));
        // exit;
        return ApiClient::post(ApiEndpoints::BEAUTY_CONTESTANT_SUBMIT_BY_TOKEN, $postData);
    }

    public function storeViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        if ($this->registration_id) {
            $data['registration_id'] = $this->registration_id;
        }
        if ($this->note) {
            $data['note'] = $this->note;
        }
        return ApiClient::post(ApiEndpoints::BEAUTY_CONTESTANT_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    /**
     * Cập nhật trạng thái (duyệt / từ chối). KHÔNG gửi lại link ảnh/video để tránh
     * làm hỏng đường dẫn đã lưu. Backend validate đầy đủ nên vẫn phải kèm các
     * trường bắt buộc (contest_id, attendee_id) — nếu thiếu sẽ báo "Xác minh dữ
     * liệu thất bại".
     */
    public function updateStatusViaApi($status)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array(
            'status' => $status,
            'contest_id' => $this->contest_id,
            'attendee_id' => $this->attendee_id,
        ));
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
            'modelClass' => 'BeautyContestants',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_REGISTERED => '<span class="badge bg-secondary">Đã đăng ký</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_WITHDRAWN => '<span class="badge bg-warning">Rút lui</span>',
            self::STATUS_DISQUALIFIED => '<span class="badge bg-danger">Loại</span>',
            self::STATUS_EMAIL_SENT => '<span class="badge bg-info">Đã gửi email</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_REGISTERED => 'Đã đăng ký',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_WITHDRAWN => 'Rút lui',
            self::STATUS_DISQUALIFIED => 'Loại',
            self::STATUS_EMAIL_SENT => 'Đã gửi email',
        );
    }

    private static $_registrationPropertyCache = array();

    public static function getPropertyNameByRegistrationId($registrationId)
    {
        if (empty($registrationId)) {
            return '';
        }

        if (isset(self::$_registrationPropertyCache[$registrationId])) {
            return self::$_registrationPropertyCache[$registrationId];
        }

        // 1. Try to find the registration in the local database
        $registration = Registrations::model()->findByPk($registrationId);
        if ($registration) {
            if (isset($registration->property)) {
                $name = $registration->property->name;
                self::$_registrationPropertyCache[$registrationId] = $name;
                return $name;
            }
            if (!empty($registration->property_name)) {
                $name = $registration->property_name;
                self::$_registrationPropertyCache[$registrationId] = $name;
                return $name;
            }
        }

        // 2. Try to fetch the registration from the API
        try {
            $registration = Registrations::fetchFromApi($registrationId);
            if ($registration) {
                if (isset($registration->property)) {
                    $name = $registration->property->name;
                    self::$_registrationPropertyCache[$registrationId] = $name;
                    return $name;
                }
                if (!empty($registration->property_name)) {
                    $name = $registration->property_name;
                    self::$_registrationPropertyCache[$registrationId] = $name;
                    return $name;
                }
            }
        } catch (Exception $e) {
            // Ignore API exceptions
        }

        return '';
    }
}
