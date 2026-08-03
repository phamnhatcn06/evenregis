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

                            <p style="font-size:14px; color:#4a5568; margin-top:20px; line-height:1.6;">
                                Mọi thắc mắc hoặc yêu cầu hỗ trợ, quý đơn vị vui lòng liên hệ với Ban tổ chức để được giải đáp kịp thời.
                            </p>

                            <!-- Lưu ý điều chỉnh đăng ký -->
                            <div style="background-color:#fef9c3; border:1px solid #fde047; border-radius:6px; padding:15px 20px; margin-top:20px; color:#854d0e; font-size:14px; line-height:1.6;">
                                ⚠️ <strong>Lưu ý:</strong> Thời gian điều chỉnh cuối cùng của đăng ký (nếu có) là từ <strong>10-15.08.2026</strong>. Sau ngày <strong>15.08.2026</strong>, các ĐV ký &amp; gửi bản xác nhận cử VĐV thi đấu Vòng Cụm gửi BTC.
                            </div>

                            <p style="font-size:15px; color:#1a202c; margin-top:25px; margin-bottom:0;">
                                Trân trọng,<br>
                                <strong>BAN TỔ CHỨC ĐẠI HỘI MƯỜNG THANH 2026</strong>
                            </p>
                        </td>
                    </tr>
<?php include __DIR__ . '/_email_footer.php'; ?>
