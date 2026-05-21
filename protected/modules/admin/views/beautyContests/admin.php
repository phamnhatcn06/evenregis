<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Thêm cuộc thi',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách cuộc thi sắc đẹp';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'beauty-contests-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên cuộc thi'),
                array(
                    'name' => 'event_id',
                    'header' => 'Sự kiện',
                    'type' => 'raw',
                    'filter' => $events,
                    'value' => function ($data) {
                        return isset($data->event_name) ? CHtml::encode($data->event_name) : $data->event_id;
                    }
                ),
                array(
                    'name' => 'gender',
                    'header' => 'Giới tính',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => BeautyContests::getGenderOptions(),
                    'value' => function ($data) {
                        return BeautyContests::getGenderLabel($data->gender);
                    }
                ),
                array('name' => 'age_min', 'header' => 'Tuổi min', 'width' => '80px', 'filter' => false),
                array('name' => 'age_max', 'header' => 'Tuổi max', 'width' => '80px', 'filter' => false),
                array(
                    'name' => 'contest_date',
                    'header' => 'Ngày thi',
                    'width' => '120px',
                    'filter' => false,
                    'value' => function ($data) {
                        return $data->contest_date ? date('d/m/Y', strtotime($data->contest_date)) : '';
                    }
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => array(0 => 'Tạm dừng', 1 => 'Hoạt động'),
                    'value' => function ($data) {
                        return BeautyContests::getActiveLabel($data->is_active);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/beautyContests');
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
