<?php
$this->breadcrumbs = array(
    Sports::label(2),
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
        <style>
            .sport-parent {
                background-color: #e8f4f8 !important;
                font-weight: 600;
            }
        </style>
        <?php
        $levelMapJs = $levelMap;
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'sports-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array(
                    'name' => 'name',
                    'header' => 'Tên môn',
                    'type' => 'raw',
                    'value' => function ($data) use ($levelMapJs) {
                        $level = isset($levelMapJs[$data->id]) ? $levelMapJs[$data->id] : 0;
                        $prefix = $level > 0 ? str_repeat('—', $level) . ' ' : '';
                        $parentId = $data->parent_id ? $data->parent_id : 0;
                        return '<span data-parent="' . $parentId . '">' . $prefix . CHtml::encode($data->name) . '</span>';
                    }
                ),
                array(
                    'name' => 'type',
                    'header' => 'Loại',
                    'type' => 'raw',
                    'filter' => array(
                        'Đồng đội' => 'Đồng đội',
                        'Cá nhân' => 'Cá nhân',
                    ),
                    'value' => function ($data) {
                        $types = array('team' => 'Đồng đội', 'individual' => 'Cá nhân');
                        return isset($types[$data->type]) ? $types[$data->type] : $data->type;
                    }
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => array(
                        '1' => 'Hoạt động',
                        '0' => 'Không hoạt động',
                    ),
                    'value' => function ($data) {
                        return $data->is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Không hoạt động</span>';
                    }
                ),
                array('name' => 'sort_order', 'header' => 'Thứ tự'),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/sports');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 50,
                'ordering' => false,
                'createdRow' => 'js:function(row, data, dataIndex) {
                    var span = $(row).find("span[data-parent]");
                    var pid = span.data("parent");
                    if (pid == 0 || pid === "" || pid === null) {
                        $(row).addClass("sport-parent");
                    }
                }',
            ),
        ));
        ?>
    </div>
</div>