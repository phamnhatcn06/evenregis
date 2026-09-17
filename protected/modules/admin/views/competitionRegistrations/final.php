<?php
/* @var $this CompetitionRegistrationsController */
/* @var $competitions array id=>name */

$this->breadcrumbs = array(
    'Thi nghiệp vụ' => array('admin'),
    'Chọn vào chung kết',
);
$this->Tabletitle = 'Chọn thí sinh vào vòng chung kết';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/finals-manager.js',
    CClientScript::POS_END
);
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Cuộc thi nghiệp vụ <span class="text-danger">*</span></label>
                <select id="finals-scope" class="form-select">
                    <option value="">-- Chọn cuộc thi --</option>
                    <?php foreach ($competitions as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="finals-app"
     data-mode="single"
     data-candidates-url="<?php echo $this->createUrl('/admin/competitionRegistrations/finalCandidates'); ?>"
     data-list-url="<?php echo $this->createUrl('/admin/competitionRegistrations/finalList'); ?>"
     data-add-url="<?php echo $this->createUrl('/admin/competitionRegistrations/finalAdd'); ?>"
     data-remove-url="<?php echo $this->createUrl('/admin/competitionRegistrations/finalRemove'); ?>">
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Thí sinh đủ điều kiện</h5>
                    <button type="button" id="finals-add-btn" class="btn btn-primary btn-sm" disabled>
                        <i class="fa fa-arrow-right me-1"></i>Thêm vào chung kết
                    </button>
                </div>
                <div class="card-body">
                    <input type="text" id="finals-search" class="form-control mb-3" placeholder="Tìm kiếm...">
                    <div id="finals-candidates" style="max-height: 60vh; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Đã vào chung kết <span class="badge bg-success" id="finals-count">0</span></h5>
                </div>
                <div class="card-body">
                    <div id="finals-finalists" style="max-height: 66vh; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
