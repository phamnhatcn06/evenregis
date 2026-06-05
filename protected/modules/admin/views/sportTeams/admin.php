<?php
$this->breadcrumbs = array(
    'Đội thể thao',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Thêm đội mới',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách đội thể thao';

$sportOptions = array();
foreach ($sports as $sport) {
    $sportOptions[$sport->id] = $sport->name;
}
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'sport-teams-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên đội', 'width' => '200px'),
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
                    'name' => 'sport_id',
                    'header' => 'Môn thể thao',
                    'type' => 'raw',
                    'filter' => $sportOptions,
                    'value' => function ($data) {
                        return isset($data->sport_name) ? CHtml::encode($data->sport_name) : $data->sport_id;
                    }
                ),
                array(
                    'name' => 'property_id',
                    'header' => 'Đơn vị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return isset($data->property_name) ? CHtml::encode($data->property_name) : $data->property_id;
                    }
                ),
                array(
                    'name' => 'is_alliance',
                    'header' => 'Liên quân',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => array(0 => 'Không', 1 => 'Có'),
                    'value' => function ($data) {
                        return $data->is_alliance ? '<span class="badge bg-info">Liên quân</span>' : '<span class="badge bg-secondary">Đơn lẻ</span>';
                    }
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => array(0 => 'Ngừng', 1 => 'Hoạt động'),
                    'value' => function ($data) {
                        return $data->is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Ngừng</span>';
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/sportTeams');
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
