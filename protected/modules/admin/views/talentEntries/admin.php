<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Đăng ký tiết mục',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách tiết mục văn nghệ';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'talent-entries-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'title', 'header' => 'Tên tiết mục', 'width' => '250px'),
                array(
                    'name' => 'show_id',
                    'header' => 'Hội diễn',
                    'type' => 'raw',
                    'filter' => $shows,
                    'value' => function ($data) {
                        return isset($data->show_name) ? CHtml::encode($data->show_name) : $data->show_id;
                    }
                ),
                array(
                    'name' => 'category_id',
                    'header' => 'Thể loại',
                    'type' => 'raw',
                    'filter' => $categories,
                    'value' => function ($data) {
                        return isset($data->category_name) ? CHtml::encode($data->category_name) : $data->category_id;
                    }
                ),
                array(
                    'header' => 'Đơn vị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return isset($data->property_name) ? CHtml::encode($data->property_name) : '';
                    }
                ),
                array('name' => 'participant_count', 'header' => 'Số người', 'width' => '80px', 'filter' => false),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => TalentEntries::getStatusOptions(),
                    'value' => function ($data) {
                        return TalentEntries::getStatusLabel($data->status);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/talentEntries');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
            ),
        ));
        ?>
    </div>
</div>
