<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('/admin/beautyContests/admin'),
    'Vòng thi' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Chọn thí sinh đi tiếp',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
    array(
        'label' => 'Chấm điểm',
        'url' => $this->createUrl('scoring', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-star',
    ),
);
$this->Tabletitle = 'Chọn thí sinh đi tiếp - Vòng: ' . CHtml::encode($model->name);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/beauty-rounds-qualify.js',
    CClientScript::POS_END
);
?>

<?php if (empty($ranking)): ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>Chưa có thí sinh nào trong vòng này hoặc chưa chấm điểm.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-trophy me-2"></i>Bảng xếp hạng</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_select_top">
                            <i class="fa fa-check me-1"></i>Chọn Top
                            <input type="number" id="top_count" value="10" min="1" class="form-control form-control-sm d-inline-block" style="width:60px;">
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">
                                        <input type="checkbox" class="form-check-input" id="check_all">
                                    </th>
                                    <th style="width:60px">Hạng</th>
                                    <th style="width:80px">Ảnh</th>
                                    <th>SBD</th>
                                    <th>Họ tên</th>
                                    <th>Đơn vị</th>
                                    <th style="width:100px">Điểm</th>
                                    <th style="width:120px">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ranking as $index => $c): ?>
                                <tr class="<?php echo (isset($c['status']) && $c['status'] == 1) ? 'table-success' : ''; ?>">
                                    <td>
                                        <input type="checkbox" class="form-check-input contestant-check"
                                               value="<?php echo $c['registration_id']; ?>"
                                               <?php echo (isset($c['status']) && $c['status'] == 1) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($index < 3): ?>
                                            <span class="badge bg-<?php echo $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger'); ?> fs-6">
                                                <?php echo $index + 1; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-bold"><?php echo $index + 1; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['photo_portrait'])): ?>
                                            <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                                 alt="Ảnh" class="rounded" style="width:50px;height:50px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa fa-user fa-2x"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo CHtml::encode($c['contestant_number']); ?></strong></td>
                                    <td><?php echo CHtml::encode($c['contestant_name']); ?></td>
                                    <td><small><?php echo CHtml::encode($c['property_name']); ?></small></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?php echo $c['score'] !== null ? number_format($c['score'], 1) : '-'; ?></span>
                                    </td>
                                    <td><?php echo BeautyRoundResults::getStatusLabel(isset($c['status']) ? $c['status'] : 0); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top:20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-check-circle me-2"></i>Xác nhận chọn</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Vòng tiếp theo</label>
                        <select class="form-select" id="next_round_id">
                            <option value="">-- Không tự động gắn --</option>
                            <?php foreach ($nextRounds as $rid => $rname): ?>
                                <option value="<?php echo $rid; ?>"><?php echo CHtml::encode($rname); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Thí sinh đi tiếp sẽ tự động được gắn vào vòng này</small>
                    </div>

                    <div class="alert alert-info mb-3">
                        <strong>Đã chọn: <span id="selected_count">0</span> thí sinh</strong>
                    </div>

                    <button type="button" class="btn btn-success w-100" id="btn_qualify">
                        <i class="fa fa-check me-1"></i>Xác nhận đi tiếp
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<input type="hidden" id="round_id" value="<?php echo $model->id; ?>">
<input type="hidden" id="qualify_url" value="<?php echo $this->createUrl('qualify', array('id' => $model->id)); ?>">
