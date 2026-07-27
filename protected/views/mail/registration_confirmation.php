<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận thông tin đăng ký</title>
</head>

<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f6f9; color:#333333; line-height:1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:30px 10px;">
        <tr>
            <td align="center">
                <table width="780" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.08); border:1px solid #e2e8f0;">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); padding:30px 25px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0 0 8px 0; font-size:24px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">
                                XÁC NHẬN THÔNG TIN ĐĂNG KÝ
                            </h1>
                            <p style="color:#e0e7ff; margin:0; font-size:15px;">
                                <?php echo CHtml::encode(isset($model->event_name) ? $model->event_name : 'Đại hội Mường Thanh 2026'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding:30px 25px;">
                            <p style="font-size:16px; margin-top:0; margin-bottom:15px; color:#2d3748;">
                                Kính gửi đơn vị: <strong><?php echo CHtml::encode(isset($model->property_name) ? $model->property_name : 'Đơn vị'); ?></strong>,
                            </p>

                            <p style="font-size:15px; color:#4a5568; margin-bottom:20px; line-height:1.6;">
                                Ban tổ chức xin gửi xác nhận thông tin chi tiết phiếu đăng ký tham dự
                                <strong><?php echo CHtml::encode(isset($model->period_name) ? $model->period_name : ''); ?></strong>
                                của đơn vị như dưới đây:
                            </p>

                            <!-- Summary Info Box -->
                            <table width="100%" cellpadding="12" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:25px; font-size:14px;">
                                <tr>
                                    <td width="30%" style="color:#718096; font-weight:bold; border-bottom:1px solid #edf2f7;">Đơn vị đăng ký:</td>
                                    <td style="color:#1a202c; font-weight:bold; border-bottom:1px solid #edf2f7;"><?php echo CHtml::encode(isset($model->property_name) ? $model->property_name : '-'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#718096; font-weight:bold; border-bottom:1px solid #edf2f7;">Sự kiện:</td>
                                    <td style="color:#1a202c; border-bottom:1px solid #edf2f7;"><?php echo CHtml::encode(isset($model->event_name) ? $model->event_name : '-'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#718096; font-weight:bold; border-bottom:1px solid #edf2f7;">Đợt đăng ký:</td>
                                    <td style="color:#1a202c; border-bottom:1px solid #edf2f7;"><?php echo CHtml::encode(isset($model->period_name) ? $model->period_name : '-'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#718096; font-weight:bold; border-bottom:1px solid #edf2f7;">Thời gian nộp:</td>
                                    <td style="color:#1a202c; border-bottom:1px solid #edf2f7;"><?php echo !empty($model->submitted_at) ? MyHelper::formatDateTime($model->submitted_at) : date('d/m/Y H:i'); ?></td>
                                </tr>
                                <tr>
                                    <td style="color:#718096; font-weight:bold;">Tổng số người tham dự:</td>
                                    <td style="color:#0d6efd; font-weight:bold; font-size:15px;"><?php echo isset($attendeesCount) ? (int)$attendeesCount : 0; ?> người</td>
                                </tr>
                            </table>

                            <!-- Section 1: Đợt 1 - Đăng ký thi đấu thể thao -->
                            <?php if (!empty($isDot1) || !empty($sportTeams)): ?>
                                <div style="margin-top:30px; margin-bottom:25px;">
                                    <div style="background-color:#0d6efd; color:#ffffff; padding:10px 15px; font-weight:bold; font-size:16px; border-radius:6px 6px 0 0; text-transform:uppercase;">
                                        🏆 ĐỢT 1: THÔNG TIN ĐĂNG KÝ CÁC ĐỘI THI ĐẤU THỂ THAO
                                    </div>
                                    <div style="border:1px solid #0d6efd; border-top:none; padding:15px; border-radius:0 0 6px 6px; background-color:#ffffff;">
                                        <?php if (empty($sportTeams)): ?>
                                            <p style="color:#718096; font-style:italic; margin:5px 0;">Chưa có thông tin đăng ký môn thể thao nào.</p>
                                        <?php else: ?>
                                            <?php foreach ($sportTeams as $tIdx => $team): ?>
                                                <div style="margin-bottom:20px; <?php echo ($tIdx > 0) ? 'border-top:1px dashed #cbd5e1; padding-top:15px;' : ''; ?>">
                                                    <div style="font-weight:bold; font-size:15px; color:#1e293b; margin-bottom:8px;">
                                                        ⚽ <?php echo CHtml::encode($team['sport_name']); ?>
                                                        <span style="color:#0d6efd;"> - <?php echo CHtml::encode($team['team_name']); ?></span>
                                                        <span style="font-weight:normal; font-size:13px; color:#64748b;">(<?php echo count($team['members']); ?> VĐV)</span>
                                                        <?php if (!empty($team['is_alliance'])): ?>
                                                            <span style="display:inline-block; background-color:#ffedd5; color:#9a3412; font-size:12px; font-weight:bold; padding:2px 8px; border-radius:12px; margin-left:6px;">
                                                                Liên quân: <?php echo CHtml::encode(implode(', ', $team['alliance_properties'])); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:13px; margin-top:5px; table-layout:fixed;">
                                                        <thead>
                                                            <tr style="background-color:#f1f5f9; color:#475569; text-align:left;">
                                                                <th width="45" style="border:1px solid #cbd5e1; text-align:center;">STT</th>
                                                                <th width="220" style="border:1px solid #cbd5e1;">Họ và tên VĐV</th>
                                                                <th width="80" style="border:1px solid #cbd5e1; text-align:center;">Giới tính</th>
                                                                <th width="180" style="border:1px solid #cbd5e1;">Đơn vị</th>
                                                                <th style="border:1px solid #cbd5e1;">Chức danh - Bộ phận</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($team['members'] as $mIdx => $member): ?>
                                                                <tr style="background-color:<?php echo ($mIdx % 2 == 0) ? '#ffffff' : '#f8fafc'; ?>;">
                                                                    <td style="border:1px solid #cbd5e1; text-align:center; color:#64748b;"><?php echo $mIdx + 1; ?></td>
                                                                    <td style="border:1px solid #cbd5e1; font-weight:bold; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo CHtml::encode($member['attendee_name']); ?></td>
                                                                    <td style="border:1px solid #cbd5e1; text-align:center;">
                                                                        <?php
                                                                        $g = isset($member['gender']) ? $member['gender'] : null;
                                                                        $gStr = ($g !== null) ? strtolower((string)$g) : '';
                                                                        if ($g === 1 || $g === '1' || $gStr === 'male' || $gStr === 'nam') {
                                                                            echo '<span style="color:#0284c7; font-weight:bold;">Nam</span>';
                                                                        } elseif ($g === 0 || $g === '0' || $gStr === 'female' || $gStr === 'nữ' || $gStr === 'nu') {
                                                                            echo '<span style="color:#e11d48; font-weight:bold;">Nữ</span>';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td style="border:1px solid #cbd5e1; color:#475569;"><?php echo CHtml::encode(!empty($member['property_name']) ? $member['property_name'] : '-'); ?></td>
                                                                    <td style="border:1px solid #cbd5e1; color:#475569;">
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
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Section 2: Đợt 2 - Đăng ký thi nghiệp vụ -->
                            <?php if (!empty($isDot2) || !empty($competitionRegistrations)): ?>
                                <div style="margin-top:30px; margin-bottom:25px;">
                                    <div style="background-color:#198754; color:#ffffff; padding:10px 15px; font-weight:bold; font-size:16px; border-radius:6px 6px 0 0; text-transform:uppercase;">
                                        🎖️ ĐỢT 2: THÔNG TIN ĐĂNG KÝ THÍ SINH THI NGHIỆP VỤ
                                    </div>
                                    <div style="border:1px solid #198754; border-top:none; padding:15px; border-radius:0 0 6px 6px; background-color:#ffffff;">
                                        <?php if (empty($competitionRegistrations)): ?>
                                            <p style="color:#718096; font-style:italic; margin:5px 0;">Chưa có thông tin đăng ký thi nghiệp vụ nào.</p>
                                        <?php else: ?>
                                            <?php foreach ($competitionRegistrations as $compId => $compData): ?>
                                                <div style="margin-bottom:20px;">
                                                    <div style="font-weight:bold; font-size:15px; color:#1e293b; margin-bottom:8px;">
                                                        🏅 <?php echo CHtml::encode($compData['competition_name']); ?>
                                                        <span style="font-weight:normal; font-size:13px; color:#64748b;">(<?php echo count($compData['attendees']); ?> thí sinh)</span>
                                                    </div>

                                                    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:13px; margin-top:5px; table-layout:fixed;">
                                                        <thead>
                                                            <tr style="background-color:#f1f5f9; color:#475569; text-align:left;">
                                                                <th width="45" style="border:1px solid #cbd5e1; text-align:center;">STT</th>
                                                                <th width="220" style="border:1px solid #cbd5e1;">Họ và tên thí sinh</th>
                                                                <th width="80" style="border:1px solid #cbd5e1; text-align:center;">Giới tính</th>
                                                                <th width="180" style="border:1px solid #cbd5e1;">Chức danh / Vị trí</th>
                                                                <th style="border:1px solid #cbd5e1;">Bộ phận / Phòng ban</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($compData['attendees'] as $cIdx => $candidate): ?>
                                                                <tr style="background-color:<?php echo ($cIdx % 2 == 0) ? '#ffffff' : '#f8fafc'; ?>;">
                                                                    <td style="border:1px solid #cbd5e1; text-align:center; color:#64748b;"><?php echo $cIdx + 1; ?></td>
                                                                    <td style="border:1px solid #cbd5e1; font-weight:bold; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo CHtml::encode($candidate['attendee_name']); ?></td>
                                                                    <td style="border:1px solid #cbd5e1; text-align:center;">
                                                                        <?php
                                                                        $cg = isset($candidate['gender']) ? $candidate['gender'] : null;
                                                                        $cgStr = ($cg !== null) ? strtolower((string)$cg) : '';
                                                                        if ($cg === 1 || $cg === '1' || $cgStr === 'male' || $cgStr === 'nam') {
                                                                            echo '<span style="color:#0284c7; font-weight:bold;">Nam</span>';
                                                                        } elseif ($cg === 0 || $cg === '0' || $cgStr === 'female' || $cgStr === 'nữ' || $cgStr === 'nu') {
                                                                            echo '<span style="color:#e11d48; font-weight:bold;">Nữ</span>';
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td style="border:1px solid #cbd5e1; color:#475569;"><?php echo CHtml::encode(!empty($candidate['position_name']) ? $candidate['position_name'] : '-'); ?></td>
                                                                    <td style="border:1px solid #cbd5e1; color:#475569;"><?php echo CHtml::encode(!empty($candidate['division_name']) ? $candidate['division_name'] : '-'); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <p style="font-size:14px; color:#4a5568; margin-top:25px; line-height:1.6;">
                                Mọi thông tin thắc mắc hoặc yêu cầu hỗ trợ, quý đơn vị vui lòng liên hệ với Ban tổ chức để được giải đáp kịp thời.
                            </p>

                            <p style="font-size:15px; color:#1a202c; margin-top:25px; margin-bottom:0;">
                                Trân trọng,<br>
                                <strong>BAN TỔ CHỨC ĐẠI HỘI MƯỜNG THANH 2026</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc; padding:20px; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                Email này được gửi tự động từ hệ thống Quản lý Đăng ký Đại hội Mường Thanh.<br>
                                Vui lòng không trả lời trực tiếp email này.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>