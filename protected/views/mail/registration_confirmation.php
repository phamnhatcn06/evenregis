<?php

/**
 * Email xác nhận thông tin đăng ký (Đợt 1 - Thể thao/Văn nghệ/Miss, Đợt 2 - Thi nghiệp vụ).
 * Dùng chung khung _email_header / _email_footer với các email hệ thống khác.
 */
$emailTitle     = 'Xác nhận thông tin đăng ký';
$headerTitle    = 'XÁC NHẬN THÔNG TIN ĐĂNG KÝ';
$headerSubtitle = isset($model->event_name) && $model->event_name !== '' ? $model->event_name : 'Đại hội Mường Thanh 2026';
$accentFrom     = '#0d6efd';
$accentTo       = '#0a58ca';
$containerWidth = 780;
include __DIR__ . '/_email_header.php';
?>
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

        <!-- Summary Info Box (Bảng tổng quan mới) -->
        <table width="100%" cellpadding="12" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:25px; font-size:14px; border-collapse:collapse;">
            <tr>
                <td width="30%" style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9;">Đơn vị đăng ký:</td>
                <td style="color:#1e293b; font-weight:bold; border-bottom:1px solid #e2e8f0;"><?php echo CHtml::encode(isset($model->property_name) ? $model->property_name : '-'); ?></td>
            </tr>
            <tr>
                <td style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9;">Đợt đăng ký:</td>
                <td style="color:#1e293b; border-bottom:1px solid #e2e8f0;"><?php echo CHtml::encode(isset($model->period_name) ? $model->period_name : '-'); ?></td>
            </tr>
            <tr>
                <td style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9;">Thời gian nộp:</td>
                <td style="color:#1e293b; border-bottom:1px solid #e2e8f0;"><?php echo !empty($model->submitted_at) ? MyHelper::formatDateTime($model->submitted_at) : date('d/m/Y H:i'); ?></td>
            </tr>
            <tr>
                <td style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9;">Hạng mục đã đăng ký:</td>
                <td style="color:#1e293b; border-bottom:1px solid #e2e8f0;"><?php echo CHtml::encode(!empty($registeredCategories) ? $registeredCategories : '-'); ?></td>
            </tr>
            <tr>
                <td style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9; vertical-align:top;">Nội dung:</td>
                <td style="color:#1e293b; border-bottom:1px solid #e2e8f0; line-height:1.7;">
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
                <td style="color:#475569; font-weight:bold; border-bottom:1px solid #e2e8f0; background-color:#f1f5f9;">Tổng số người tham dự:</td>
                <td style="color:#0d6efd; font-weight:bold; font-size:15px; border-bottom:1px solid #e2e8f0;"><?php echo isset($attendeesCount) ? (int)$attendeesCount : 0; ?> người</td>
            </tr>
            <tr>
                <td style="color:#475569; font-weight:bold; background-color:#f1f5f9;">Thời gian in xác nhận:</td>
                <td style="color:#1e293b;"><?php echo date('d/m/Y H:i'); ?></td>
            </tr>
        </table>

        <!-- PDF Notification Box -->
        <div style="background-color:#e0f2fe; border:1px solid #bae6fd; border-radius:6px; padding:15px 20px; margin-bottom:25px; color:#0369a1; font-size:14px; line-height:1.6;">
            📎 <strong>Tệp đính kèm:</strong> Danh sách chi tiết người tham dự các nội dung<?php echo !empty($registeredCategories) ? ' (' . CHtml::encode($registeredCategories) . ')' : ''; ?> được đính kèm trong file PDF kèm theo email này.
        </div>

        <?php if (!empty($personnelChanges)): ?>
        <!-- Thay đổi nhân sự (thay thế / huỷ tư cách) -->
        <div style="margin-bottom:25px;">
            <div style="font-size:15px; font-weight:bold; color:#b45309; margin-bottom:10px;">🔄 Thay đổi nhân sự</div>
            <table width="100%" cellpadding="10" cellspacing="0" style="border:1px solid #fde68a; border-radius:8px; border-collapse:collapse; font-size:13px; background-color:#fffbeb;">
                <tr style="background-color:#fef3c7;">
                    <th style="text-align:left; color:#92400e; border-bottom:1px solid #fde68a; width:14%;">Loại</th>
                    <th style="text-align:left; color:#92400e; border-bottom:1px solid #fde68a; width:43%;">Nhân sự</th>
                    <th style="text-align:left; color:#92400e; border-bottom:1px solid #fde68a; width:43%;">Nội dung ảnh hưởng &amp; lý do</th>
                </tr>
                <?php foreach ($personnelChanges as $chg): ?>
                <tr>
                    <td style="vertical-align:top; border-bottom:1px solid #fef3c7; color:#1e293b;">
                        <?php
                        if ($chg['action'] === AttendeeReplacements::ACTION_REPLACE) {
                            $badgeColor = '#2563eb'; // xanh — thay thế
                        } elseif ($chg['action'] === AttendeeReplacements::ACTION_CANCEL_CONTENT) {
                            $badgeColor = '#f59e0b'; // hổ phách — huỷ nội dung
                        } else {
                            $badgeColor = '#dc2626'; // đỏ — huỷ tư cách
                        }
                        ?>
                        <span style="display:inline-block; padding:2px 8px; border-radius:4px; font-weight:bold; color:#ffffff; background-color:<?php echo $badgeColor; ?>;">
                            <?php echo CHtml::encode($chg['action_label']); ?>
                        </span>
                        <?php if (!empty($chg['performed_at'])): ?>
                            <div style="color:#94a3b8; font-size:11px; margin-top:4px;"><?php echo CHtml::encode($chg['performed_at']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align:top; border-bottom:1px solid #fef3c7; color:#1e293b; line-height:1.6;">
                        <div><span style="color:#64748b;">Người cũ:</span> <strong><?php echo CHtml::encode($chg['old_name']); ?></strong><?php echo !empty($chg['old_staff_code']) ? ' (' . CHtml::encode($chg['old_staff_code']) . ')' : ''; ?></div>
                        <?php if ($chg['action'] === AttendeeReplacements::ACTION_REPLACE && !empty($chg['new_name'])): ?>
                            <div><span style="color:#64748b;">Người thay:</span> <strong style="color:#16a34a;"><?php echo CHtml::encode($chg['new_name']); ?></strong><?php echo !empty($chg['new_staff_code']) ? ' (' . CHtml::encode($chg['new_staff_code']) . ')' : ''; ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align:top; border-bottom:1px solid #fef3c7; color:#1e293b; line-height:1.6;">
                        <?php if (!empty($chg['content_lines'])): ?>
                            <?php foreach ($chg['content_lines'] as $line): ?>
                                <div>• <?php echo CHtml::encode($line); ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="color:#94a3b8;">Không có nội dung thi đấu.</div>
                        <?php endif; ?>
                        <?php if (!empty($chg['reason'])): ?>
                            <div style="margin-top:4px; color:#64748b; font-style:italic;">Lý do: <?php echo CHtml::encode($chg['reason']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <p style="font-size:14px; color:#4a5568; margin-top:20px; line-height:1.6;">
            Mọi thắc mắc hoặc yêu cầu hỗ trợ, quý đơn vị vui lòng liên hệ với Ban tổ chức để được giải đáp kịp thời.
        </p>

        <!-- Lưu ý điều chỉnh đăng ký -->
        <div style="background-color:#fef9c3; border:1px solid #fde047; border-radius:6px; padding:15px 20px; margin-top:20px; font-size:14px; line-height:1.7;">
            <div style="color:#dc2626; font-weight:bold; margin-bottom:6px;">⚠️ Lưu ý:</div>
            <div style="color:#dc2626; font-weight:bold;">
                - Các trường hợp đã gửi hồ sơ thay đổi người tham dự đang được xử lý và cập nhật. Hệ thống sẽ gửi lại xác nhận cho đơn vị vào ngày 10/08/2026.
            </div>
            <div style="color:#dc2626; font-weight:bold; margin-bottom:4px;">- Thời gian điều chỉnh cuối cùng của đăng ký (nếu có) là từ 10-15.08.2026.</div>
            <div style="color:#dc2626; font-weight:bold; margin-bottom:4px;">- Sau ngày 15.08.2026, các ĐV ký &amp; gửi bản xác nhận cử VĐV thi đấu Vòng Cụm gửi BTC. Ảnh của các thành viên trong <strong>bản xác nhận</strong> phải được <strong>đóng dấu giáp lai/xác nhận trên từng ảnh</div>

        </div>


        <p style="font-size:15px; color:#1a202c; margin-top:25px; margin-bottom:0;">
            Trân trọng,<br>
            <strong>BAN TỔ CHỨC ĐẠI HỘI MƯỜNG THANH 2026</strong>
        </p>
    </td>
</tr>
<?php include __DIR__ . '/_email_footer.php'; ?>