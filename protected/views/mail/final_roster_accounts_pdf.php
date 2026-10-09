<?php
/**
 * PDF danh sách tài khoản tham dự Vòng chung kết của MỘT đơn vị.
 * Đính kèm email gửi cho đơn vị để đơn vị chuyển định danh đăng nhập cho từng thành viên.
 *
 * @var string $eventName Tên sự kiện
 * @var string $unitName  Tên đơn vị
 * @var array  $people    Danh sách dòng roster (mảng thuộc tính)
 * @var string $loginUrl  URL trang đăng nhập cổng cá nhân
 */
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
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: "Times New Roman", serif; }
        body { margin: 0; padding: 0; color: #1a202c; font-size: 12px; }
        h1 { font-size: 17px; text-align: center; margin: 0 0 4px 0; text-transform: uppercase; }
        .sub { text-align: center; font-size: 13px; margin: 0 0 2px 0; }
        .unit { text-align: center; font-size: 14px; font-weight: bold; margin: 6px 0 2px 0; }
        .meta { text-align: center; font-size: 11px; color: #4a5568; margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #94a3b8; padding: 5px 6px; vertical-align: top; }
        th { background-color: #e2e8f0; font-weight: bold; text-align: center; font-size: 12px; }
        td.c { text-align: center; }
        .ident { font-weight: bold; }
        .empty { color: #c53030; font-style: italic; }
        .note { margin-top: 14px; font-size: 11px; color: #4a5568; line-height: 1.5; }
    </style>
</head>

<body>
    <h1>Danh sách tài khoản tham dự Vòng chung kết</h1>
    <p class="sub"><?php echo CHtml::encode($eventName); ?></p>
    <p class="unit">Đơn vị: <?php echo CHtml::encode($unitName); ?></p>
    <p class="meta">Tổng số: <?php echo count($people); ?> người &middot; Lập lúc: <?php echo date('d/m/Y H:i'); ?></p>

    <table>
        <thead>
            <tr>
                <th style="width:6%;">STT</th>
                <th style="width:34%;">Họ và tên</th>
                <th style="width:16%;">Mã nhân viên</th>
                <th style="width:22%;">Bộ phận</th>
                <th style="width:22%;">Định danh đăng nhập</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 0; foreach ($people as $row): $stt++; $ident = $col($row, 'login_identifier'); ?>
                <tr>
                    <td class="c"><?php echo $stt; ?></td>
                    <td><?php echo CHtml::encode($col($row, 'full_name')); ?></td>
                    <td class="c"><?php echo CHtml::encode($col($row, 'staff_code')); ?></td>
                    <td><?php echo CHtml::encode($division($row)); ?></td>
                    <td class="c <?php echo $ident === '' ? 'empty' : 'ident'; ?>">
                        <?php echo $ident === '' ? 'Chưa cấp' : CHtml::encode($ident); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="note">
        <strong>Hướng dẫn đăng nhập:</strong> Mỗi thành viên truy cập
        <strong><?php echo CHtml::encode($loginUrl); ?></strong>,
        nhập <strong>Định danh đăng nhập</strong> ở trên và tự đặt mã PIN cá nhân trong lần đăng nhập đầu tiên.
        Vui lòng giữ bảo mật định danh đăng nhập, không chia sẻ cho người ngoài đơn vị.
    </p>
</body>

</html>
