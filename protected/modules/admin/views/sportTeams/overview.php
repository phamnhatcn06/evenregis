<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    'Tổng quan',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Tổng quan đội thể thao';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="fa fa-building fa-3x text-primary mb-3"></i>
                <h4>Xem theo đơn vị</h4>
                <p class="text-muted">Hiển thị tất cả môn thể thao và đội thi của một đơn vị</p>
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalSelectProperty">
                    <i class="fa fa-search me-2"></i>Chọn đơn vị
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="fa fa-futbol-o fa-3x text-success mb-3"></i>
                <h4>Xem theo bộ môn</h4>
                <p class="text-muted">Hiển thị tất cả đội thi đấu của một bộ môn</p>
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#modalSelectSport">
                    <i class="fa fa-search me-2"></i>Chọn bộ môn
                </button>
            </div>
        </div>
    </div>
</div>

<div id="result-container"></div>

<?php $this->renderPartial('_modal_select_property', array('properties' => $properties, 'events' => $events)); ?>
<?php $this->renderPartial('_modal_select_sport', array('sports' => $sports, 'events' => $events)); ?>

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sport-teams-overview.js',
    CClientScript::POS_END
);
?>
