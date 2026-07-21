<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ' => array('admin'),
    $entry->title => array('view', 'id' => $entry->id),
    'Chấm điểm',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $entry->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Chấm điểm tiết mục: ' . CHtml::encode($entry->title);
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-4">
                <span class="text-muted">Tiết mục:</span>
                <strong class="ms-1"><?php echo CHtml::encode($entry->title); ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Đơn vị:</span>
                <strong class="ms-1"><?php echo CHtml::encode($entry->property_name); ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Số phiếu điểm:</span>
                <strong class="text-primary ms-1" id="score_count"><?php echo count($scores); ?></strong>
            </div>
            <div class="col-md-2 text-md-end">
                <span class="text-muted">Điểm TB:</span>
                <span class="badge bg-success fs-6 ms-1" id="score_average"><?php echo $average !== null ? number_format($average, 2) : '-'; ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-star me-2"></i>Danh sách điểm giám khảo</h5>
        <button type="button" class="btn btn-sm btn-primary" id="btn_add_score">
            <i class="fa fa-plus me-1"></i>Thêm điểm
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">ID</th>
                        <th style="width:120px">Giám khảo</th>
                        <th>Tiêu chí</th>
                        <th style="width:100px">Điểm</th>
                        <th>Nhận xét</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="scores_tbody">
                    <?php if (empty($scores)): ?>
                    <tr id="no_scores_row"><td colspan="6" class="text-center text-muted py-4">Chưa có điểm nào</td></tr>
                    <?php else: foreach ($scores as $s): ?>
                    <tr data-id="<?php echo $s->id; ?>"
                        data-judge-id="<?php echo CHtml::encode($s->judge_id); ?>"
                        data-criteria="<?php echo CHtml::encode($s->criteria); ?>"
                        data-score="<?php echo CHtml::encode($s->score); ?>"
                        data-note="<?php echo CHtml::encode($s->note); ?>">
                        <td><?php echo CHtml::encode($s->id); ?></td>
                        <td><?php echo CHtml::encode($s->judge_name ?: $s->judge_id); ?></td>
                        <td><?php echo CHtml::encode($s->criteria) ?: '-'; ?></td>
                        <td><span class="badge bg-primary fs-6"><?php echo number_format($s->score, 2); ?></span></td>
                        <td><?php echo CHtml::encode($s->note) ?: '-'; ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-score">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-score">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->renderPartial('_modal_score', array('entry' => $entry)); ?>

<input type="hidden" id="save_score_url" value="<?php echo $this->createUrl('saveScore'); ?>">
<input type="hidden" id="delete_score_url" value="<?php echo $this->createUrl('deleteScore'); ?>">
<input type="hidden" id="entry_id" value="<?php echo $entry->id; ?>">
