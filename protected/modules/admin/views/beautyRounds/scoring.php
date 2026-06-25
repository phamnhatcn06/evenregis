<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('/admin/beautyContests/admin'),
    'Vòng thi' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Chấm điểm',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
    array(
        'label' => 'Chọn đi tiếp',
        'url' => $this->createUrl('qualify', array('id' => $model->id)),
        'color' => 'success',
        'icon' => 'fa-check-circle',
    ),
);
$this->Tabletitle = 'Chấm điểm vòng: ' . CHtml::encode($model->name);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/beauty-rounds-scoring.js',
    CClientScript::POS_END
);
?>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="text-muted">Điểm tối đa:</span>
                <strong class="text-primary ms-1"><?php echo $model->max_score ?: 100; ?></strong>
            </div>
            <div class="col-auto">
                <span class="text-muted">Trọng số:</span>
                <strong class="text-primary ms-1"><?php echo $model->weight ?: 1; ?></strong>
            </div>
            <div class="col-auto">
                <span class="text-muted">Số thí sinh:</span>
                <strong class="text-primary ms-1"><?php echo count($contestants); ?></strong>
            </div>
        </div>
    </div>
</div>

<?php if (empty($contestants)): ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>Chưa có thí sinh nào trong vòng này.
        <a href="<?php echo $this->createUrl('assignContestants', array('id' => $model->id)); ?>" class="alert-link">Gắn thí sinh ngay</a>
    </div>
<?php else: ?>
    <div class="row" id="contestants_grid">
        <?php foreach ($contestants as $index => $c): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card h-100 contestant-card" data-result-id="<?php echo $c['id']; ?>">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="badge bg-dark"><?php echo CHtml::encode($c['contestant_number']); ?></span>
                    <span class="score-badge badge bg-primary fs-6"><?php echo $c['score'] !== null ? number_format($c['score'], 1) : '-'; ?></span>
                </div>
                <div class="text-center p-3">
                    <?php if (!empty($c['photo_portrait'])): ?>
                        <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                             alt="Ảnh" class="rounded shadow-sm" style="width:150px;height:200px;object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                             style="width:150px;height:200px;margin:0 auto;">
                            <i class="fa fa-user fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-0">
                    <h6 class="card-title text-center mb-1"><?php echo CHtml::encode($c['contestant_name']); ?></h6>
                    <p class="text-muted text-center small mb-3"><?php echo CHtml::encode($c['property_name']); ?></p>

                    <div class="mb-2">
                        <label class="form-label small mb-1">Điểm (0-<?php echo $model->max_score ?: 100; ?>)</label>
                        <input type="number" class="form-control form-control-sm score-input"
                               value="<?php echo $c['score']; ?>"
                               min="0" max="<?php echo $model->max_score ?: 100; ?>" step="0.5">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Ghi chú</label>
                        <textarea class="form-control form-control-sm note-input" rows="2"><?php echo CHtml::encode($c['note']); ?></textarea>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm w-100 btn-save-score">
                        <i class="fa fa-save me-1"></i>Lưu điểm
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<input type="hidden" id="save_score_url" value="<?php echo $this->createUrl('saveScore'); ?>">
