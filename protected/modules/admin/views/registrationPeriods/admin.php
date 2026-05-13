<?php
$this->breadcrumbs = array(
    'Đợt đăng ký' => array('admin'),
    Yii::t('app', 'Manage'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Create'),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);

$this->Tabletitle = 'Quản lý đợt đăng ký';
?>

<div class="card">
    <div class="card-body">
        <?php $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'registration-periods-grid',
            'dataProvider' => $dataProvider,
            'itemsCssClass' => 'table table-bordered table-striped mb-0',
            'columns' => array(
                array(
                    'name' => 'id',
                    'headerHtmlOptions' => array('style' => 'width:60px'),
                ),
                'name',
                array(
                    'name' => 'event_name',
                    'header' => 'Sự kiện',
                    'value' => 'isset($data->event_name) ? $data->event_name : ""',
                ),
                array(
                    'name' => 'start_time',
                    'header' => 'Bắt đầu',
                    'value' => '$data->start_time ? MyHelper::formatDateTime($data->start_time) : "-"',
                ),
                array(
                    'name' => 'end_time',
                    'header' => 'Kết thúc',
                    'value' => '$data->end_time ? MyHelper::formatDateTime($data->end_time) : "-"',
                ),
                array(
                    'name' => 'max_per_org',
                    'header' => 'Tối đa/đơn vị',
                    'value' => '$data->max_per_org ?: "Không giới hạn"',
                    'headerHtmlOptions' => array('style' => 'width:120px'),
                ),
                array(
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'value' => 'RegistrationPeriods::getStatusBadge($data)',
                    'headerHtmlOptions' => array('style' => 'width:100px'),
                ),
                array(
                    'class' => 'CButtonColumn',
                    'header' => 'Thao tác',
                    'template' => '{view} {update} {delete}',
                    'buttons' => array(
                        'view' => array(
                            'url' => 'Yii::app()->createUrl("/admin/registrationPeriods/view", array("id" => $data->id))',
                            'options' => array('class' => 'btn btn-sm btn-info', 'title' => 'Xem'),
                            'label' => '<i class="fa fa-eye"></i>',
                            'imageUrl' => false,
                        ),
                        'update' => array(
                            'url' => 'Yii::app()->createUrl("/admin/registrationPeriods/update", array("id" => $data->id))',
                            'options' => array('class' => 'btn btn-sm btn-warning', 'title' => 'Sửa'),
                            'label' => '<i class="fa fa-pencil"></i>',
                            'imageUrl' => false,
                        ),
                        'delete' => array(
                            'url' => 'Yii::app()->createUrl("/admin/registrationPeriods/delete", array("id" => $data->id))',
                            'options' => array('class' => 'btn btn-sm btn-danger btn-delete', 'title' => 'Xóa'),
                            'label' => '<i class="fa fa-trash"></i>',
                            'imageUrl' => false,
                            'click' => 'function(){
                                if(confirm("Bạn có chắc muốn xóa đợt đăng ký này?")) {
                                    var url = $(this).attr("href");
                                    $.ajax({
                                        type: "POST",
                                        url: url,
                                        success: function(data) {
                                            $.fn.yiiGridView.update("registration-periods-grid");
                                        }
                                    });
                                }
                                return false;
                            }',
                        ),
                    ),
                    'headerHtmlOptions' => array('style' => 'width:120px'),
                    'htmlOptions' => array('class' => 'text-center'),
                ),
            ),
        )); ?>
    </div>
</div>