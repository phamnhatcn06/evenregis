<?php
/**
 * Update Attendee
 * @var Attendees $model
 */

$this->pageTitle = 'Cập nhật người tham dự';
$this->breadcrumbs = array(
    'Quản lý' => array('/admin/default/index'),
    'Người tham dự' => array('admin'),
    $model->full_name => array('view', 'id' => $model->id),
    'Cập nhật',
);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-edit"></i> Cập nhật: <?php echo CHtml::encode($model->full_name); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $this->renderPartial('_form', array(
                        'model' => $model,
                        'staffList' => $staffList,
                        'events' => $events,
                        'properties' => $properties,
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</div>
