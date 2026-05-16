<?php
/**
 * Create Attendee
 * @var Attendees $model
 */

$this->pageTitle = 'Thêm người tham dự';
$this->breadcrumbs = array(
    'Quản lý' => array('/admin/default/index'),
    'Người tham dự' => array('admin'),
    'Thêm mới',
);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-user-plus"></i> Thêm người tham dự
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
