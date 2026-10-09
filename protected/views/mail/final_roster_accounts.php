<?php
/**
 * Email gửi cho ĐƠN VỊ: đề nghị đơn vị chuyển thông tin tài khoản (định danh đăng nhập) cho
 * từng thành viên vào vòng chung kết. Danh sách đầy đủ nằm ở file PDF đính kèm.
 * Dùng chung khung _email_header / _email_footer với các email hệ thống khác.
 *
 * @var string $eventName Tên sự kiện
 * @var string $unitName  Tên đơn vị
 * @var array  $people    Danh sách dòng roster (mảng thuộc tính)
 * @var string $loginUrl  URL trang đăng nhập cổng cá nhân
 */
$emailTitle     = 'Thông tin tài khoản tham dự Vòng chung kết';
$headerTitle    = 'THÔNG TIN TÀI KHOẢN THAM DỰ';
$headerSubtitle = $eventName !== '' ? $eventName : 'Đại hội Mường Thanh 2026';
$accentFrom     = '#0d6efd';
$accentTo       = '#0a58ca';
$containerWidth = 720;
include __DIR__ . '/_email_header.php';

$col = function ($row, $key) {
    return isset($row[$key]) && $row[$key] !== null ? (string) $row[$key] : '';
};
$division = function ($row) use ($col) {
    foreach (array('division_name', 'department_name') as $k) {
        $v = $col($row, $k);
        if ($v !== '') {
            return $v;
        }
    }
    return '';
};
?>
<!-- Body Content -->
<tr>
    <td style="padding:30px 25px;">
        <p style="font-size:16px; margin-top:0; margin-bottom:15px; color:#2d3748;">
            Kính gửi đơn vị: <strong><?php echo CHtml::encode($unitName); ?></strong>,
        </p>

        <p style="font-size:15px; color:#4a5568; margin-bottom:18px; line-height:1.6;">
            Ban tổ chức xin gửi <strong>thông tin tài khoản tham dự Vòng chung kết</strong> của
            <strong><?php echo count($people); ?></strong> thành viên thuộc đơn vị.
            Kính đề nghị đơn vị <strong>chuyển định danh đăng nhập tương ứng cho từng thành viên</strong>
            để mỗi người tự đăng nhập và thiết lập mã PIN cá nhân.
        </p>

        <table width="100%" cellpadding="10" cellspacing="0" style="border:1px solid #bfdbfe; border-radius:8px; border-collapse:collapse; background-color:#eff6ff; margin-bottom:22px; font-size:14px;">
            <tr>
                <td style="color:#1e3a8a;">
                    <strong>Hướng dẫn đăng nhập cho thành viên:</strong><br>
                    1. Truy cập: <a href="<?php echo CHtml::encode($loginUrl); ?>" style="color:#2563eb;"><?php echo CHtml::encode($loginUrl); ?></a><br>
                    2. Nhập <strong>Định danh đăng nhập</strong> được cấp (xem bảng bên dưới hoặc file PDF đính kèm).<br>
                    3. Tự đặt <strong>mã PIN cá nhân</strong> trong lần đăng nhập đầu tiên.
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:13px; border:1px solid #e2e8f0;">
            <thead>
                <tr style="background-color:#f1f5f9; color:#334155;">
                    <th style="border:1px solid #e2e8f0; text-align:center; width:40px;">STT</th>
                    <th style="border:1px solid #e2e8f0; text-align:left;">Họ và tên</th>
                    <th style="border:1px solid #e2e8f0; text-align:center;">Mã NV</th>
                    <th style="border:1px solid #e2e8f0; text-align:left;">Bộ phận</th>
                    <th style="border:1px solid #e2e8f0; text-align:center;">Định danh đăng nhập</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 0; foreach ($people as $row): $stt++; $ident = $col($row, 'login_identifier'); ?>
                    <tr>
                        <td style="border:1px solid #e2e8f0; text-align:center;"><?php echo $stt; ?></td>
                        <td style="border:1px solid #e2e8f0;"><?php echo CHtml::encode($col($row, 'full_name')); ?></td>
                        <td style="border:1px solid #e2e8f0; text-align:center;"><?php echo CHtml::encode($col($row, 'staff_code')); ?></td>
                        <td style="border:1px solid #e2e8f0;"><?php echo CHtml::encode($division($row)); ?></td>
                        <td style="border:1px solid #e2e8f0; text-align:center; font-weight:bold; <?php echo $ident === '' ? 'color:#c53030; font-style:italic; font-weight:normal;' : ''; ?>">
                            <?php echo $ident === '' ? 'Chưa cấp' : CHtml::encode($ident); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="font-size:13px; color:#64748b; margin-top:20px; line-height:1.6;">
            Danh sách đầy đủ được đính kèm trong file PDF của email này.
            Vui lòng giữ bảo mật thông tin định danh đăng nhập, không chia sẻ cho người ngoài đơn vị.
        </p>
    </td>
</tr>
<?php include __DIR__ . '/_email_footer.php'; ?>
