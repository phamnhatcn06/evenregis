<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đã nhận hồ sơ</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#4caf50; padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:24px;">Đã nhận hồ sơ dự thi</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <span style="display:inline-block; width:80px; height:80px; background-color:#e8f5e9; border-radius:50%; line-height:80px; font-size:40px;">✓</span>
                            </div>

                            <p style="font-size:16px; color:#333;">Kính gửi <strong><?php echo CHtml::encode($contestant->attendee_name); ?></strong>,</p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                Chúng tôi đã nhận được hồ sơ dự thi của bạn cho cuộc thi <strong><?php echo CHtml::encode($contestant->contest_name); ?></strong>.
                            </p>

                            <!-- Summary Box -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background-color:#e8f5e9; border-radius:5px; margin:20px 0;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px 0; font-weight:bold; color:#2e7d32;">Thông tin đã gửi:</p>
                                        <table width="100%" style="font-size:14px; color:#555;">
                                            <tr>
                                                <td width="40%">Đơn vị:</td>
                                                <td><strong><?php echo CHtml::encode($contestant->property_name); ?></strong></td>
                                            </tr>
                                            <?php if (!empty($contestant->height_cm)): ?>
                                            <tr>
                                                <td>Chiều cao:</td>
                                                <td><strong><?php echo $contestant->height_cm; ?> cm</strong></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($contestant->weight_kg)): ?>
                                            <tr>
                                                <td>Cân nặng:</td>
                                                <td><strong><?php echo $contestant->weight_kg; ?> kg</strong></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($contestant->measurements)): ?>
                                            <tr>
                                                <td>Số đo 3 vòng:</td>
                                                <td><strong><?php echo CHtml::encode($contestant->measurements); ?></strong></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td>Thời gian gửi:</td>
                                                <td><strong><?php echo date('d/m/Y H:i'); ?></strong></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                Hồ sơ của bạn đang được Ban tổ chức xem xét. Chúng tôi sẽ thông báo kết quả trong thời gian sớm nhất.
                            </p>

                            <p style="font-size:15px; color:#555; margin-top:25px;">
                                Nếu bạn cần chỉnh sửa thông tin, vui lòng liên hệ Ban tổ chức.
                            </p>

                            <p style="font-size:15px; color:#333; margin-top:20px;">
                                Trân trọng,<br>
                                <strong>Ban tổ chức Đại hội</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f5f5f5; padding:20px; text-align:center; border-top:1px solid #e0e0e0;">
                            <p style="margin:0; font-size:12px; color:#999;">
                                Email này được gửi tự động, vui lòng không trả lời trực tiếp.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
