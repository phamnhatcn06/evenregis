<?php

$compReg = (object) array(
    'id' => 241,
    'competition_id' => 2,
    'attendee_id' => 2025,
    'registration_id' => 141,
    'candidate_number' => null,
    'status' => 0,
    'registered_at' => null,
    'confirmed_by' => null,
    'confirmed_at' => null,
    'note' => null,
    'created_at' => "2026-06-11T07:56:53.000000Z",
    'updated_at' => "2026-06-11T07:56:53.000000Z",
    'competition' => array(
        'id' => 2,
        'name' => "An ninh – Kỹ thuật – Công nghệ Thông tin"
    ),
    'attendee' => array(
        'id' => 2025,
        'full_name' => "Văn Đức Lịch",
        'property' => array(
            'id' => 8,
            'name' => "Mường Thanh Luxury Buôn Ma Thuột",
            'region' => array(
                'id' => 8,
                'name' => "CỤM 7"
            )
        ),
        'department' => array(
            'id' => 1969,
            'name' => "Phòng Kỹ thuật"
        ),
        'position' => array(
            'id' => 9380,
            'name' => "Nhân viên kỹ thuật"
        )
    ),
    'registration' => array(
        'id' => 141,
        'code' => null
    )
);

// Run the extraction logic from controller
$propId = isset($compReg->property_id) ? $compReg->property_id : null;
if (!$propId && isset($compReg->attendee)) {
    $att = $compReg->attendee;
    $propId = is_array($att) ? (isset($att['property_id']) ? $att['property_id'] : null) : (isset($att->property_id) ? $att->property_id : null);
}

echo "Extracted PropId: " . var_export($propId, true) . "\n";

$attendeeName = isset($compReg->attendee_name) ? $compReg->attendee_name : '-';
$attendeePosition = '';
$attendeeGender = isset($compReg->attendee_gender) ? $compReg->attendee_gender : '';

if (isset($compReg->position)) {
    $pos = $compReg->position;
    if (is_array($pos) && isset($pos['name'])) {
        $attendeePosition = $pos['name'];
    } elseif (is_object($pos) && isset($pos->name)) {
        $attendeePosition = $pos->name;
    } elseif (is_string($pos)) {
        $attendeePosition = $pos;
    }
}

if (isset($compReg->attendee)) {
    $att = $compReg->attendee;
    if (is_array($att)) {
        $attendeeName = isset($att['full_name']) ? $att['full_name'] : $attendeeName;
        $attendeeGender = isset($att['gender']) ? $att['gender'] : $attendeeGender;
        if (empty($attendeePosition) && isset($att['position'])) {
            $pos = $att['position'];
            if (is_array($pos) && isset($pos['name'])) {
                $attendeePosition = $pos['name'];
            } elseif (is_object($pos) && isset($pos->name)) {
                $attendeePosition = $pos->name;
            } elseif (is_string($pos)) {
                $attendeePosition = $pos;
            }
        }
    }
}

echo "Extracted Attendee Name: " . $attendeeName . "\n";
echo "Extracted Attendee Position: " . $attendeePosition . "\n";
