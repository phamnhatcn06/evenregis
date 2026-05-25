<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ',
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
$this->Tabletitle = 'Danh sách cuộc thi văn nghệ';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'talent-shows-grid',
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
                    'name' => 'show_date',
                    'header' => 'Ngày biểu diễn',
                    'width' => '120px',
                    'filter' => false,
                    'value' => function ($data) {
                        return $data->show_date ? date('d/m/Y', strtotime($data->show_date)) : '';
                    }
                ),
                array('name' => 'location', 'header' => 'Địa điểm', 'width' => '150px', 'filter' => false),
                array('name' => 'max_entries_per_org', 'header' => 'Max tiết mục', 'width' => '100px', 'filter' => false),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => array(0 => 'Tạm dừng', 1 => 'Hoạt động'),
                    'value' => function ($data) {
                        return TalentShows::getActiveLabel($data->is_active);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/talentShows');
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
