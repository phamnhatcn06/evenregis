<?php
$this->breadcrumbs = array(
    Regionals::label(2),
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
            'id' => 'regionals-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'code', 'header' => 'Mã'),
                array('name' => 'name', 'header' => 'Tên khu vực'),
                array('name' => 'description', 'header' => 'Mô tả'),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return $data['status'] == 1
                            ? '<span class="badge bg-success">Hoạt động</span>'
                            : '<span class="badge bg-secondary">Không hoạt động</span>';
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/regionals');
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
