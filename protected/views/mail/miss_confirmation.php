<?php
/**
 * Email xác nhận đã nhận hồ sơ dự thi Miss Mường Thanh 2026.
 * Dùng chung khung _email_header / _email_footer; đồng bộ tông màu vàng với email mời dự thi.
 */
$emailTitle     = 'Xác nhận đã nhận hồ sơ dự thi';
$headerTitle    = 'MISS MƯỜNG THANH 2026';
$headerSubtitle = 'Đã nhận hồ sơ dự thi';
$accentFrom     = '#d4a574';
$accentTo       = '#b8860b';
$containerWidth = 600;
include __DIR__ . '/_email_header.php';
?>
                    <!-- Content -->
                    <tr>
                        <td style="padding:30px 40px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <span style="display:inline-block; width:80px; height:80px; background-color:#fff8e7; color:#b8860b; border-radius:50%; line-height:80px; font-size:40px;">✓</span>
                            </div>

                            <p style="font-size:16px; color:#333;">Kính gửi <strong><?php echo CHtml::encode($contestant->attendee_name); ?></strong>,</p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                Chúng tôi đã nhận được hồ sơ dự thi của bạn cho cuộc thi <strong><?php echo CHtml::encode($contestant->contest_name); ?></strong>.
                            </p>

                            <!-- Summary Box -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background: linear-gradient(135deg, #fff8e7 0%, #fef3e2 100%); border-left:4px solid #d4a574; border-radius:0 8px 8px 0; margin:20px 0;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px 0; font-weight:bold; color:#8b6914;">Thông tin đã gửi:</p>
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

                            <p style="font-size:15px; color:#1a202c; margin-top:20px;">
                                Trân trọng,<br>
                                <strong>BAN TỔ CHỨC MISS MƯỜNG THANH 2026</strong>
                            </p>
                        </td>
                    </tr>
<?php include __DIR__ . '/_email_footer.php'; ?>
