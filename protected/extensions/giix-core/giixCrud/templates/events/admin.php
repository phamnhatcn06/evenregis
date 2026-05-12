<?php
$this->breadcrumbs = array(
    Events::label(2),
    Yii::t('app', 'Admin'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Create') . ' ',
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = Yii::t('app', 'List') . ' ' . $model->label();
?>

<?php
$this->widget('ext.edatatables.EDataTables', array(
    'id' => 'events-grid',
    'dataProvider' => $model->search(),
    'language' => 'vi',
    'filter' => true,
    'columns' => array(
        array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
        array('name' => 'code', 'header' => 'Mã'),
        array('name' => 'name', 'header' => 'Tên sự kiện'),
        array('name' => 'from_date', 'header' => 'Từ ngày', 'type' => 'date'),
        array('name' => 'to_date', 'header' => 'Đến ngày', 'type' => 'date'),
        array(
            'name' => 'status',
            'header' => 'Trạng thái',
            'type' => 'raw',
            'filter' => array(
                'active' => 'Hoạt động',
                'inactive' => 'Không hoạt động',
                'completed' => 'Hoàn thành',
            ),
            'value' => function ($data) {
                $labels = array(
                    'active' => '<span class="badge bg-success">Hoạt động</span>',
                    'inactive' => '<span class="badge bg-secondary">Không hoạt động</span>',
                    'completed' => '<span class="badge bg-info">Hoàn thành</span>',
                );
                return isset($labels[$data->status]) ? $labels[$data->status] : $data->status;
            }
        ),
        array('name' => 'created_at', 'header' => 'Ngày tạo', 'type' => 'datetime'),
        array(
            'header' => 'Thao tác',
            'width' => '100px',
            'type' => 'raw',
            'filter' => false,
            'sortable' => false,
            'value' => function ($data) {
                return IconHelper::actionButtons($data, array('view', 'update'), '/admin/events');
            }
        ),
    ),
    'options' => array(
        'pageLength' => 25,
        'order' => array(array(0, 'desc')),
    ),
));
?>
