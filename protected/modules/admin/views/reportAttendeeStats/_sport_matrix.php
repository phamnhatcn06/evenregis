<?php
/**
 * Ma trận thống kê BAN THỂ THAO (dùng cho cả Vòng loại và Vòng chung kết).
 *
 * @var array $columns Danh sách môn (cột): mỗi phần tử có sport_id, name
 * @var array|null $data Kết quả aggregate: metrics, columns_data, total (null nếu không có)
 * @var string $title Tiêu đề khối (Vòng loại / Vòng chung kết)
 * @var string $headerClass Class màu cho header bảng
 */

$ageSuffix = function ($value) {
    return ($value === null || $value === '') ? '-' : ($value . ' tuổi');
};
$numOrDash = function ($value) {
    return ($value === null || $value === '' || $value === 0) ? '-' : number_format($value);
};
?>
<div class="card border shadow-sm mt-4">
    <div class="card-header <?php echo $headerClass; ?> d-flex align-items-center">
        <h5 class="mb-0"><i class="fa fa-futbol-o me-2"></i><?php echo CHtml::encode($title); ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($data) || empty($columns)): ?>
            <div class="text-center text-muted py-4">
                <i class="fa fa-info-circle me-1"></i> Chưa có dữ liệu
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 text-center align-middle">
                    <thead class="<?php echo $headerClass; ?>">
                        <tr>
                            <th class="text-start" style="min-width: 220px;">HẠNG MỤC</th>
                            <?php foreach ($columns as $col): ?>
                                <th style="min-width: 80px;"><?php echo CHtml::encode($col['name']); ?></th>
                            <?php endforeach; ?>
                            <th style="min-width: 90px;" class="table-active">TỔNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $metrics = $data['metrics'];
                        $colsData = $data['columns_data'];
                        $total = $data['total'];
                        foreach ($metrics as $key => $label):
                            $isAge = ($key === 'youngest' || $key === 'oldest');
                        ?>
                            <tr>
                                <td class="text-start fw-semibold"><?php echo CHtml::encode($label); ?></td>
                                <?php foreach ($columns as $col):
                                    $sid = $col['sport_id'];
                                    $val = isset($colsData[$sid][$key]) ? $colsData[$sid][$key] : null;
                                ?>
                                    <td>
                                        <?php
                                        if ($key === 'multi_sport') {
                                            echo '<span class="text-muted">—</span>';
                                        } elseif ($isAge) {
                                            echo $ageSuffix($val);
                                        } else {
                                            echo $numOrDash($val);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="fw-bold table-active">
                                    <?php
                                    $tval = isset($total[$key]) ? $total[$key] : null;
                                    echo $isAge ? $ageSuffix($tval) : $numOrDash($tval);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
