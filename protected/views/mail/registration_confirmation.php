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
                                    <td style="color:#475569; font-weight:bold; background-color:#f1f5f9;">Tổng số người tham dự:</td>
                                    <td style="color:#0d6efd; font-weight:bold; font-size:15px;"><?php echo isset($attendeesCount) ? (int)$attendeesCount : 0; ?> người</td>
                                </tr>
                            </table>

                            <!-- PDF Notification Box -->
                            <div style="background-color:#e0f2fe; border:1px solid #bae6fd; border-radius:6px; padding:15px 20px; margin-bottom:25px; color:#0369a1; font-size:14px; line-height:1.6;">
                                📎 <strong>Tệp đính kèm:</strong> Danh sách chi tiết VĐV tham gia thi đấu các môn thể thao và các thí sinh dự thi nghiệp vụ được đính kèm trong file PDF kèm theo email này.
                            </div>

                            <p style="font-size:14px; color:#4a5568; margin-top:20px; line-height:1.6;">
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