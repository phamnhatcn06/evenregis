<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Phiếu xác nhận đăng ký tham dự</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            color: #1e293b;
            line-height: 1.4;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .sub-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #475569;
            margin-bottom: 15px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }

        .summary-table td {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .summary-label {
            background-color: #f8fafc;
            font-weight: bold;
            color: #475569;
            width: 25%;
        }

        .section-header {
            background-color: #0d6efd;
            color: #ffffff;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .section-header-green {
            background-color: #198754;
            color: #ffffff;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .group-title {
            font-weight: bold;
            font-size: 11pt;
            color: #0f172a;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .col-stt {
            width: 6%;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            border: 1px solid #94a3b8;
            padding: 6px 5px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            page-break-inside: avoid;
            font-size: 9pt;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .gender-nam {
            color: #0284c7;
            font-weight: bold;
        }

        .gender-nu {
            color: #e11d48;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Header Header Top -->
    <table class="header-table">
        <tr>
            <td style="width:50%; text-align:left; font-size:10px;">
                <span style="display:inline-block; text-align:center;">
                    <strong>TẬP ĐOÀN MƯỜNG THANH</strong><br>
                    <?php echo CHtml::encode(isset($model->property_name) ? mb_strtoupper($model->property_name) : 'ĐƠN VỊ DỰ THI'); ?>
                </span>
            </td>
            <td style="width:50%; text-align:center; font-size:10px;">
                <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
                Độc lập - Tự do - Hạnh phúc
            </td>
        </tr>
    </table>

    <div class="header-title">PHIẾU XÁC NHẬN ĐĂNG KÝ THAM DỰ</div>
    <div class="sub-title"><?php echo CHtml::encode(isset($model->event_name) ? $model->event_name : 'Đại hội Mường Thanh 2026'); ?></div>

    <!-- Summary Box matching exact user layout -->
    <table class="summary-table">
        <tr>
            <td class="summary-label">Đơn vị đăng ký:</td>
            <td style="font-weight:bold;"><?php echo CHtml::encode(isset($model->property_name) ? $model->property_name : '-'); ?></td>
        </tr>
        <tr>
            <td class="summary-label">Đợt đăng ký:</td>
            <td><?php echo CHtml::encode(isset($model->period_name) ? $model->period_name : '-'); ?></td>
        </tr>
        <tr>
            <td class="summary-label">Thời gian nộp:</td>
            <td><?php echo !empty($model->submitted_at) ? MyHelper::formatDateTime($model->submitted_at) : date('d-m-Y H:i'); ?></td>
        </tr>
        <tr>
            <td class="summary-label">Hạng mục đã đăng ký:</td>
            <td><?php echo CHtml::encode(!empty($registeredCategories) ? $registeredCategories : '-'); ?></td>
        </tr>
        <tr>
            <td class="summary-label">Nội dung:</td>
            <td>
                <?php if (!empty($contentSummaryLines)): ?>
                    <?php foreach ($contentSummaryLines as $idx => $line): ?>
                        <div><?php echo CHtml::encode($line); ?><?php echo ($idx < count($contentSummaryLines) - 1) ? ',' : ''; ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="summary-label">Tổng số người tham dự:</td>
            <td style="font-weight:bold; color:#0d6efd;"><?php echo isset($attendeesCount) ? (int)$attendeesCount : 0; ?> người</td>
        </tr>
    </table>

    <!-- Section 1: Đợt 1 - Đăng ký thi đấu thể thao -->
    <?php if (!empty($isDot1) || !empty($sportTeams)): ?>
        <div class="section-header">
            DANH SÁCH CHI TIẾT CÁC ĐỘI THI ĐẤU THỂ THAO
        </div>

        <?php if (empty($sportTeams)): ?>
            <p style="color:#64748b; font-style:italic;">Chưa có thông tin đăng ký môn thể thao nào.</p>
        <?php else: ?>
            <?php foreach ($sportTeams as $tIdx => $team): ?>
                <div class="group-title">
                    <?php echo CHtml::encode($team['sport_name']); ?> - <?php echo CHtml::encode($team['team_name']); ?>
                    (<?php echo count($team['members']); ?> VĐV)
                    <?php if (!empty($team['is_alliance'])): ?>
                        <span style="color:#c2410c;">[Liên quân: <?php echo CHtml::encode(implode(', ', $team['alliance_properties'])); ?>]</span>
                    <?php endif; ?>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center col-stt">STT</th>
                            <th width="28%">Họ và tên VĐV</th>
                            <th width="12%" class="text-center">Giới tính</th>
                            <th width="24%">Đơn vị</th>
                            <th width="30%">Chức danh - Bộ phận</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($team['members'] as $mIdx => $member): ?>
                            <tr>
                                <td class="text-center"><?php echo $mIdx + 1; ?></td>
                                <td class="text-bold"><?php echo CHtml::encode($member['attendee_name']); ?></td>
                                <td class="text-center">
                                    <?php
                                    $g = isset($member['gender']) ? $member['gender'] : null;
                                    $gStr = ($g !== null) ? strtolower((string)$g) : '';
                                    if ($g === 1 || $g === '1' || $gStr === 'male' || $gStr === 'nam') {
                                        echo '<span class="gender-nam">Nam</span>';
                                    } elseif ($g === 0 || $g === '0' || $gStr === 'female' || $gStr === 'nữ' || $gStr === 'nu') {
                                        echo '<span class="gender-nu">Nữ</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo CHtml::encode(!empty($member['property_name']) ? $member['property_name'] : '-'); ?></td>
                                <td>
                                    <?php
                                    $pos = array();
                                    if (!empty($member['position_name'])) $pos[] = CHtml::encode($member['position_name']);
                                    if (!empty($member['division_name'])) $pos[] = CHtml::encode($member['division_name']);
                                    echo !empty($pos) ? implode(' - ', $pos) : '-';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Section 1b: Đợt 1 - Văn nghệ -->
    <?php if (!empty($talentEntries)): ?>
        <div class="section-header">
            DANH SÁCH CHI TIẾT TIẾT MỤC VĂN NGHỆ
        </div>
        <?php foreach ($talentEntries as $entry): ?>
            <div class="group-title">
                <?php echo CHtml::encode(!empty($entry['category_name']) ? $entry['category_name'] : 'Tiết mục'); ?><?php if (!empty($entry['title'])): ?> - <?php echo CHtml::encode($entry['title']); ?><?php endif; ?>
                (<?php echo count($entry['members']); ?> người)
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center col-stt">STT</th>
                        <th width="34%">Họ và tên</th>
                        <th width="12%" class="text-center">Giới tính</th>
                        <th width="24%">Chức danh</th>
                        <th width="24%">Bộ phận</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entry['members'])): ?>
                        <tr><td colspan="5" class="text-center">Chưa có thành viên.</td></tr>
                    <?php else: ?>
                        <?php foreach ($entry['members'] as $mIdx => $member): ?>
                            <tr>
                                <td class="text-center"><?php echo $mIdx + 1; ?></td>
                                <td class="text-bold"><?php echo CHtml::encode($member['attendee_name']); ?></td>
                                <td class="text-center">
                                    <?php
                                    $g = isset($member['gender']) ? $member['gender'] : null;
                                    $gStr = ($g !== null) ? strtolower((string)$g) : '';
                                    if ($g === 1 || $g === '1' || $gStr === 'male' || $gStr === 'nam') {
                                        echo '<span class="gender-nam">Nam</span>';
                                    } elseif ($g === 0 || $g === '0' || $gStr === 'female' || $gStr === 'nữ' || $gStr === 'nu') {
                                        echo '<span class="gender-nu">Nữ</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo CHtml::encode(!empty($member['position_name']) ? $member['position_name'] : '-'); ?></td>
                                <td><?php echo CHtml::encode(!empty($member['division_name']) ? $member['division_name'] : '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Section 1c: Đợt 1 - Miss Mường Thanh -->
    <?php if (!empty($beautyContestants)): ?>
        <div class="section-header">
            ĐỢT 1: DANH SÁCH THÍ SINH MISS MƯỜNG THANH
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center col-stt">STT</th>
                    <th width="12%" class="text-center">SBD</th>
                    <th width="32%">Họ và tên thí sinh</th>
                    <th width="24%">Cuộc thi</th>
                    <th width="26%">Chức danh - Bộ phận</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($beautyContestants as $cIdx => $contestant): ?>
                    <tr>
                        <td class="text-center"><?php echo $cIdx + 1; ?></td>
                        <td class="text-center"><?php echo CHtml::encode(!empty($contestant['candidate_number']) ? $contestant['candidate_number'] : '-'); ?></td>
                        <td class="text-bold"><?php echo CHtml::encode($contestant['attendee_name']); ?></td>
                        <td><?php echo CHtml::encode(!empty($contestant['contest_name']) ? $contestant['contest_name'] : '-'); ?></td>
                        <td>
                            <?php
                            $pos = array();
                            if (!empty($contestant['position_name'])) $pos[] = CHtml::encode($contestant['position_name']);
                            if (!empty($contestant['division_name'])) $pos[] = CHtml::encode($contestant['division_name']);
                            echo !empty($pos) ? implode(' - ', $pos) : '-';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Section 2: Đợt 2 - Đăng ký thi nghiệp vụ -->
    <?php if (!empty($isDot2) || !empty($competitionRegistrations)): ?>
        <div class="section-header-green">
            ĐỢT 2: DANH SÁCH CHI TIẾT THÍ SINH THI NGHIỆP VỤ
        </div>

        <?php if (empty($competitionRegistrations)): ?>
            <p style="color:#64748b; font-style:italic;">Chưa có thông tin đăng ký thi nghiệp vụ nào.</p>
        <?php else: ?>
            <?php foreach ($competitionRegistrations as $compId => $compData): ?>
                <div class="group-title">
                    <?php echo CHtml::encode($compData['competition_name']); ?>
                    (<?php echo count($compData['attendees']); ?> thí sinh)
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center col-stt">STT</th>
                            <th width="30%">Họ và tên thí sinh</th>
                            <th width="12%" class="text-center">Giới tính</th>
                            <th width="26%">Chức danh / Vị trí</th>
                            <th width="26%">Bộ phận / Phòng ban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compData['attendees'] as $cIdx => $candidate): ?>
                            <tr>
                                <td class="text-center"><?php echo $cIdx + 1; ?></td>
                                <td class="text-bold"><?php echo CHtml::encode($candidate['attendee_name']); ?></td>
                                <td class="text-center">
                                    <?php
                                    $cg = isset($candidate['gender']) ? $candidate['gender'] : null;
                                    $cgStr = ($cg !== null) ? strtolower((string)$cg) : '';
                                    if ($cg === 1 || $cg === '1' || $cgStr === 'male' || $cgStr === 'nam') {
                                        echo '<span class="gender-nam">Nam</span>';
                                    } elseif ($cg === 0 || $cg === '0' || $cgStr === 'female' || $cgStr === 'nữ' || $cgStr === 'nu') {
                                        echo '<span class="gender-nu">Nữ</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo CHtml::encode(!empty($candidate['position_name']) ? $candidate['position_name'] : '-'); ?></td>
                                <td><?php echo CHtml::encode(!empty($candidate['division_name']) ? $candidate['division_name'] : '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td>
                <strong>ĐẠI DIỆN ĐƠN VỊ ĐĂNG KÝ</strong><br>
                <span style="font-style:italic; font-size:10px; color:#64748b;">(Ký, ghi rõ họ tên)</span>
            </td>
            <td>
                <strong>BAN TỔ CHỨC ĐẠI HỘI MƯỜNG THANH 2026</strong><br>
                <span style="font-style:italic; font-size:10px; color:#64748b;">(Xác nhận hệ thống)</span>
            </td>
        </tr>
    </table>

</body>

</html>
