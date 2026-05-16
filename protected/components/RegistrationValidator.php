<?php

/**
 * RegistrationValidator - Validation logic cho đăng ký mở rộng (Section 15)
 */
class RegistrationValidator
{
    const PORTRAIT_WIDTH = 530;
    const PORTRAIT_HEIGHT = 530;
    const MAX_FILE_SIZE_IMAGE = 5242880; // 5MB
    const MAX_FILE_SIZE_CONTRACT = 10485760; // 10MB
    const ALLOWED_IMAGE_TYPES = array('image/jpeg', 'image/png');
    const ALLOWED_CONTRACT_TYPES = array('image/jpeg', 'image/png', 'application/pdf');

    /**
     * BR-REG-02: Validate file đính kèm bắt buộc
     */
    public static function validateRequiredDocuments($attendee)
    {
        $errors = array();

        if (empty($attendee->cccd_front_path)) {
            $errors[] = 'Chưa upload ảnh CCCD mặt trước';
        }
        if (empty($attendee->cccd_back_path)) {
            $errors[] = 'Chưa upload ảnh CCCD mặt sau';
        }
        if (empty($attendee->portrait_path)) {
            $errors[] = 'Chưa upload ảnh chân dung';
        }
        if (empty($attendee->contract_path)) {
            $errors[] = 'Chưa upload hợp đồng lao động';
        }

        return $errors;
    }

    /**
     * BR-REG-03: Validate kích thước ảnh chân dung 530x530px
     */
    public static function validatePortraitDimension($filePath)
    {
        if (!file_exists($filePath)) {
            return array('File không tồn tại');
        }

        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            return array('Không thể đọc thông tin ảnh');
        }

        list($width, $height) = $imageInfo;

        if ($width !== self::PORTRAIT_WIDTH || $height !== self::PORTRAIT_HEIGHT) {
            return array(
                sprintf(
                    'Ảnh chân dung phải có kích thước %dx%dpx (hiện tại: %dx%dpx)',
                    self::PORTRAIT_WIDTH,
                    self::PORTRAIT_HEIGHT,
                    $width,
                    $height
                )
            );
        }

