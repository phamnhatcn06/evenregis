<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mời gửi hồ sơ dự thi Miss</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#e91e63; padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:24px;">Mời gửi hồ sơ dự thi Miss</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:16px; color:#333;">Kính gửi <strong><?php echo CHtml::encode($contestant->attendee_name); ?></strong>,</p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                Bạn đã được đăng ký tham gia cuộc thi <strong><?php echo CHtml::encode($contestant->contest_name); ?></strong>.
                            </p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                Vui lòng click vào nút bên dưới để gửi hồ sơ dự thi của bạn:
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo $submissionUrl; ?>"
                                           style="display:inline-block; background-color:#e91e63; color:#ffffff; padding:15px 40px; font-size:16px; font-weight:bold; text-decoration:none; border-radius:5px;">
                                            Gửi hồ sơ dự thi
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background-color:#fff3e0; border-left:4px solid #ff9800; margin:20px 0;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px 0; font-weight:bold; color:#e65100;">Thông tin cần chuẩn bị:</p>
                                        <ul style="margin:0; padding-left:20px; color:#555; line-height:1.8;">
                                            <li>Chiều cao, cân nặng, số đo 3 vòng</li>
                                            <li>2 ảnh chân dung (tỉ lệ 3:4, tối đa 20MB/ảnh)</li>
                                            <li>2 ảnh toàn thân (tỉ lệ 9:16, tối đa 20MB/ảnh)</li>
                                            <li>1 video dự thi (tối đa 4 phút, 500MB)</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <!-- Deadline -->
                            <p style="font-size:14px; color:#d32f2f; background-color:#ffebee; padding:10px 15px; border-radius:4px;">
                                <strong>Lưu ý:</strong> Link có hiệu lực đến <strong><?php echo $expiresAt; ?></strong>
                            </p>

                            <p style="font-size:15px; color:#555; margin-top:25px;">
                                Nếu bạn có thắc mắc, vui lòng liên hệ Ban tổ chức.
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
