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

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'events-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'code', 'header' => 'Mã'),
                array('name' => 'name', 'header' => 'Tên sự kiện'),
                array('name' => 'from_date', 'header' => 'Từ ngày', 'value' => function ($data) {
                    return MyHelper::formatDate($data->from_date);
                }),
                array('name' => 'to_date', 'header' => 'Đến ngày', 'value' => function ($data) {
                    return MyHelper::formatDate($data->to_date);
                }),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => array(
                        '1' => 'Hoạt động',
                        '2' => 'Không hoạt động',
                    ),
                    'value' => function ($data) {
                        $labels = array(
                            '1' => '<span class="badge bg-success">Hoạt động</span>',
                            '2' => '<span class="badge bg-secondary">Không hoạt động</span>',
                        );
                        return isset($labels[$data->status]) ? $labels[$data->status] : $data->status;
                    }
                ),
                array('name' => 'created_at', 'header' => 'Ngày tạo', 'value' => function ($data) {
                    return MyHelper::formatDateTime($data->created_at);
                }),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/events');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'order' => array(array(0, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