        return array();
    }

    /**
     * Validate uploaded file
     */
    public static function validateUploadedFile($file, $type = 'image')
    {
        $errors = array();

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return array('File upload không hợp lệ');
        }

        $maxSize = $type === 'contract' ? self::MAX_FILE_SIZE_CONTRACT : self::MAX_FILE_SIZE_IMAGE;
        $allowedTypes = $type === 'contract' ? self::ALLOWED_CONTRACT_TYPES : self::ALLOWED_IMAGE_TYPES;

        if ($file['size'] > $maxSize) {
            $errors[] = sprintf('Dung lượng file vượt quá %dMB', $maxSize / 1048576);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'Định dạng file không được hỗ trợ';
        }

        return $errors;
    }

    /**
     * BR-REG-05: Đếm số môn root sports đã đăng ký
     */
    public static function countRootSportsRegistered($attendeeId)
    {
        $sql = "
            SELECT COUNT(DISTINCT root_sport.id) AS root_sport_count
            FROM sport_team_members stm
            JOIN sport_teams st ON stm.team_id = st.id
            JOIN sports s ON st.sport_id = s.id
            LEFT JOIN sports root_sport ON
                (s.parent_id IS NULL AND root_sport.id = s.id)
                OR (s.parent_id IS NOT NULL AND root_sport.id = s.parent_id)
            WHERE stm.attendee_id = :attendee_id
              AND root_sport.id IS NOT NULL
        ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':attendee_id', $attendeeId);

        return (int) $command->queryScalar();
    }

    /**
     * BR-REG-05: Kiểm tra có thể đăng ký thêm môn thể thao không
     */
    public static function canRegisterMoreSports($attendeeId, $eventId)
    {
        $event = Events::model()->findByPk($eventId);
        if (!$event) {
            return array('valid' => false, 'message' => 'Không tìm thấy sự kiện');
        }

        $maxSports = isset($event->max_sports_per_attendee) ? (int) $event->max_sports_per_attendee : 3;
        $currentCount = self::countRootSportsRegistered($attendeeId);

        return array(
            'valid' => $currentCount < $maxSports,
            'current' => $currentCount,
            'max' => $maxSports,
            'remaining' => max(0, $maxSports - $currentCount),
            'message' => $currentCount >= $maxSports
                ? sprintf('Đã đạt giới hạn %d môn thể thao', $maxSports)
                : null,
        );
    }

    /**
     * BR-REG-05: Kiểm tra môn mới có cùng root với môn đã đăng ký không
     */
    public static function wouldExceedSportLimit($attendeeId, $sportId, $eventId)
    {
        $sport = Sports::model()->findByPk($sportId);
        if (!$sport) {
            return array('valid' => false, 'message' => 'Không tìm thấy môn thể thao');
        }

        $rootSportId = $sport->parent_id ? $sport->parent_id : $sport->id;

        $existingRootSql = "
            SELECT DISTINCT
                CASE WHEN s.parent_id IS NULL THEN s.id ELSE s.parent_id END AS root_id
            FROM sport_team_members stm
            JOIN sport_teams st ON stm.team_id = st.id
            JOIN sports s ON st.sport_id = s.id
            WHERE stm.attendee_id = :attendee_id
        ";

        $command = Yii::app()->db->createCommand($existingRootSql);
        $command->bindValue(':attendee_id', $attendeeId);
        $existingRoots = $command->queryColumn();

        if (in_array($rootSportId, $existingRoots)) {
            return array('valid' => true, 'message' => null, 'isNewRoot' => false);
        }

        $result = self::canRegisterMoreSports($attendeeId, $eventId);

        return array(
            'valid' => $result['valid'],
            'message' => $result['message'],
            'isNewRoot' => true,
            'currentRoots' => count($existingRoots),
            'maxRoots' => $result['max'],
        );
    }

    /**
     * BR-REG-06: Kiểm tra attendee có đủ điều kiện thi nghiệp vụ không
     */
    public static function canRegisterCompetition($attendeeId, $competitionId)
    {
        $attendee = Attendees::model()->findByPk($attendeeId);
        if (!$attendee) {
            return array('valid' => false, 'message' => 'Không tìm thấy người tham dự');
        }

        if (!$attendee->staff_id) {
            return array('valid' => false, 'message' => 'Người tham dự không liên kết với nhân viên');
        }

        $allowedDepartments = CompetitionDepartments::getDepartmentCodes($competitionId);

        if (empty($allowedDepartments)) {
            return array('valid' => true, 'message' => null);
        }

        $sql = "
            SELECT s.department_code
            FROM staffs s
            WHERE s.id = :staff_id
        ";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':staff_id', $attendee->staff_id);
        $departmentCode = $command->queryScalar();

        if (empty($departmentCode)) {
            return array('valid' => false, 'message' => 'Nhân viên chưa có mã phòng ban');
        }

        if (!in_array($departmentCode, $allowedDepartments)) {
            return array(
                'valid' => false,
                'message' => sprintf(
                    'Phòng ban "%s" không đủ điều kiện tham gia cuộc thi này',
                    $departmentCode
                ),
            );
        }

        return array('valid' => true, 'message' => null);
    }

    /**
     * Lấy danh sách attendees đủ điều kiện thi nghiệp vụ
     */
    public static function getEligibleAttendeesForCompetition($competitionId, $eventId, $propertyId = null)
    {
        $allowedDepartments = CompetitionDepartments::getDepartmentCodes($competitionId);

        $criteria = new CDbCriteria;
        $criteria->with = array('staff');
        $criteria->addCondition('t.event_id = :event_id');
        $criteria->addCondition('t.staff_id IS NOT NULL');
        $criteria->params[':event_id'] = $eventId;

        if ($propertyId) {
            $criteria->addCondition('t.property_id = :property_id');
            $criteria->params[':property_id'] = $propertyId;
        }

        if (!empty($allowedDepartments)) {
            $criteria->addInCondition('staff.department_code', $allowedDepartments);
        }

        return Attendees::model()->findAll($criteria);
    }
}
