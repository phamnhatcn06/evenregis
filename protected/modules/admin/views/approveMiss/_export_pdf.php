<?php
/**
 * Trang in PDF danh sách thí sinh của một vòng thi.
 * Mỗi thí sinh là một trang landscape: bên trái ảnh đại diện, bên phải toàn bộ
 * thông tin. Header mỗi trang ghi rõ vòng thi mà thí sinh đang được gán.
 *
 * @var string $roundName Tên vòng thi (hoặc "Chưa phân vòng")
 * @var array  $contestants Danh sách thí sinh của vòng
 */

/**
 * Chuyển URL ảnh gốc sang URL thumbnail phục vụ qua MissFileController.
 */
if (!function_exists('missExportThumbUrl')):
function missExportThumbUrl($photoUrl, $width = 900)
{
    if (empty($photoUrl)) {
        return '';
    }
    $pos = strpos($photoUrl, '/uploads/miss/');
    if ($pos !== false) {
        $cleanPath = substr($photoUrl, $pos + strlen('/uploads/miss/'));
        return Yii::app()->createUrl('/admin/missFile/view') . '?path=' . urlencode($cleanPath) . '&w=' . $width;
    }
    return $photoUrl;
}
endif;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Danh sách thí sinh - <?php echo CHtml::encode($roundName); ?></title>
    <style>
        @page {
            size: A4 landscape;
            /* Lề 0 để trình duyệt không in ngày giờ / URL ở đầu-cuối trang */
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            margin: 0;
            color: #212529;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            position: sticky;
            top: 0;
            background: #343a40;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
        }

        .toolbar .title {
            font-size: 15px;
            font-weight: bold;
        }

        .toolbar button {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .pages {
            padding: 16px;
            background: #e9ecef;
        }

        .page {
            width: 277mm;
            min-height: 190mm;
            background: #fff;
            margin: 0 auto 16px auto;
            padding: 8mm;
            box-shadow: 0 0 6px rgba(0, 0, 0, .2);
            display: flex;
            flex-direction: column;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .page-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 6px;
            margin-bottom: 10px;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }

        .page-header .round-label {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .page-header .candidate-name {
            font-size: 22px;
            font-weight: bold;
        }

        .page-body {
            flex: 1;
            display: flex;
            gap: 12mm;
        }

        .photo-col {
            width: 88mm;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        /* Khung ảnh tỉ lệ cố định 9:16 (88mm x 156.4mm) */
        .photo-frame {
            width: 88mm;
            height: 156.4mm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 14px;
            background: #f1f3f5;
        }

        .info-col {
            flex: 1;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table.info th,
        table.info td {
            border: 1px solid #dee2e6;
            padding: 7px 10px;
            text-align: left;
            vertical-align: top;
        }

        table.info th {
            width: 34%;
            background: #f1f3f5;
            font-weight: 600;
        }

        .empty {
            padding: 40px;
            text-align: center;
            color: #6c757d;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .pages {
                padding: 0;
                background: #fff;
            }

            .page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 10mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span class="title">Danh sách thí sinh — <?php echo CHtml::encode($roundName); ?> (<?php echo count($contestants); ?> thí sinh)</span>
        <button type="button" onclick="window.print()">In / Lưu PDF</button>
    </div>

    <div class="pages">
        <?php if (empty($contestants)): ?>
            <div class="empty">Không có thí sinh nào trong vòng thi này.</div>
        <?php else: ?>
            <?php foreach ($contestants as $c): ?>
                <?php
                $attendeeName = '';
                if (isset($c->members) && !empty($c->members)) {
                    $attendeeName = $c->members[0]['attendee_name'];
                } elseif (!empty($c->attendee_name)) {
                    $attendeeName = $c->attendee_name;
                }

                $unitName = '';
                if (!empty($c->property_name)) {
                    $unitName = $c->property_name;
                } elseif (!empty($c->registration_id)) {
                    $unitName = BeautyContestants::getPropertyNameByRegistrationId($c->registration_id);
                }

                $photoUrl = '';
                if (!empty($c->photo_portrait)) {
                    $photoUrl = $c->photo_portrait;
                } elseif (!empty($c->photo_full_body)) {
                    $photoUrl = $c->photo_full_body;
                }
                $thumbUrl = missExportThumbUrl($photoUrl, 900);

                $birthDate = MyHelper::formatDate(isset($c->birthday) ? $c->birthday : null);
                $age = MyHelper::calculateAge(isset($c->birthday) ? $c->birthday : null);
                $birthdayDisplay = $birthDate !== '' ? ($birthDate . ($age !== null ? ' (' . $age . ' tuổi)' : '')) : '';

                $rows = array(
                    'Đơn vị' => $unitName,
                    'Phòng ban' => isset($c->department_name) ? $c->department_name : '',
                    'Ngày sinh' => $birthdayDisplay,
                    'Cuộc thi' => isset($c->contest_name) ? $c->contest_name : '',
                    'Chiều cao' => !empty($c->height_cm) ? ($c->height_cm . ' cm') : '',
                    'Cân nặng' => !empty($c->weight_kg) ? ($c->weight_kg . ' kg') : '',
                    'Số đo 3 vòng' => isset($c->measurements) ? $c->measurements : '',
                    'Năng khiếu' => isset($c->talent) ? $c->talent : '',
                    'Email cá nhân' => isset($c->personal_email) ? $c->personal_email : '',
                    'Giới thiệu' => isset($c->bio) ? $c->bio : '',
                    'Ngày gửi hồ sơ' => !empty($c->submitted_at) ? MyHelper::formatDateTime($c->submitted_at) : '',
                );
                ?>
                <div class="page">
                    <div class="page-header">
                        <span class="candidate-name"><?php echo CHtml::encode($attendeeName); ?></span>
                        <span class="round-label">Vòng: <?php echo CHtml::encode($roundName); ?></span>
                    </div>
                    <div class="page-body">
                        <div class="photo-col">
                            <div class="photo-frame">
                                <?php if ($thumbUrl): ?>
                                    <img src="<?php echo CHtml::encode($thumbUrl); ?>" alt="<?php echo CHtml::encode($attendeeName); ?>">
                                <?php else: ?>
                                    <span class="photo-placeholder">Chưa có ảnh</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-col">
                            <table class="info">
                                <tbody>
                                    <?php foreach ($rows as $label => $value): ?>
                                        <tr>
                                            <th><?php echo CHtml::encode($label); ?></th>
                                            <td><?php echo $value !== '' ? nl2br(CHtml::encode($value)) : '—'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
