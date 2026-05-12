<?php
$this->breadcrumbs = array(
    Properties::label(2),
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
            'id' => 'properties-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'prefix', 'header' => Yii::t('app', 'Mã đơn vị')),
                array('name' => 'smile_code', 'header' => Yii::t('app', 'Mã Smile')),
                array('name' => 'name', 'header' => Yii::t('app', 'Tên đơn vị')),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'sortable' => false,
                    'filter' => array(
                        'Hoạt động' => 'Hoạt động',
                        'Không hoạt động' => 'Không hoạt động',
                    ),
                    'value' => function ($data) {
                        return $data->status == 1
                            ? '<span class="badge bg-success">Hoạt động</span>'
                            : '<span class="badge bg-secondary">Không hoạt động</span>';
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 100,
                'order' => array(array(0, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
